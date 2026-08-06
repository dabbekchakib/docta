<?php

namespace Database\Seeders;

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (PermissionEnum::cases() as $permission) {
            Permission::findOrCreate($permission->value);
        }

        $this->createRole(RoleEnum::SuperAdmin, PermissionEnum::cases());
        $this->createRole(RoleEnum::Admin, [
            PermissionEnum::UsersView,
            PermissionEnum::UsersCreate,
            PermissionEnum::UsersUpdate,
            PermissionEnum::PatientsView,
            PermissionEnum::PatientsCreate,
            PermissionEnum::PatientsUpdate,
            PermissionEnum::PatientsDelete,
            PermissionEnum::DoctorsView,
            PermissionEnum::DoctorsCreate,
            PermissionEnum::DoctorsUpdate,
            PermissionEnum::DoctorsDelete,
            PermissionEnum::DoctorsExport,
            PermissionEnum::SecretariesView,
            PermissionEnum::SecretariesCreate,
            PermissionEnum::SecretariesUpdate,
            PermissionEnum::SecretariesDelete,
            PermissionEnum::SecretariesExport,
            PermissionEnum::AppointmentsView,
            PermissionEnum::AppointmentsCreate,
            PermissionEnum::AppointmentsUpdate,
            PermissionEnum::AppointmentsDelete,
            PermissionEnum::AppointmentsConfirm,
            PermissionEnum::AppointmentsCancel,
            PermissionEnum::AppointmentsCalendar,
            PermissionEnum::ReportsView,
        ]);
        $this->createRole(RoleEnum::Doctor, [
            PermissionEnum::PatientsView,
            PermissionEnum::DoctorsView,
            PermissionEnum::AppointmentsView,
            PermissionEnum::AppointmentsCreate,
            PermissionEnum::AppointmentsUpdate,
            PermissionEnum::AppointmentsConfirm,
            PermissionEnum::AppointmentsCancel,
            PermissionEnum::AppointmentsCalendar,
            PermissionEnum::ConsultationsManage,
        ]);
        $this->createRole(RoleEnum::Secretary, [
            PermissionEnum::PatientsView,
            PermissionEnum::PatientsCreate,
            PermissionEnum::PatientsUpdate,
            PermissionEnum::DoctorsView,
            PermissionEnum::AppointmentsView,
            PermissionEnum::AppointmentsCreate,
            PermissionEnum::AppointmentsUpdate,
            PermissionEnum::AppointmentsDelete,
            PermissionEnum::AppointmentsConfirm,
            PermissionEnum::AppointmentsCancel,
            PermissionEnum::AppointmentsCalendar,
        ]);
        $this->createRole(RoleEnum::Patient, [
            PermissionEnum::PatientsView,
        ]);
        $this->createRole(RoleEnum::Accountant, [
            PermissionEnum::PatientsView,
            PermissionEnum::BillingManage,
            PermissionEnum::ReportsView,
        ]);

        $this->createAdminUsers();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * @param  PermissionEnum[]  $permissions
     */
    private function createRole(RoleEnum $role, array $permissions): void
    {
        $roleModel = Role::findOrCreate($role->value);

        $roleModel->syncPermissions(
            array_map(fn (PermissionEnum $permission) => $permission->value, $permissions)
        );
    }

    private function createAdminUsers(): void
    {
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@docta.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $superAdmin->syncRoles([RoleEnum::SuperAdmin->value]);
    }
}
