<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Temuan;
use Illuminate\Auth\Access\HandlesAuthorization;

class TemuanPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Temuan');
    }

    public function view(AuthUser $authUser, Temuan $temuan): bool
    {
        return $authUser->can('View:Temuan');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Temuan');
    }

    public function update(AuthUser $authUser, Temuan $temuan): bool
    {
        return $authUser->can('Update:Temuan');
    }

    public function delete(AuthUser $authUser, Temuan $temuan): bool
    {
        return $authUser->can('Delete:Temuan');
    }

    public function restore(AuthUser $authUser, Temuan $temuan): bool
    {
        return $authUser->can('Restore:Temuan');
    }

    public function forceDelete(AuthUser $authUser, Temuan $temuan): bool
    {
        return $authUser->can('ForceDelete:Temuan');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Temuan');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Temuan');
    }

    public function replicate(AuthUser $authUser, Temuan $temuan): bool
    {
        return $authUser->can('Replicate:Temuan');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Temuan');
    }

}