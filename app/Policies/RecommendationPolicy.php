<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Recommendation;
use Illuminate\Auth\Access\HandlesAuthorization;

class RecommendationPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Recommendation');
    }

    public function view(AuthUser $authUser, Recommendation $recommendation): bool
    {
        return $authUser->can('View:Recommendation');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Recommendation');
    }

    public function update(AuthUser $authUser, Recommendation $recommendation): bool
    {
        return $authUser->can('Update:Recommendation');
    }

    public function delete(AuthUser $authUser, Recommendation $recommendation): bool
    {
        return $authUser->can('Delete:Recommendation');
    }

    public function restore(AuthUser $authUser, Recommendation $recommendation): bool
    {
        return $authUser->can('Restore:Recommendation');
    }

    public function forceDelete(AuthUser $authUser, Recommendation $recommendation): bool
    {
        return $authUser->can('ForceDelete:Recommendation');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Recommendation');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Recommendation');
    }

    public function replicate(AuthUser $authUser, Recommendation $recommendation): bool
    {
        return $authUser->can('Replicate:Recommendation');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Recommendation');
    }

}