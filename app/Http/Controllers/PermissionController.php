<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PermissionController extends Controller
{

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user' => 'required',
            'permission' => 'required|regex:/^(?!.*(.).*\1)[wrd]+$/',
            'folder'=>'required',
        ]);


        $user = User::findOrFail($data['user']);
        $folder = Folder::findOrFail($data['folder']);

        Gate::authorize('isOwner',$folder);

        //sorting the array:
        $data['permission'] = str_split($data['permission']);
        sort($data['permission']);
        $data['permission'] = implode($data['permission']);


        $user->foldersPermissions()->syncWithoutDetaching([$data['folder'] => ['permission'=>$data['permission']]]);
        return response()->json(['message'=>'permissions created successfully'],201);
    }


    public function destroy()
    {
        $data = \request()->validate([
            'user' => 'required',
            'folder'=>'required'
        ]);

        $user = User::findOrFail($data['user']);
        $folder = Folder::findOrFail($data['folder']);

        Gate::authorize('isOwner',$folder);

        $user->foldersPermissions()->detach($data['folder']);

        return response()->json(['message'=>'permissions deleted successfully'],203);
    }
}
