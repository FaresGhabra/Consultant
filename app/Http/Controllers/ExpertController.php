<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Expert;
use App\Models\Experience_expert;
use App\Models\Appointment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

use Illuminate\Http\Request;


class ExpertController extends Controller
{
    public function searchExpert(Request $request)
    {
        $users =  User::where('name',$request->name)->where('role',1)->get()->toArray();
        if(!$users){
            return response()->json(['message'=>'ما عنا حدا بهالاسم والله يا غالي']);
        }
        $data = [];
        foreach($users as $user){
            $ex = Expert::where('user_id',$user['id'])->get(['id','address'])->toArray();
            if(file_exists(public_path('imegs/'.$user['phone'].".jpg")))
            $user = array_merge($user,['address'=>$ex[0]['address']],['expert_id'=>$ex[0]['id']],['photo'=>base64_encode(file_get_contents(base_path("public\imegs\\".$user['phone'].".jpg")))]);
            else
            $user = array_merge($user,['address'=>$ex[0]['address']],['expert_id'=>$ex[0]['id']],['photo'=>null]);
            array_push($data,$user);
        }
         return response()->json($data);
    }

    public function setAvailableTime(Request $request){


        $available_time = $request->available_time;

        if(!str::isJson($available_time))
           return response()->json(['message'=>'false.. not valid JSON']);

        Expert::findorFail($request->expert_id)->update([//there.. expert id
            'available_time'=>$request->available_time
        ]);
        return response()->json(['message'=>'success']);
    }
    

    public function setAppointment(Request $request){//there
        $x = Experience_expert::find($request->experience_expert_id);
        $us = User::find($request->user_id);
        if($us->wallet < $x->cost)
            return response()->json(['message'=>"sorry.. you don't have enough money"]);
        Appointment::create([
            'experience_expert_id'=>$request->experience_expert_id,
            'user_id'=>$request->user_id,
            'date'=>$request->date,
            'day'=>$request->day,
            'hour'=>$request->hour
        ]);
        $us->update([
            'wallet'=>$us->wallet-=$x->cost,
        ]);
        $y = Expert::find($x->expert_id);
        $z = User::find($y->user_id);
        $z->update([
            'wallet'=>$z->wallet+=$x->cost,
        ]);
        return response()->json(['message'=>'ناطرينك حبيب القلب']);
    }

    public function getAvailableTime(Request $request){
        $exid = $request->expert_id;
        $app = Appointment::where('date', '>=', Carbon::now()->format('Y-m-d') )->whereHas('experience_expert',function(Builder $query)use($exid){
           $query->where('expert_id',$exid); 
        })->get();

        $available_time = Expert::where('id',$exid)->get('available_time');
        $avtime = json_decode($available_time,true);
        $x = $avtime[0]['available_time'];
        $y = json_decode($x,true);

        foreach($app as $reserved){
            $y[$reserved->day] = array_diff($y[$reserved->day],array($reserved->hour));
                 $y[$reserved->day] = array_values($y[$reserved->day]);
        }
        return $y;
    }
    
    public function showAppointment(Request $request){
        $exid = $request->expert_id;
        $apps = Appointment::where('date', '>=', Carbon::now()->format('Y-m-d') )->whereHas('experience_expert',function(Builder $query)use($exid){
            $query->where('expert_id',$exid); 
         })->get();
         $arrkey = ['day','hour','date','user_id','user_name','user_phone'];
         $appointments = [];
         foreach($apps as $app){
            $arrval = [];
            $user = User::find($app->user_id);
            array_push(
                $arrval,
                $app->day,
                $app->hour,
                $app->date,
                $app->user_id,
                $user->name,
                $user->phone
            );
            array_push($appointments,array_combine($arrkey,$arrval));
         }

         return $appointments;
    }
}

