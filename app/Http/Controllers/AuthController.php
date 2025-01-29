<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException as ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8'
        ]);

        $user = User::where('email',$data['email'])->first();

        if(!$user){
            throw ValidationException::withMessages([
                'email' => 'the provided credentials are incorrect'
            ]);
        }

        if(!Hash::check($request->password, $user->password)){
            throw ValidationException::withMessages([
                'email' => 'the provided credentials are incorrect'
            ]);
        }

        if(!$user->tokens->isEmpty()){
            $user->tokens()->delete();
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token
        ]);

    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8'
        ]);

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);
        $root = Folder::create([
            'name' => 'root'.$user->id,
            'user_id' => $user->id
        ]);

        $root->usersPermissions()->attach([$user->id=>['permission'=>'drw']]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token
        ]);

    }

    public function logout(request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json([
            'message' => 'logged out successfully'
        ]);
    }

}
