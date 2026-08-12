<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Doctor;
use App\Models\LaboratoryReport;
use App\Models\User;
use App\Policies\Concerns\AuthorizesMedicalAccess;

class LaboratoryReportPolicy
{
    use AuthorizesMedicalAccess;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            Permission::LaboratoryReportsView->value,
            Permission::LaboratoryReportsCreate->value,
            Permission::LaboratoryReportsDownload->value,
        ]);
    }

    public function view(User $user, LaboratoryReport $report): bool
    {
        if (! $user->hasPermissionTo(Permission::LaboratoryReportsView->value)) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        $request = $report->request;

        if (! $request) {
            return false;
        }

        return $this->isDoctor($user) && $request->doctor_id === $this->doctorIdFor($user);
    }

    public function create(User $user, LaboratoryReport $report): bool
    {
        return $user->hasPermissionTo(Permission::LaboratoryReportsCreate->value);
    }

    public function download(User $user, LaboratoryReport $report): bool
    {
        if (! $user->hasPermissionTo(Permission::LaboratoryReportsDownload->value)) {
            return false;
        }

        return $this->view($user, $report);
    }

    public function update(User $user, LaboratoryReport $report): bool
    {
        return $this->create($user, $report);
    }

    public function delete(User $user, LaboratoryReport $report): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }

    private function doctorIdFor(User $user): ?int
    {
        return Doctor::query()->where('user_id', $user->id)->value('id');
    }
}
