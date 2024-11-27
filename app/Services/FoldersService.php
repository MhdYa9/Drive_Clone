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


    public function recursiveCTE()
    {
        $query ="WITH RECURSIVE subfolders AS (SELECT * FROM folders where id = {$this->folder->id} UNION ALL SELECT f.* FROM folders f JOIN subfolders sf ON sf.id = f.parent_id) SELECT * FROM subfolders";
        $folders = DB::select($query);

        return $folders;
    }

    public function bfs()
    {
        $folders = [$this->folder->id];
        $subfolders = [$this->folder->id];
        while (true){
            $subfolders = Folder::whereIn('parent_id',$subfolders)->pluck('id')->toArray();
            if(count($subfolders) == 0){
                break;
            }
            $folders = array_merge($folders,$subfolders);
        }

        return $folders;
    }

    public function getSubFolders(){
        return $this->recursiveCTE();
        //return $this->bfs();
    }


}
