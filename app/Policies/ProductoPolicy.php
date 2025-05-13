<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Productos;
use Illuminate\Auth\Access\HandlesAuthorization;
class ProductoPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_producto');
    }

    public function view(User $user, Productos $producto): bool
    {
        return $user->can('view_producto');
    }

    public function create(User $user): bool
    {
        return $user->can('create_producto');
    }

    public function update(User $user, Productos $producto): bool
    {
        return $user->can('update_producto');
    }

    public function delete(User $user, Productos $producto): bool
    {
        return $user->can('delete_producto');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_producto');
    }

    public function forceDelete(User $user, Productos $producto): bool
    {
        return $user->can('force_delete_producto');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_producto');
    }

    public function restore(User $user, Productos $producto): bool
    {
        return $user->can('restore_producto');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_producto');
    }

    public function replicate(User $user, Productos $producto): bool
    {
        return $user->can('replicate_producto');
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder_producto');
    }
}
