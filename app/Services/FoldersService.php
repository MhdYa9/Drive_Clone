<?php

namespace App\Services;

use App\Models\Folder;
use Illuminate\Support\Facades\DB;

class FoldersService
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

    public function getSubFolders($type = 'bfs'){
        switch ($type){
            case 'bfs':
                return $this->bfs();
                break;
            default:
                return $this->recursiveCTE();
                break;
        }
    }

    public function validDestParent(int $destParent)
    {
        return $this->bfs(function (array $folders) use ($destParent){
            return in_array($destParent,$folders);
        });
    }




}
