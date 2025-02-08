<?php

namespace App\Http\Controllers;

use App\Http\Resources\FolderResource;
use App\Models\Folder;
use App\Rules\ValidFolderName;
use App\Rules\ValidParentFolder;
use App\Services\FolderService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class FolderController extends Controller
{


    /*
     *
     * actions:
     * filtering: filter (size,...)
     * observers
     *
     *
     * */

    public function index(){

        $user = \request()->user();

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

        return FolderResource::collection($folders);

    }

    public function search(Request $request)
    {
        $data = $request->validate([
            'folder' => 'required|integer|exists:folders,id',
            'search' => 'required|string'
        ]);

        $folders = Folder::where('name','like','%'.$data['search']. '%')
            ->where('ancestors','like','%,'.$data['folder'].',%')->get();
        return FolderResource::collection($folders);
    }

    /* *
     * permissions for folders read and write and crud on it
     * */

    public function show(Folder $folder)
    {
        $this->authorize('read', $folder);
        return new FolderResource($folder->load('subFolders','files'));
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $parent = Folder::findOrFail($request->parent);
        $this->authorize('write', $parent);

        $name = $request->validate([
            'name' => ['required','string','max:255',new ValidFolderName($parent->id)],
        ])['name'];

        $folder = Folder::create([
            'parent_id' => $parent->id,
            'ancestors' => ($parent->ancestors?(','.$parent->id.$parent->ancestors):(','.$parent->id.',')),
            'user_id' => $user->id,
            'name' => $name
        ]);

        $ps = new PermissionService($folder);
        $ps->permitAncestorsOwners();

        return response()->json(['message' => 'your folder is created'],201);
    }

    public function update(Request $request, Folder $folder){

        $this->authorize('write', $folder);
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
            //check if I have writing access to folder I am moving to
            $this->authorize('write', $parent);

            Validator::make(['name' => $folder->name,'parent'=>$parent],
                ['name' => new ValidFolderName($parent->id),
                'parent'=>new ValidParentFolder($folder)])->validate();

            $permission = $folder->usersPermissions()
                ->where('user_id',$folder->user_id)
                ->first()->pivot->permission;

            $ps = new PermissionService($folder);

            $fs = new FolderService($folder);
            $fs->updateAncestors($parent);
            //add permissions to new ancestors owners
            $ps->permitAncestorsOwners($permission);

        }
        return response()->json(['message' => 'Folder updated']);
    }

    public function destroy(int $folder){

        $folder = Folder::where('id',$folder)->withTrashed()->first();
        $this->authorize('delete', $folder);
        $hard_delete = request('hard_delete') == 1;

        $folder_service = new FolderService($folder);
        $folder_service->deleteSubTree(hard_delete:$hard_delete);

        return response()->json(['message' => 'Folder deleted'],204);
    }

    public function restore(int $folder){

        //don't restore folder if parent deleted
        $folder = Folder::onlyTrashed()->find($folder);
        $this->authorize('delete', $folder);
        if(Folder::where('id', $folder->parent_id)->onlyTrashed()->exists() !== null){
            throw ValidationException::withMessages(['cannot restore folder when parent is deleted']);
        }
        $folder->restore();
        return response()->json(['message' => 'Folder restored'],204);
    }

}
