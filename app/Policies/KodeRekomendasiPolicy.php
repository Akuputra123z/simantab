<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\KodeRekomendasi;
use Illuminate\Auth\Access\HandlesAuthorization;

class KodeRekomendasiPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:KodeRekomendasi');
    }

    public function view(AuthUser $authUser, KodeRekomendasi $kodeRekomendasi): bool
    {
        return $authUser->can('View:KodeRekomendasi');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:KodeRekomendasi');
    }

    public function update(AuthUser $authUser, KodeRekomendasi $kodeRekomendasi): bool
    {
        return $authUser->can('Update:KodeRekomendasi');
    }

    public function delete(AuthUser $authUser, KodeRekomendasi $kodeRekomendasi): bool
    {
        return $authUser->can('Delete:KodeRekomendasi');
    }

    public function restore(AuthUser $authUser, KodeRekomendasi $kodeRekomendasi): bool
    {
        return $authUser->can('Restore:KodeRekomendasi');
    }

    public function forceDelete(AuthUser $authUser, KodeRekomendasi $kodeRekomendasi): bool
    {
        return $authUser->can('ForceDelete:KodeRekomendasi');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:KodeRekomendasi');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:KodeRekomendasi');
    }

    public function replicate(AuthUser $authUser, KodeRekomendasi $kodeRekomendasi): bool
    {
        return $authUser->can('Replicate:KodeRekomendasi');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:KodeRekomendasi');
    }

}