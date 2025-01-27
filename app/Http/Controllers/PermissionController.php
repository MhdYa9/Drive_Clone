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

        $data['permission'] = $this->permissionFormatter($data['permission']);
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


    private function permissionFormatter(String  $permissions)
    {
        //sorting the array:
        $str = '';
        $permissions = str_split($permissions);
        sort($permissions);
        $arr = ['d','r','w'];

        $j = 0;
        for($i=0;$i<count($arr);$i++){
            if($j>=count($permissions) ||$permissions[$j] != $arr[$i]){
                $str .= '_';
            }
            else if($permissions[$j] == $arr[$i]){
                $str.=$permissions[$j];
                $j++;
            }
        }
        return $str;
    }
}
