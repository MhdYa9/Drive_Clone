<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Folder;
use App\Rules\ValidFolderName;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'folder' => 'required|integer|exists:folders,id',
            'file'=>'required|file'
        ]);

        $file = $request->file('file');

        $request['name'] = $file->getClientOriginalName();
        $request->validate([
            'name' => [new ValidFolderName($data['folder'])],
        ]);

        $user = $request->user();

        DB::beginTransaction();

        try{
            $path = $file->store('private/'.$user->id);

            File::create([
                'user_id' => $user->id,
                'path' => $path,
                'name'=>$file->getClientOriginalName(),
                'folder_id' => $data['folder']
            ]);
            DB::commit();
        }
        catch(\Exception $e){

            DB::rollback();
            if(isset($path) && Storage::exists($path)){
                Storage::delete($path);
            }
            return response()->json(['message'=>'something wrong happened'],500);
        }
        return response()->json(['message'=>'file uploaded']);
    }

    /**
     * Display the specified resource.
     */
    public function show(File $file)
    {
        $folder = $file->folder;
        $this->authorize('read',$folder);
        $path = storage_path('app/'.$file->path);
        return response()->file($path);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, File $file)
    {
        $folder = $file->folder;
        $this->authorize('write', $folder);
        $type = $request->validate([
            'type'=>'required|string|in:renaming,moving'
        ]);

        if($type['type'] == 'renaming') {
            $name = $request->validate([
                'name' => 'required|string|max:255',
            ])['name'];

            if($file->name != $name){
                $new_name = $request->validate([
                    'name'=>[new ValidFolderName($file->folder_id)]
                ]);
                $file->update($new_name);
            }
        }
        else if ($type['type'] == 'moving') {

            $new_folder = Folder::findOrFail($request->new_folder);
            //check if I have writing access to folder I am moving to
            $this->authorize('write', $new_folder);

            Validator::make(['name' => $file->name,'new_folder'=>$new_folder],
                ['name' => new ValidFolderName($new_folder->id)])->validate();

            $file->update([
                'folder_id' => $new_folder->id,
            ]);

        }
        return response()->json(['message' => 'file updated']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(File $file)
    {
        $folder = $file->folder;
        $this->authorize('delete',$folder);

        if(\request('hard_delete') == 1){
            $file->forceDelete();
            Storage::delete($file->path);
        }
        else{
            $file->delete();
        }
        return response()->json(['message'=>'file deleted']);
    }

    public function restore(string $id)
    {
        $file = File::onlyTrashed()->find($id);
        if(Folder::where('id', $file->folder_id)->onlyTrashed()->exists() != null){
            throw ValidationException::withMessages(['cannot restore folder when parent is deleted']);
        }
        $file->restore();
        return response()->json(['message' => 'Folder restored'],204);
    }

    public function download()
    {

    }
}
