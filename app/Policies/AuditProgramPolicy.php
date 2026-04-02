<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\AuditProgram;
use Illuminate\Auth\Access\HandlesAuthorization;

class AuditProgramPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AuditProgram');
    }

    public function view(AuthUser $authUser, AuditProgram $auditProgram): bool
    {
        return $authUser->can('View:AuditProgram');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AuditProgram');
    }

    public function update(AuthUser $authUser, AuditProgram $auditProgram): bool
    {
        return $authUser->can('Update:AuditProgram');
    }

    public function delete(AuthUser $authUser, AuditProgram $auditProgram): bool
    {
        return $authUser->can('Delete:AuditProgram');
    }

    public function restore(AuthUser $authUser, AuditProgram $auditProgram): bool
    {
        return $authUser->can('Restore:AuditProgram');
    }

    public function forceDelete(AuthUser $authUser, AuditProgram $auditProgram): bool
    {
        return $authUser->can('ForceDelete:AuditProgram');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AuditProgram');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AuditProgram');
    }

    public function replicate(AuthUser $authUser, AuditProgram $auditProgram): bool
    {
        return $authUser->can('Replicate:AuditProgram');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AuditProgram');
    }

}