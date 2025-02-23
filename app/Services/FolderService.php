<?php

namespace App\Services;

use App\Models\File;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FolderService
{


    public function __construct(private ?Folder $folder = null){
    }

    public function setFolder(Folder $folder)
    {
        $this->folder = $folder;
    }


    private function recursiveCTE()
    {
        $query ="WITH RECURSIVE subfolders AS (SELECT id FROM folders where id = {$this->folder->id} UNION ALL SELECT f.id FROM folders f JOIN subfolders sf ON sf.id = f.parent_id) SELECT * FROM subfolders";
        $folders = DB::select($query);
        $folders = array_map(function ($item) {
            return $item->id;
        },$folders);

        return $folders;
    }

    private function bfs(?\Closure $closure = null)
    {
        $folders = [$this->folder->id];
        $subfolders = [$this->folder->id];
        while (true){
            $subfolders = Folder::whereIn('parent_id',$subfolders)->pluck('id')->toArray();
            if(count($subfolders) == 0){
                break;
            }
            $folders = array_merge($folders,$subfolders);
            if($closure !== null){
                if($closure($subfolders)){
                    return true;
                }
            }
        }
        if($closure !== null){
            return false;
        }
        return $folders;
    }

    private function quickFetch(array $columns = ['id'])
    {
        $folders = Folder::select(...$columns)
            ->where('ancestors','like','%,'.$this->folder->id.',%');
        if(count($columns) == 1 && $columns[0] == 'id'){
            $folders = $folders->pluck('id')->toArray();
        }
        else{
            $folders = $folders->get();
        }
        return $folders;
    }

    public function getChildrenIds($type = 'quick'){
        switch ($type){
            case 'bfs':
                return $this->bfs();
            case 'quick':
                return $this->quickFetch();
            case 'rec':
                return $this->recursiveCTE();
            default:
                return [];
        }
    }

    public function getChildren(array $columns,$type = 'quick')
    {
        switch ($type){
            case 'quick':
                return $this->quickFetch($columns);
            default:
                return [];
        }
    }

    public function validDestParent(Folder $destParent)
    {

//        if($destParent->id == $this->folder->id) return false;
//        return $this->bfs(function (array $folders) use ($destParent){
//            return in_array($destParent->id,$folders);
//        });

        //-------------------------------------------------------

        //the folder shall not be a child of mine, hence I should not be an ancestor of his
        $ancestors = $destParent->ancestorsArray;
        return !in_array($this->folder->id,$ancestors);

    }

    public function updateAncestors(Folder $destParent)
    {
        $old_ancestors =$this->folder->ancestors;
        $new_ancestors = ','.($destParent->ancestors?($destParent->id.$destParent->ancestors):($destParent->id.','));
        $this->folder->update([
            'parent_id' => $destParent->id,
            'ancestors' => $new_ancestors
        ]);
        DB::statement("UPDATE folders SET ancestors = REPLACE(ancestors,'{$old_ancestors}','{$new_ancestors}') WHERE ancestors like '%,{$this->folder->id},%'");
    }


    public function deleteSubTree($hard_delete = 0,$type = 'quick')
    {
        if($hard_delete){
            $this->folder->forceDelete();
        }
        $subtree=  [$this->folder->id,...$this->getChildrenIds($type)];
        Folder::whereIn('id',$subtree)->delete();
        File::whereIn('folder_id',$subtree)->delete();
    }


    public function getSharedFolder(User $user)
    {
        $folders = Folder::whereHas('usersPermissions', function ($query) use ($user) {
            return $query->where('users.id', $user->id)
                ->where('permissions.permission', 'like', '%r%');
        })
            ->where(function ($query) use ($user) {
                $query->whereNull('parent_id')
                    ->orWhereNotIn('parent_id', function ($subQuery) use ($user) {
                        $subQuery->select('folders.id')
                            ->from('folders')
                            ->join('permissions', 'folders.id', '=', 'permissions.folder_id')
                            ->join('users', 'permissions.user_id', '=', 'users.id')
                            ->where('users.id', $user->id)
                            ->where('permissions.permission', 'like', '%r%');
                    });
            })
            ->get();

        return $folders;
    }


    public function zip()
    {

        $subFolders = $this->getChildren(['id','name','ancestors']);


        $paths_map = $this->formatAncestors($subFolders);

        $zip_file = $this->folder->name;
        $zip = new \ZipArchive();
        $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        //add folders according to the hierarchy
        foreach ($paths_map as $_ => $path) {
            $zip->addEmptyDir($path);
        }

        //add files
        $files = File::whereIn('folder_id',$subFolders->pluck('id')->toArray())->get();
        foreach ($files as $file){
            $zip->addFile(storage_path('app/'.$file->path),$paths_map[$file->folder_id].'/'.$file->name);
        }

        $zip->close();

        return $zip_file;
    }

    private function formatAncestors($subFolders)
    {
        $folders_map[$this->folder->id] = $this->folder->name;
        $paths_map = [];

        foreach($subFolders as $subFolder){
            $folders_map[$subFolder->id] = $subFolder->name;
        }
        foreach ($subFolders as $item){
            $arr = explode(',',$item->ancestors);
            $arr = array_reverse(array_filter($arr));
            for($i=0;$i<count($arr);$i++){
                $arr[$i] = $folders_map[$arr[$i]];
            }
            $item->ancestors = implode('/',$arr);
            $paths_map[$item->id] = $item->ancestors.'/'.$item->name;
        }

        return $paths_map;
    }




}
