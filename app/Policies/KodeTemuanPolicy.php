<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\KodeTemuan;
use Illuminate\Auth\Access\HandlesAuthorization;

class KodeTemuanPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:KodeTemuan');
    }

    public function view(AuthUser $authUser, KodeTemuan $kodeTemuan): bool
    {
        return $authUser->can('View:KodeTemuan');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:KodeTemuan');
    }

    public function update(AuthUser $authUser, KodeTemuan $kodeTemuan): bool
    {
        return $authUser->can('Update:KodeTemuan');
    }

    public function delete(AuthUser $authUser, KodeTemuan $kodeTemuan): bool
    {
        return $authUser->can('Delete:KodeTemuan');
    }

    public function restore(AuthUser $authUser, KodeTemuan $kodeTemuan): bool
    {
        return $authUser->can('Restore:KodeTemuan');
    }

    public function forceDelete(AuthUser $authUser, KodeTemuan $kodeTemuan): bool
    {
        return $authUser->can('ForceDelete:KodeTemuan');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:KodeTemuan');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:KodeTemuan');
    }

    public function replicate(AuthUser $authUser, KodeTemuan $kodeTemuan): bool
    {
        return $authUser->can('Replicate:KodeTemuan');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:KodeTemuan');
    }

}