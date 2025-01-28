<?php

namespace App\Services;

use App\Models\Folder;
use App\Models\User;

class PermissionService
{

    public function __construct(private Folder $folder ){

    }


    public function permitAncestorsOwners()
    {
        $ancestors = array_merge($this->folder->ancestorsArray,$this->folder->id);
        $user_ids = Folder::whereIn('id', $ancestors)
            ->select('user_id')
            ->distinct()
            ->pluck('user_id')->toArray();

        $users_permissions = [];
        foreach ($user_ids as $user_id) {
            $users_permissions[$user_id] = ['permission'=>'drw'];
        }
        $this->folder->usersPermissions()->syncWithoutDetaching($users_permissions);
        return true;
    }

    public function addPermissionsToChildren(User $user,string $permission)
    {
        $fs = new FolderService($this->folder);
        $children = array_merge($fs->getChildrenIds(),$this->folder->id);

        $permissions = [];
        foreach ($children as $child) {
            $permissions[$child] = ['permission'=>$permission];
        }

        $user->foldersPermissions()->syncWithoutDetaching($permissions);
        return true;
    }

    public function removePermissionsFromChildren(User $user)
    {
        $fs = new FolderService($this->folder);
        $children = array_merge($fs->getChildrenIds(),$this->folder->id);
        $user->foldersPermissions()->detach($children);
        return true;
    }



}
