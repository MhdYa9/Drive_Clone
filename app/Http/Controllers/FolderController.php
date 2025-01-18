<?php

namespace App\Http\Controllers;

use App\Http\Resources\FolderResource;
use App\Models\Folder;
use App\Rules\ValidFolderName;
use App\Rules\ValidParentFolder;
use App\Services\FolderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FolderController extends Controller
{

    public function index(){
        //TODO: complete the function
    }

    /* *
     * crud folder
     * delete every thing under the folder and the folder itself
     * premissions for folders read and write and crud on it
     * search on folders and files
     * */

    public function show(Folder $folder)
    {
        return new FolderResource($folder->load('subFolders','files'));
    }
    public function store(Request $request)
    {
        $parent = Folder::findOrFail($request->parent_id);

        $name = $request->validate([
            'name' => ['required','string','max:255',new ValidFolderName($parent)],
        ])['name'];

        Folder::create([
            'parent_id' => $parent->id,
            'ancestors' => $parent->id + $parent->ancestors,
            'name' => $name
        ]);

        return response()->json(['message' => 'your folder is created'],201);
    }

    public function update(Request $request, Folder $folder){

        $type = $request->validate([
            'type'=>'required|string|in:renaming,moving'
        ]);

        $data = [];

        if($type['type'] == 'renaming') {
            $name = $request->validate([
                'name' => 'required|string|max:255',
            ])['name'];

            if($folder->name != $name){
                $data = $request->validate([
                    'name'=>[new ValidFolderName($folder->parent_id)]
                ]);
            }
        }
        else if ($type['type'] == 'moving') {
            $data = $request->validate([
               'parent_id' => 'required|integer|exists:folders,id',
            ]);

            Validator::make(['name' => $folder->name,'parent_id'=>$data['parent_id']],
                ['name' => new ValidFolderName($data['parent_id']),
                'parent_id'=>new ValidParentFolder($folder)])->validate();
        }


        $folder->update($data);
        return response()->json(['message' => 'Folder updated']);
    }

    public function destroy(Folder $folder){

        $hard_delete = request('hard_delete');

        $folder_service = new FolderService($folder);
        $folder_service->deleteSubTree(hard_delete:$hard_delete);

        return response()->json(['message' => 'Folder deleted'],203);

    }


}
