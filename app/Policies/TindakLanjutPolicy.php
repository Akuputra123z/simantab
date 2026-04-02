<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\TindakLanjut;
use Illuminate\Auth\Access\HandlesAuthorization;

class TindakLanjutPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TindakLanjut');
    }

    public function view(AuthUser $authUser, TindakLanjut $tindakLanjut): bool
    {
        return $authUser->can('View:TindakLanjut');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TindakLanjut');
    }

    public function update(AuthUser $authUser, TindakLanjut $tindakLanjut): bool
    {
        return $authUser->can('Update:TindakLanjut');
    }

    public function delete(AuthUser $authUser, TindakLanjut $tindakLanjut): bool
    {
        return $authUser->can('Delete:TindakLanjut');
    }

    public function restore(AuthUser $authUser, TindakLanjut $tindakLanjut): bool
    {
        return $authUser->can('Restore:TindakLanjut');
    }

    public function forceDelete(AuthUser $authUser, TindakLanjut $tindakLanjut): bool
    {
        return $authUser->can('ForceDelete:TindakLanjut');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TindakLanjut');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TindakLanjut');
    }

    public function replicate(AuthUser $authUser, TindakLanjut $tindakLanjut): bool
    {
        return $authUser->can('Replicate:TindakLanjut');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TindakLanjut');
    }

}