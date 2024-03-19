<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Expert;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use Illuminate\validation\Rule;

class AuthController extends Controller
{
    public function register(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => ['required','string','max:255'],
            'email' => ['required','email',"unique:users,email"],
            'password' => ['required','confirmed', Rules\Password::defaults()],
            'gender' => ['required',Rule::in(['F','M'])],
            'phone' => ['required','regex:/^09\d{8}$/',],
            'photo' => ['image'],
            'role' => ['required',Rule::in(['0','1'])],
        ]);

        if($validator->fails()){
            return ApiResponse::sendResponse(422,'Registed Validation error',$validator->messages()->all());
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'password'=>Hash::make($request->password),
            'gender' => $request->gender,
            'phone'  => $request->phone
        ];

        if($request->has('photo')){
            $newImageName =  time().'-'.$request->photo->getClientOriginalName();
            $request->photo->saveAs('photos',$newImageName,'public');
            $data['photo'] = $newImageName;
        }

        $user = User::create($data);

        //Role 1 => register as expert
        //Role 0 => register as normal user
        if($request->role == 1){

            //expert must have one experience at least
            $expValidator = Validator::make($request->all(),[
                'experiences'=>['array','required'],
                'experiences.*.description' => ['required','string'],
                'experiences.*.cost' => ['required','numeric']
            ]);

            if($expValidator->fails()){
                return ApiResponse::sendResponse(422,'Experiences Validation error',$expValidator->messages()->all());
            }

            $expert = Expert::create([
                'user_id' => $user->id
            ]);

            foreach($request->experiences as $experience){
                $expert->experiences()->attach($experience['id'],[
                    'title' => $experience['title'],
                    'description' => $experience['description'],
                    'cost' => $experience['cost'],
                 ]);
            }
        }

        return ApiResponse::sendResponse(201,'Registered successfully',['user'=> $user, 'token'=> $user->createToken('auth_token')->plainTextToken]);
    }
    
    public function login(Request $request){
        $validator = Validator::make($request->all(),[
            'email'=>'required|email',
            'password'=>'required'
        ]);

        if($validator->fails()){
            return ApiResponse::sendResponse(422,'login Validation error',$validator->errors());
        }

        if(Auth::attempt(['email'=>$request->email, 'password'=>$request->password])){
            $user = Auth::user();
            $data['token'] = $user->createToken('auth_token')->plainTextToken;
            $data['name'] =  $user->name;
            $data['email'] =  $user->email;
            return ApiResponse::sendResponse(200, 'Login Successfully', $data);
        }
        else{
            return ApiResponse::sendResponse(401,'Invalid Email or Password');
        }
    }

    public function logout(Request $request){
       // dd($request->user());
        $request->user()->currentAccessToken()->delete();
        return ApiResponse::sendResponse(200,'loged out successfully');
    }
}
