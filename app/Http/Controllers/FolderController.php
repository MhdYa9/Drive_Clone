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

        $data = request()->validate([
            'folder_id' => 'required',
            'search' => 'required|string'
        ]);

        $fs = new FolderService(Folder::findOrFail($data['folder_id']));
        $folders = Folder::where('');

    }

    /* *
     * crud folder
     * premissions for folders read and write and crud on it
     * search on folders and files
     * */

    public function show(Folder $folder)
    {
        return new FolderResource($folder->load('subFolders','files'));
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $parent = Folder::findOrFail($request->parent);

        $name = $request->validate([
            'name' => ['required','string','max:255',new ValidFolderName($parent->id)],
        ])['name'];

        Folder::create([
            'parent_id' => $parent->id,
            'ancestors' => ($parent->ancestors?(','.$parent->id.$parent->ancestors):(','.$parent->id.',')),
            'user_id' => $user->id,
            'name' => $name
        ]);

        return response()->json(['message' => 'your folder is created'],201);
    }

    public function update(Request $request, Folder $folder){

        $type = $request->validate([
            'type'=>'required|string|in:renaming,moving'
        ]);

        if($type['type'] == 'renaming') {
            $name = $request->validate([
                'name' => 'required|string|max:255',
            ])['name'];

            if($folder->name != $name){
                $data = $request->validate([
                    'name'=>[new ValidFolderName($folder->parent_id)]
                ]);
                $folder->update($data);
            }
        }
        else if ($type['type'] == 'moving') {
            $parent = Folder::findOrFail($request->parent_id);

            Validator::make(['name' => $folder->name,'parent'=>$parent],
                ['name' => new ValidFolderName($parent->id),
                'parent'=>new ValidParentFolder($folder)])->validate();

            $fs = new FolderService($folder);
            $fs->updateAncestors($parent);
        }


        return response()->json(['message' => 'Folder updated']);
    }

    public function destroy(int $folder){

        $folder = Folder::where('id',$folder)->withTrashed()->first();

        $hard_delete = request('hard_delete');

        $folder_service = new FolderService($folder);
        $folder_service->deleteSubTree(hard_delete:$hard_delete);

        return response()->json(['message' => 'Folder deleted'],204);

    }


}
