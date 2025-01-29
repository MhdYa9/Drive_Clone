<?php

namespace App\Services;

use App\Models\Folder;
use App\Models\User;

class PermissionService
{

    public function __construct(private Folder $folder ){

    }

    public function permitAncestorsOwners(string $permission = 'drw')
    {
        $ancestors = $this->folder->ancestorsArray;
        $ancestors[] = $this->folder->id;
        $user_ids = Folder::whereIn('id', $ancestors)
            ->select('user_id')
            ->distinct()
            ->pluck('user_id')->toArray();

        $users_permissions = [];
        foreach ($user_ids as $user_id) {
            $users_permissions[$user_id] = ['permission'=>$permission];
        }
        $this->folder->usersPermissions()->syncWithoutDetaching($users_permissions);
        return true;
    }

    public function removeAncestorsOwnersPermissions(){
        $ancestors = $this->folder->ancestorsArray;
        $ancestors[] = $this->folder->id;
        $user_ids = Folder::whereIn('id', $ancestors)
            ->select('user_id')
            ->distinct()
            ->pluck('user_id')->toArray();

        $this->folder->usersPermissions()->detach($user_ids);
        return true;
    }

    public function addPermissionsToChildren(User $user,string $permission)
    {
        $fs = new FolderService($this->folder);
        $children = $fs->getChildrenIds();
        $children[] = $this->folder->id;

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
        $children = $fs->getChildrenIds();
        if($user->foldersPermissions()->detach($this->folder->id)){
            $user->foldersPermissions()->detach($children);
        }
        return true;
    }



}
