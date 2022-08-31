<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Validator;
use App\Models\User;
class AuthController extends Controller
{
    public function _constructor(){
        $this->middleware('auth:api',['expect' => ['login','register']]);
    }
    public function register(Request $request){
        $validator = Validator::make($request->all(),[
            'name'=>'required',
            'email'=>'required|string|email',
            'password'=>'required|string'
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(),400);
        }

        $user = User::create(array_merge(
            $validator->validate(),
            ['password'=>bcrypt($request->password)]
        ));
        return response()->json([
            'message'=>'User successfully registered',
            'user'=>$user
        ], 200);
    }

    public function login(Request $request){
        $validator = Validator::make($request->all(),[
            'email'=>'required|email',
            'password'=>'required|string'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(),422);
        }

        if(!$token=auth()->attempt($validator->validated())){
            return response()->json([
                'error'=>'Unauthorized'
            ], 401);
        }
        return $this->createNewToken($token);
    }

    public function allUsersList(Request $request){
        if(!$user = auth()->user()){
            return response()->json([
                'error'=>'Unauthorized'
            ], 401);
        }

        $limit = 100;
        $allUsers = [];

        if ($request->has('limit')) {
            $limit = $request->input('limit');
            $allUsers = User::take($limit)->orderBy('id', 'asc')->get();
        }else{
            $allUsers = User::all();
        }

        return response()->json(['message'=>'LIST OF USER','payload'=>$allUsers],200);
    }

    public function createNewToken($token){
        return response()->json([
            'access_token'=>$token,
            'token_type'=>'bearer',
            'expires_in'=>auth()->factory()->getTTL()*60,
            'user'=>auth()->user()
        ], 200);
    }
}
