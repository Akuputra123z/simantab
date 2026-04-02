<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\AuditAssignment;
use Illuminate\Auth\Access\HandlesAuthorization;

class AuditAssignmentPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AuditAssignment');
    }

    public function view(AuthUser $authUser, AuditAssignment $auditAssignment): bool
    {
        return $authUser->can('View:AuditAssignment');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AuditAssignment');
    }

    public function update(AuthUser $authUser, AuditAssignment $auditAssignment): bool
    {
        return $authUser->can('Update:AuditAssignment');
    }

    public function delete(AuthUser $authUser, AuditAssignment $auditAssignment): bool
    {
        return $authUser->can('Delete:AuditAssignment');
    }

    public function restore(AuthUser $authUser, AuditAssignment $auditAssignment): bool
    {
        return $authUser->can('Restore:AuditAssignment');
    }

    public function forceDelete(AuthUser $authUser, AuditAssignment $auditAssignment): bool
    {
        return $authUser->can('ForceDelete:AuditAssignment');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AuditAssignment');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AuditAssignment');
    }

    public function replicate(AuthUser $authUser, AuditAssignment $auditAssignment): bool
    {
        return $authUser->can('Replicate:AuditAssignment');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AuditAssignment');
    }

}