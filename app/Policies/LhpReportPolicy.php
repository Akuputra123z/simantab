<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LhpReport;
use Illuminate\Auth\Access\HandlesAuthorization;

class LhpReportPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LhpReport');
    }

    public function view(AuthUser $authUser, LhpReport $lhpReport): bool
    {
        return $authUser->can('View:LhpReport');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LhpReport');
    }

    public function update(AuthUser $authUser, LhpReport $lhpReport): bool
    {
        return $authUser->can('Update:LhpReport');
    }

    public function delete(AuthUser $authUser, LhpReport $lhpReport): bool
    {
        return $authUser->can('Delete:LhpReport');
    }

    public function restore(AuthUser $authUser, LhpReport $lhpReport): bool
    {
        return $authUser->can('Restore:LhpReport');
    }

    public function forceDelete(AuthUser $authUser, LhpReport $lhpReport): bool
    {
        return $authUser->can('ForceDelete:LhpReport');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LhpReport');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LhpReport');
    }

    public function replicate(AuthUser $authUser, LhpReport $lhpReport): bool
    {
        return $authUser->can('Replicate:LhpReport');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LhpReport');
    }

}