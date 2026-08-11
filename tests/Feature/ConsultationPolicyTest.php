<?php

namespace Tests\Feature;

use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsultationPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    protected function consultation(array $attributes = []): Consultation
    {
        return Consultation::factory()->create($attributes + [
            'consultation_date' => now()->toDateString(),
        ]);
    }

    protected function doctorUser(): array
    {
        $user = User::factory()->create();
        $user->assignRole('doctor');
        $doctor = Doctor::factory()->create(['user_id' => $user->id]);

        return [$user, $doctor];
    }

    public function test_admin_can_view_create_update_delete_and_print_consultations(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $consultation = $this->consultation();

        $this->assertTrue($admin->can('viewAny', Consultation::class));
        $this->assertTrue($admin->can('view', $consultation));
        $this->assertTrue($admin->can('create', Consultation::class));
        $this->assertTrue($admin->can('update', $consultation));
        $this->assertTrue($admin->can('delete', $consultation));
        $this->assertTrue($admin->can('print', $consultation));
    }

    public function test_doctor_can_view_update_and_print_only_his_own_consultations(): void
    {
        [$doctorUser, $doctor] = $this->doctorUser();
        $own = $this->consultation(['doctor_id' => $doctor->id]);
        $other = $this->consultation();

        $this->assertTrue($doctorUser->can('viewAny', Consultation::class));
        $this->assertTrue($doctorUser->can('view', $own));
        $this->assertTrue($doctorUser->can('update', $own));
        $this->assertTrue($doctorUser->can('print', $own));
        $this->assertFalse($doctorUser->can('view', $other));
        $this->assertFalse($doctorUser->can('update', $other));
        $this->assertFalse($doctorUser->can('print', $other));
    }

    public function test_doctor_cannot_delete_or_force_delete_consultations(): void
    {
        [$doctorUser, $doctor] = $this->doctorUser();
        $consultation = $this->consultation(['doctor_id' => $doctor->id]);

        $this->assertFalse($doctorUser->can('delete', $consultation));
        $this->assertFalse($doctorUser->can('deleteAny', Consultation::class));
        $this->assertFalse($doctorUser->can('forceDelete', $consultation));
    }

    public function test_doctor_can_create_consultations(): void
    {
        [$doctorUser] = $this->doctorUser();

        $this->assertTrue($doctorUser->can('create', Consultation::class));
    }

    public function test_secretary_can_view_but_not_create_update_or_delete(): void
    {
        $secretary = User::factory()->create();
        $secretary->assignRole('secretary');
        $consultation = $this->consultation();

        $this->assertTrue($secretary->can('viewAny', Consultation::class));
        $this->assertTrue($secretary->can('view', $consultation));
        $this->assertFalse($secretary->can('create', Consultation::class));
        $this->assertFalse($secretary->can('update', $consultation));
        $this->assertFalse($secretary->can('delete', $consultation));
        $this->assertFalse($secretary->can('print', $consultation));
    }

    public function test_super_admin_can_manage_consultations(): void
    {
        $superAdmin = User::where('email', 'admin@docta.com')->firstOrFail();
        $consultation = $this->consultation();

        $this->assertTrue($superAdmin->can('viewAny', Consultation::class));
        $this->assertTrue($superAdmin->can('view', $consultation));
        $this->assertTrue($superAdmin->can('delete', $consultation));
        $this->assertTrue($superAdmin->can('restore', $consultation));
    }
}
