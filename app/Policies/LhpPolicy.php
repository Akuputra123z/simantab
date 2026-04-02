<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Lhp;
use Illuminate\Auth\Access\HandlesAuthorization;

class LhpPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Lhp');
    }

    public function view(AuthUser $authUser, Lhp $lhp): bool
    {
        return $authUser->can('View:Lhp');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Lhp');
    }

    public function update(AuthUser $authUser, Lhp $lhp): bool
    {
        return $authUser->can('Update:Lhp');
    }

    public function delete(AuthUser $authUser, Lhp $lhp): bool
    {
        return $authUser->can('Delete:Lhp');
    }

    public function restore(AuthUser $authUser, Lhp $lhp): bool
    {
        return $authUser->can('Restore:Lhp');
    }

    public function forceDelete(AuthUser $authUser, Lhp $lhp): bool
    {
        return $authUser->can('ForceDelete:Lhp');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Lhp');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Lhp');
    }

    public function replicate(AuthUser $authUser, Lhp $lhp): bool
    {
        return $authUser->can('Replicate:Lhp');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Lhp');
    }

}