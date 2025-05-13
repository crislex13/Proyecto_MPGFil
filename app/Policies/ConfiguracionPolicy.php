<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Configuracion;
use Illuminate\Auth\Access\HandlesAuthorization;

class ConfiguracionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_configuracion');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Configuracion $configuracion): bool
    {
        return $user->can('view_configuracion');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_configuracion');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Configuracion $configuracion): bool
    {
        return $user->can('update_configuracion');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Configuracion $configuracion): bool
    {
        return $user->can('delete_configuracion');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_configuracion');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Configuracion $configuracion): bool
    {
        return $user->can('force_delete_configuracion');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_configuracion');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Configuracion $configuracion): bool
    {
        return $user->can('restore_configuracion');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_configuracion');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Configuracion $configuracion): bool
    {
        return $user->can('replicate_configuracion');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_configuracion');
    }
}
