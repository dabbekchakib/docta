<?php

namespace Tests\Feature;

use App\Enums\MedicalDocumentType;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\MedicalDocument;
use App\Models\Patient;
use App\Models\User;
use App\Services\MedicalDocumentService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class MedicalDocumentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        Storage::fake('medical-documents');
    }

    private function fakeFile(string $name, string $mime): UploadedFile
    {
        $content = match ($mime) {
            'application/pdf' => '%PDF-1.4'.PHP_EOL.'% docta'.PHP_EOL.str_repeat('x', 100),
            'image/png' => base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='),
            default => str_repeat('x', 100),
        };

        return UploadedFile::fake()->createWithContent($name, $content);
    }

    public function test_create_document_stores_file_on_private_disk(): void
    {
        $record = Patient::factory()->create()->medicalRecord;
        $user = User::factory()->create();

        $file = $this->fakeFile('bilan.pdf', 'application/pdf');

        $this->actingAs($user);

        $document = app(MedicalDocumentService::class)->create($record, [
            'title' => 'Bilan sanguin',
            'document_type' => MedicalDocumentType::Analysis->value,
            'document_date' => now()->toDateString(),
        ], $file);

        $this->assertDatabaseHas('medical_documents', [
            'id' => $document->id,
            'title' => 'Bilan sanguin',
            'created_by' => $user->id,
        ]);

        $this->assertNotNull($document->file());
        Storage::disk('medical-documents')->assertExists($document->file()->getPathRelativeToRoot());
    }

    public function test_create_rejects_unsupported_mime_type(): void
    {
        $record = Patient::factory()->create()->medicalRecord;

        $file = UploadedFile::fake()->create('note.txt', 50, 'text/plain');

        try {
            app(MedicalDocumentService::class)->create($record, [
                'title' => 'Document invalide',
                'document_type' => MedicalDocumentType::Other->value,
            ], $file);
            $this->fail('Un fichier non autorisé aurait dû être refusé.');
        } catch (HttpException $exception) {
            $this->assertSame(422, $exception->getStatusCode());
        }
    }

    public function test_create_rejects_oversized_file(): void
    {
        $record = Patient::factory()->create()->medicalRecord;

        $file = UploadedFile::fake()->create('gros.pdf', 12000, 'application/pdf');

        try {
            app(MedicalDocumentService::class)->create($record, [
                'title' => 'Fichier trop lourd',
                'document_type' => MedicalDocumentType::Other->value,
            ], $file);
            $this->fail('Un fichier trop volumineux aurait dû être refusé.');
        } catch (HttpException $exception) {
            $this->assertSame(422, $exception->getStatusCode());
        }
    }

    public function test_download_returns_streamed_response(): void
    {
        $record = Patient::factory()->create()->medicalRecord;

        $file = $this->fakeFile('certificat.pdf', 'application/pdf');

        $document = app(MedicalDocumentService::class)->create($record, [
            'title' => 'Certificat médical',
            'document_type' => MedicalDocumentType::Certificate->value,
        ], $file);

        $response = app(MedicalDocumentService::class)->download($document);

        $this->assertInstanceOf(StreamedResponse::class, $response);
    }

    public function test_authorized_doctor_can_download_document(): void
    {
        $user = User::factory()->create();
        $user->assignRole('doctor');
        $doctor = Doctor::factory()->create(['user_id' => $user->id]);

        $patient = Patient::factory()->create();
        Consultation::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'consultation_date' => now()->toDateString(),
        ]);

        $file = $this->fakeFile('imagerie.png', 'image/png');

        $document = app(MedicalDocumentService::class)->create($patient->medicalRecord, [
            'title' => 'Radiographie',
            'document_type' => MedicalDocumentType::Radiology->value,
        ], $file);

        $this->actingAs($user)
            ->get(route('medical-documents.download', $document))
            ->assertOk()
            ->assertDownload('imagerie.png');
    }

    public function test_unauthorized_doctor_cannot_download_document(): void
    {
        $user = User::factory()->create();
        $user->assignRole('doctor');
        Doctor::factory()->create(['user_id' => $user->id]);

        $patient = Patient::factory()->create();

        $file = $this->fakeFile('confidentiel.pdf', 'application/pdf');

        $document = app(MedicalDocumentService::class)->create($patient->medicalRecord, [
            'title' => 'Document confidentiel',
            'document_type' => MedicalDocumentType::Report->value,
        ], $file);

        $this->actingAs($user)
            ->get(route('medical-documents.download', $document))
            ->assertForbidden();
    }

    public function test_download_route_requires_authentication(): void
    {
        $record = Patient::factory()->create()->medicalRecord;

        $document = MedicalDocument::factory()->create([
            'medical_record_id' => $record->id,
        ]);

        $this->get(route('medical-documents.download', $document))->assertRedirect(route('login'));
    }
}
