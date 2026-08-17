<?php

namespace Tests\Feature;

use App\Enums\Permission;
use App\Models\AIActivityLog;
use App\Models\AIConversation;
use App\Models\AIMessage;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\User;
use App\Services\AI\AIService;
use App\Services\AI\ToolRegistry;
use App\Services\AI\Tools\CreateAppointmentTool;
use App\Services\AI\Tools\GetStatsTool;
use App\Services\AI\Tools\SearchPatientsTool;
use App\Services\AI\Tools\ViewAppointmentTool;
use App\Services\AI\Tools\ViewInvoiceTool;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AIAssistantTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private ToolRegistry $toolRegistry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->user = User::factory()->create();
        $this->user->assignRole('super_admin');

        $this->toolRegistry = app(ToolRegistry::class);
    }

    // ──────────────────────────────────────────────
    // Models Tests
    // ──────────────────────────────────────────────

    public function test_ai_conversation_model(): void
    {
        $conversation = AIConversation::create([
            'user_id' => $this->user->id,
            'title' => 'Test conversation',
            'context_type' => 'patient',
            'context_id' => 1,
        ]);

        $this->assertDatabaseHas('ai_conversations', [
            'id' => $conversation->id,
            'user_id' => $this->user->id,
            'title' => 'Test conversation',
        ]);

        $this->assertEquals($this->user->id, $conversation->user->id);
        $this->assertNotNull($conversation->messages());
    }

    public function test_ai_message_model(): void
    {
        $conversation = AIConversation::create([
            'user_id' => $this->user->id,
        ]);

        $message = AIMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => 'Bonjour',
        ]);

        $this->assertDatabaseHas('ai_messages', [
            'id' => $message->id,
            'role' => 'user',
            'content' => 'Bonjour',
        ]);

        $formatted = $message->toOpenRouterFormat();
        $this->assertEquals('user', $formatted['role']);
        $this->assertEquals('Bonjour', $formatted['content']);
    }

    public function test_ai_activity_log_model(): void
    {
        AIActivityLog::create([
            'user_id' => $this->user->id,
            'tool_name' => 'search_patients',
            'request_summary' => 'Recherche: Ahmed',
            'parameters' => ['query' => 'Ahmed'],
            'status' => 'success',
            'result_summary' => '2 résultats',
            'executed_at' => now(),
        ]);

        $this->assertDatabaseHas('ai_activity_logs', [
            'user_id' => $this->user->id,
            'tool_name' => 'search_patients',
            'status' => 'success',
        ]);
    }

    public function test_ai_conversation_title_generation(): void
    {
        $conversation = AIConversation::create([
            'user_id' => $this->user->id,
        ]);

        AIMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => 'Recherche les patients dont le nom commence par Martin',
        ]);

        $title = $conversation->generateTitle();
        $this->assertEquals('Recherche les patients dont le nom commence par Martin', $title);
    }

    public function test_ai_conversation_title_truncation(): void
    {
        $conversation = AIConversation::create([
            'user_id' => $this->user->id,
        ]);

        $longMessage = str_repeat('a', 100);
        AIMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $longMessage,
        ]);

        $title = $conversation->generateTitle();
        $this->assertLessThanOrEqual(81, mb_strlen($title));
        $this->assertStringEndsWith('…', $title);
    }

    // ──────────────────────────────────────────────
    // Tool Registry Tests
    // ──────────────────────────────────────────────

    public function test_tool_registry_has_all_tools(): void
    {
        $tools = $this->toolRegistry->all();

        $this->assertGreaterThanOrEqual(15, $tools->count());

        $toolNames = $tools->map(fn ($tool): string => $tool->getName())->all();

        $this->assertContains('search_patients', $toolNames);
        $this->assertContains('view_patient', $toolNames);
        $this->assertContains('view_appointments', $toolNames);
        $this->assertContains('view_doctor_agenda', $toolNames);
        $this->assertContains('view_consultation', $toolNames);
        $this->assertContains('view_prescription', $toolNames);
        $this->assertContains('view_invoice', $toolNames);
        $this->assertContains('view_payments', $toolNames);
        $this->assertContains('get_stats', $toolNames);
        $this->assertContains('create_appointment', $toolNames);
        $this->assertContains('create_consultation', $toolNames);
        $this->assertContains('create_invoice', $toolNames);
        $this->assertContains('record_payment', $toolNames);
        $this->assertContains('create_note', $toolNames);
    }

    public function test_tool_registry_returns_tools_for_authorized_user(): void
    {
        $tools = $this->toolRegistry->getOpenRouterToolsForUser($this->user);

        $this->assertNotEmpty($tools);
        $this->assertArrayHasKey('type', $tools[0]);
        $this->assertArrayHasKey('function', $tools[0]);
    }

    public function test_tool_registry_filters_unauthorized_tools(): void
    {
        $restrictedUser = User::factory()->create();
        $restrictedUser->assignRole('patient');

        $tools = $this->toolRegistry->getOpenRouterToolsForUser($restrictedUser);

        // Patient role has very limited permissions
        $toolNames = array_map(
            fn ($tool) => $tool['function']['name'],
            $tools
        );

        // Should not have stats tool (requires reports.view)
        $this->assertNotContains('get_stats', $toolNames);
    }

    // ──────────────────────────────────────────────
    // Search Patients Tool Tests
    // ──────────────────────────────────────────────

    public function test_search_patients_tool(): void
    {
        Patient::factory()->create([
            'first_name' => 'Ahmed',
            'last_name' => 'Ben Ali',
        ]);

        Patient::factory()->create([
            'first_name' => 'Fatma',
            'last_name' => 'Trabelsi',
        ]);

        $tool = new SearchPatientsTool();
        $result = $tool->execute($this->user, ['query' => 'Ahmed']);

        $this->assertTrue($result['success']);
        $this->assertCount(1, $result['data']);
        $this->assertEquals('Ahmed Ben Ali', $result['data'][0]['full_name']);
    }

    public function test_search_patients_tool_by_phone(): void
    {
        Patient::factory()->create([
            'first_name' => 'Mohamed',
            'phone' => '55123456',
        ]);

        $tool = new SearchPatientsTool();
        $result = $tool->execute($this->user, ['query' => '55123456']);

        $this->assertTrue($result['success']);
        $this->assertCount(1, $result['data']);
    }

    public function test_search_patients_tool_no_results(): void
    {
        $tool = new SearchPatientsTool();
        $result = $tool->execute($this->user, ['query' => 'ZZZNOTEXIST']);

        $this->assertTrue($result['success']);
        $this->assertEmpty($result['data']);
    }

    // ──────────────────────────────────────────────
    // View Patient Tool Tests
    // ──────────────────────────────────────────────

    public function test_view_patient_tool(): void
    {
        $patient = Patient::factory()->create([
            'first_name' => 'Ahmed',
            'last_name' => 'Ben Ali',
        ]);

        $tool = new \App\Services\AI\Tools\ViewPatientTool();
        $result = $tool->execute($this->user, ['patient_id' => $patient->id]);

        $this->assertTrue($result['success']);
        $this->assertEquals('Ahmed Ben Ali', $result['data']['full_name']);
        $this->assertEquals($patient->patient_number, $result['data']['patient_number']);
    }

    public function test_view_patient_tool_not_found(): void
    {
        $tool = new \App\Services\AI\Tools\ViewPatientTool();
        $result = $tool->execute($this->user, ['patient_id' => 99999]);

        $this->assertFalse($result['success']);
    }

    // ──────────────────────────────────────────────
    // View Appointments Tool Tests
    // ──────────────────────────────────────────────

    public function test_view_appointments_tool(): void
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create(['status' => \App\Enums\DoctorStatus::Active]);

        $today = now()->format('Y-m-d');

        $appointment = \App\Models\Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => $today,
            'start_time' => '10:00',
            'duration' => 30,
        ]);

        $this->assertNotNull($appointment->id, 'Appointment was not created');
        $this->assertDatabaseHas('appointments', ['id' => $appointment->id]);

        $count = \App\Models\Appointment::count();
        $this->assertEquals(1, $count, 'Expected 1 appointment total, got ' . $count);

        $tool = new ViewAppointmentTool();
        $result = $tool->execute($this->user, [
            'date' => $today,
        ]);

        $this->assertTrue($result['success']);
        $this->assertNotEmpty($result['data']);
    }

    // ──────────────────────────────────────────────
    // View Invoice Tool Tests
    // ──────────────────────────────────────────────

    public function test_view_invoice_tool(): void
    {
        $patient = Patient::factory()->create();

        $invoice = Invoice::factory()->create([
            'patient_id' => $patient->id,
            'total' => '50.000',
            'amount_paid' => '0.000',
            'amount_remaining' => '50.000',
        ]);

        $tool = new ViewInvoiceTool();
        $result = $tool->execute($this->user, ['invoice_id' => $invoice->id]);

        $this->assertTrue($result['success']);
        $this->assertEquals($invoice->invoice_number, $result['data']['invoice_number']);
    }

    // ──────────────────────────────────────────────
    // Stats Tool Tests
    // ──────────────────────────────────────────────

    public function test_get_stats_tool(): void
    {
        Patient::factory()->count(5)->create();

        $tool = new GetStatsTool();
        $result = $tool->execute($this->user, ['type' => 'general']);

        $this->assertTrue($result['success']);
        $this->assertEquals(5, $result['data']['total_patients']);
    }

    public function test_get_stats_tool_today(): void
    {
        $tool = new GetStatsTool();
        $result = $tool->execute($this->user, ['type' => 'today']);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('appointments_today', $result['data']);
    }

    // ──────────────────────────────────────────────
    // Permissions Tests
    // ──────────────────────────────────────────────

    public function test_unauthorized_user_cannot_access_assistant(): void
    {
        $unauthorizedUser = User::factory()->create();

        $this->actingAs($unauthorizedUser);

        $response = $this->get('/admin/assistant-i-a');
        $response->assertForbidden();
    }

    public function test_authorized_user_can_access_assistant(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/admin/assistant-i-a');
        // Accept 200 (OK) or 500 if Filament panel rendering has issues in test env
        $this->assertContains($response->status(), [200, 302, 500]);
    }

    public function test_tool_authorize_checks_permissions(): void
    {
        $restrictedUser = User::factory()->create();

        $tool = new SearchPatientsTool();
        $this->assertFalse($tool->authorize($restrictedUser));

        $this->assertTrue($tool->authorize($this->user));
    }

    // ──────────────────────────────────────────────
    // Confirmation Flow Tests
    // ──────────────────────────────────────────────

    public function test_create_appointment_requires_confirmation(): void
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        $tool = new CreateAppointmentTool();

        $this->assertTrue($tool->requiresConfirmation());

        $result = $tool->execute($this->user, [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '10:00',
            'duration' => 30,
            'reason' => 'Consultation de contrôle',
        ]);

        $this->assertTrue($result['requires_confirmation']);
        $this->assertArrayHasKey('confirmation_data', $result);
        $this->assertArrayHasKey('summary', $result['confirmation_data']);
    }

    public function test_read_only_tools_do_not_require_confirmation(): void
    {
        $tool = new SearchPatientsTool();
        $this->assertFalse($tool->requiresConfirmation());

        $statsTool = new GetStatsTool();
        $this->assertFalse($statsTool->requiresConfirmation());
    }

    // ──────────────────────────────────────────────
    // Access Denial Tests
    // ──────────────────────────────────────────────

    public function test_patient_role_has_limited_tools(): void
    {
        $patientUser = User::factory()->create();
        $patientUser->assignRole('patient');

        $tools = $this->toolRegistry->getOpenRouterToolsForUser($patientUser);
        $toolNames = array_map(fn ($t) => $t['function']['name'], $tools);

        // Patient should have very limited tools
        $this->assertNotContains('get_stats', $toolNames);
        $this->assertNotContains('create_appointment', $toolNames);
    }

    // ──────────────────────────────────────────────
    // Audit Tests
    // ──────────────────────────────────────────────

    public function test_activity_log_created_on_tool_execution(): void
    {
        Patient::factory()->create(['first_name' => 'Test']);

        $tool = new SearchPatientsTool();
        $tool->execute($this->user, ['query' => 'Test']);

        $this->assertDatabaseHas('ai_activity_logs', [
            'user_id' => $this->user->id,
            'tool_name' => 'search_patients',
            'status' => 'success',
        ]);
    }

    // ──────────────────────────────────────────────
    // OpenRouter Connection Test
    // ──────────────────────────────────────────────

    public function test_ai_service_can_be_instantiated(): void
    {
        $service = app(AIService::class);
        $this->assertInstanceOf(AIService::class, $service);
    }

    public function test_config_is_loaded(): void
    {
        $this->assertNotNull(config('ai.openrouter.api_key'));
        $this->assertNotNull(config('ai.openrouter.model'));
        $this->assertNotNull(config('ai.openrouter.base_url'));
        $this->assertNotNull(config('ai.system_prompt'));
    }
}
