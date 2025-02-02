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

    private function quickFetch()
    {
        $folders = Folder::select('id')
            ->where('ancestors','like','%,'.$this->folder->id.',%')
            ->pluck('id')->toArray();
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




}
