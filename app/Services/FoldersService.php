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

    public function getSubFolders(){

        $query ="WITH RECURSIVE subfolders AS (SELECT * FROM folders where id = {$this->folder->id} UNION ALL SELECT f.* FROM folders f JOIN subfolders sf ON sf.id = f.parent_id) SELECT * FROM subfolders";
        $folders = DB::select($query);

        return $folders;
    }


}
