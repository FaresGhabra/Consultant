<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Expert;
use App\Models\Experience_expert;
use Illuminate\Support\Facades\Validator;



class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api',['except'=>['login','register']]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email' => 'required|email',
            'password' => 'required|string|min:6'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(),422);
        }

        if(!$token = auth()->attempt($validator->validated())){
            return response()->json(['message'=>'Unauthoraized'],401);
        }
        $user = Auth::user()->toArray();

        if($user['role'] == 1){
            $ex = Expert::where('user_id',$user['id'])->get();
            $user = array_merge($user,['expert_id'=>$ex[0]['id']]);
        }
        else
            $user = array_merge($user,['expert_id'=>null]);
        return response()->json(['message'=>'successfully login','user'=>$user,'token'=>$token]);
    }


    public function register(Request $request){

        if($request->role==0){
        
        $validator = Validator::make($request->all(),[
            'name'=>'required|string|between:2,100',
            'email'=>'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6',
            'phone'=>'required|string',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(),422);}
           
        $user = User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>bcrypt($request->password),
            'phone'=>$request->phone,
            'role'=>$request->role,
        ]);
        $token = Auth::login($user);
    }


        if($request->role == 1)
        {
            $validator = Validator::make($request->all(),[
                'name'=>'required|string|between:2,100',
                'email'=>'required|string|email|max:100|unique:users',
                'password' => 'required|string|confirmed|min:6',
                'phone'=>'unique:users',
                'address' => 'required|string',
                
            ]);
            if($validator->fails()){
                return response()->json($validator->errors(),422);}

            $user = User::create([
                'name'=>$request->name,
                'email'=>$request->email,
                'password'=>bcrypt($request->password),
                'phone'=>$request->phone,
                'role'=>$request->role
            ]);
            $token = Auth::login($user);
            
            $user = array_merge($user->attributesToArray(),Expert::create([
                'user_id' => $user->id,
                 'address'=>$request->address,
                 'available_time'=>$request->available_time,
            ])->attributesToArray());
            if(request('photo')) {
            file_put_contents(public_path('imegs/').request('phone').'.jpg',file_get_contents($request->file('photo')->path()));
            $user = array_merge($user,['photo'=>base64_encode(file_get_contents(base_path("public\imegs\\".$user['phone'].".jpg")))]);
            }else{
               $user = array_merge($user,['photo'=>null]);}
            foreach(json_decode( $request->experience_expert) as $new_experince)
            {
             Experience_expert::create([
                 'expert_id' => $user['id'],
                 'experience_id'=>$new_experince->experience_id,
                 'description'=>$new_experince->description,
                 'cost'=>$new_experince->cost
             ]);
            }
         }

        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user,
            'token'=>$token
        ], 201);
    }

    public function logout() {
        auth()->logout();
        return response()->json(['message' => 'User successfully signed out']);
    }

    public function refresh() {
        return $this->createNewToken(auth()->refresh());
    }

    public function userProfile() {
        return response()->json(auth()->user());
    }

    protected function createNewToken($token){
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 1440,
            'user' => auth()->user()
        ]);
    }
}
