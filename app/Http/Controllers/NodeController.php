<?php

namespace App\Http\Controllers;

use App\Models\Node;
use App\Rules\ValidFolderName;
use App\Rules\ValidParentFolder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class NodeController extends Controller
{

    public function index(){
        //TODO: complete the function
    }

    /*
     *
     * crud folder
     * delete every thing under the folder and the folder itself
     * seperate fodler and file models
     * improve query complexity on dfs
     * premissions for folders read and write and crud on it
     * search on folders and files
     * */

    public function show()
    {
        //TODO: complete the function
    }
    public function store(Request $request)
    {
        $parent = $request->validate(
            ['parent'=>
                ['required'
                ,'integer'
                ,Rule::prohibitedIf(function (){
                    $parent = Node::findOrFail(request('parent'));
                    return $parent->type != 'folder';
                })
                ]
            ])['parent'];

        $data = $request->validate([
            'type'=>'bail|required|string|in:folder,file',
            'name' => ['required','string','max:255',new ValidFolderName($parent,request('type'))],
        ]);

        Node::create([
            'parent_id' => $parent,
            ...$data
        ]);

        return response()->json(['message' => $data['type'].' created'],203);
    }

    public function update(Request $request, Node $folder){


        $type = $request->validate([
            'type'=>'required|string|in:renaming,moving'
        ]);

        $data = [];

        if($type['type'] == 'renaming'){
            $name = $request->validate([
                'name' => 'required|string|max:255',
            ])['name'];

            if($folder->name != $name){
                $data = $request->validate([
                    'name'=>[new ValidFolderName($folder->parent_id,$folder->type)]
                ]);
            }
        }
        else if ($type['type'] == 'moving'){
            $data = $request->validate([
               'parent_id' => 'required|integer|exists:nodes,id',
            ]);

            Validator::make([
                'name' => $folder->name,'parent_id'=>$data['parent_id']],
                ['name' => new ValidFolderName($data['parent_id'],$type['type']),
                'parent_id'=>new ValidParentFolder($folder->id)])->validate();
            //if
        }


        $folder->update($data);
        return response()->json(['message' => 'Node updated']);
    }

    public function destroy(Node $folder){
        //TODO: complete the function
    }


}
