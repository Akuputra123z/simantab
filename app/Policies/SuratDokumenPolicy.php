<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\SuratDokumen;
use Illuminate\Auth\Access\HandlesAuthorization;

class SuratDokumenPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SuratDokumen');
    }

    public function view(AuthUser $authUser, SuratDokumen $suratDokumen): bool
    {
        return $authUser->can('View:SuratDokumen');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SuratDokumen');
    }

    public function update(AuthUser $authUser, SuratDokumen $suratDokumen): bool
    {
        return $authUser->can('Update:SuratDokumen');
    }

    public function delete(AuthUser $authUser, SuratDokumen $suratDokumen): bool
    {
        return $authUser->can('Delete:SuratDokumen');
    }

    public function restore(AuthUser $authUser, SuratDokumen $suratDokumen): bool
    {
        return $authUser->can('Restore:SuratDokumen');
    }

    public function forceDelete(AuthUser $authUser, SuratDokumen $suratDokumen): bool
    {
        return $authUser->can('ForceDelete:SuratDokumen');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SuratDokumen');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SuratDokumen');
    }

    public function replicate(AuthUser $authUser, SuratDokumen $suratDokumen): bool
    {
        return $authUser->can('Replicate:SuratDokumen');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SuratDokumen');
    }

}