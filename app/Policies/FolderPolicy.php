<?php

namespace App\Policies;

use App\Models\Folder;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\DB;

class FolderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return 0;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Folder $folder): bool
    {
        $user_permission = DB::table('permissions')
                ->select('permission')
                ->where('user_id', $user->id)
                ->where('folder_id', $folder->id)->first()?->permission;
        return $user_permission !== null && $user_permission['1'] == 'r';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user,Folder $parent): bool
    {
        return 0;

    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Folder $folder): bool
    {
        return 0;

    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Folder $folder): bool
    {
        return 0;

    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Folder $folder): bool
    {
        return 0;

    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Folder $folder): bool
    {
        return 0;

    }
}
