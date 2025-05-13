<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SesionAdicional;
use Illuminate\Auth\Access\HandlesAuthorization;

class SesionAdicionalPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_sesion::adicional');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SesionAdicional $sesionAdicional): bool
    {
        return $user->can('view_sesion::adicional');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_sesion::adicional');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SesionAdicional $sesionAdicional): bool
    {
        return $user->can('update_sesion::adicional');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SesionAdicional $sesionAdicional): bool
    {
        return $user->can('delete_sesion::adicional');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_sesion::adicional');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, SesionAdicional $sesionAdicional): bool
    {
        return $user->can('force_delete_sesion::adicional');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_sesion::adicional');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, SesionAdicional $sesionAdicional): bool
    {
        return $user->can('restore_sesion::adicional');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_sesion::adicional');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, SesionAdicional $sesionAdicional): bool
    {
        return $user->can('replicate_sesion::adicional');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_sesion::adicional');
    }
}
