<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\UnitDiperiksa;
use Illuminate\Auth\Access\HandlesAuthorization;

class UnitDiperiksaPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:UnitDiperiksa');
    }

    public function view(AuthUser $authUser, UnitDiperiksa $unitDiperiksa): bool
    {
        return $authUser->can('View:UnitDiperiksa');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:UnitDiperiksa');
    }

    public function update(AuthUser $authUser, UnitDiperiksa $unitDiperiksa): bool
    {
        return $authUser->can('Update:UnitDiperiksa');
    }

    public function delete(AuthUser $authUser, UnitDiperiksa $unitDiperiksa): bool
    {
        return $authUser->can('Delete:UnitDiperiksa');
    }

    public function restore(AuthUser $authUser, UnitDiperiksa $unitDiperiksa): bool
    {
        return $authUser->can('Restore:UnitDiperiksa');
    }

    public function forceDelete(AuthUser $authUser, UnitDiperiksa $unitDiperiksa): bool
    {
        return $authUser->can('ForceDelete:UnitDiperiksa');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:UnitDiperiksa');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:UnitDiperiksa');
    }

    public function replicate(AuthUser $authUser, UnitDiperiksa $unitDiperiksa): bool
    {
        return $authUser->can('Replicate:UnitDiperiksa');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:UnitDiperiksa');
    }

}