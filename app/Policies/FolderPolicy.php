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


    private function permission(User $user, Folder $folder)
    {
        $permission = DB::table('permissions')
            ->select('permission')
            ->where('user_id', $user->id)
            ->where('folder_id', $folder->id)->first()?->permission;

        return $permission;
    }

    public function viewAny(User $user): bool
    {
        return 0;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Folder $folder): bool
    {
        $permission = $this->permission($user, $folder);
        return $permission !== null && $permission['1'] == 'r';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user,Folder $parent): bool
    {
        $permission = $this->permission($user, $parent);
        return $permission !== null && $permission['1'] == 'r';
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
