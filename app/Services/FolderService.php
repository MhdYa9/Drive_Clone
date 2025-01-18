<?php

namespace App\Services;

use App\Models\File;
use App\Models\Folder;
use Illuminate\Support\Facades\DB;

class FolderService
{

    private Folder $folder;

    public function __construct(Folder $folder){
        $this->folder = $folder;
    }


    private function recursiveCTE()
    {
        $query ="WITH RECURSIVE subfolders AS (SELECT * FROM folders where id = {$this->folder->id} UNION ALL SELECT f.* FROM folders f JOIN subfolders sf ON sf.id = f.parent_id) SELECT * FROM subfolders";
        $folders = DB::select($query);

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

    private function quickFetch()
    {

    }

    public function getChildren($type = 'bfs'){
        switch ($type){
            case 'bfs':
                return $this->bfs();
                break;
            default:
                return $this->recursiveCTE();
                break;
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


    public function deleteSubTree($hard_delete = 0,$type = 'bfs')
    {
        if($hard_delete){
            $this->folder->forceDelete();
        }
        $subtree=  [$this->folder->id,...$this->getChildren($type)];
        Folder::whereIn('id',$subtree)->delete();
        File::whereIn('parent_id',$subtree)->delete();
    }




}
