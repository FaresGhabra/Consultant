<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Appointment;
use App\Models\Expert;
use App\Models\Rate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function add_favorite(Request $request){
        $request->validate([
            'expert_id' => 'required|integer'
        ]);

        Auth::user()->experts()->attach($request->expert_id);

        return ApiResponse::sendResponse(200,'added to favorites successfully');
    }

    public function show_favorites(){
        return ApiResponse::sendResponse(200,'success',Auth::user()->experts);
    }

    public function unfavorite(Request $request){
        $request->validate([
            'expert_id' => 'required|integer'
        ]);

        Auth::user()->experts()->detach($request->expert_id);

        return ApiResponse::sendResponse(200,'deleted from favorites successfully');
    }

    public function rate(Request $request){
        $request->validate([
            'stars' => 'required|integer',
            'review' => 'string|required',
            'expert_id' => 'required|integer',
        ]);

        $expert = Expert::find($request->expert_id);
        $expert->stars += $request->stars;
        $expert->review_num++;
        $expert->save();

        $data = Rate::create([
            'user_id' => Auth::user()->id,
            'expert_id' => $request->expert_id,
            'stars' => $request->stars,
            'review' => $request->review
        ]);

        return ApiResponse::sendResponse(201,'expert rated successfully',$data);
    }

    public function book_appointment(Request $request){
        $request->validate([
            'expert_id' => 'required|integer',
            'pivot_id' => 'required|integer',
            'date' => 'required|date',
            'duration' => 'required|integer'
        ]);
        
        $appointment = Appointment::create([
            'user_id' => Auth::user()->id,
            'expert_id' => $request->expert_id,
            'experience_expert_id' => $request->pivot_id,
            'date' => $request->date,
            'duration' => $request->duration
        ]);
        
        $cost  = DB::table('experience_expert')->where('id',$appointment->experience_expert_id)->value('cost');
        $user = Auth::user();
        $user->wallet -= $cost;
        $user->save();

        $expert = Expert::find($request->expert_id)->user;
        $expert->wallet += $cost;
        $expert->save();

        return ApiResponse::sendResponse(201,'Appointment Booked Successfully');
    }

    public function search(string $query){
        $experts_ids1 = DB::table('users')
                            ->join('experts','users.id','=','experts.user_id')
                            ->where('users.name','like', '%'.$query.'%')
                            ->select('experts.id')
                            ->get()->toArray();
        $ids = [];                
        foreach($experts_ids1 as $id){
            array_push( $ids , $id->id ); 
        }                                      
        $experts_ids2 = DB::table('experience_expert')
                    ->join('experiences','experience_expert.experience_id','=','experiences.id')
                    ->Where('experiences.name','like', '%'.$query.'%')
                    ->get('experience_expert.expert_id');
        foreach($experts_ids2 as $id){
            array_push( $ids , $id->expert_id ); 
        }  
        $ids = array_unique($ids);           
        $data = Expert::whereIn('id',$ids)->with(['user','rates','experiences'])->get();

        return ApiResponse::sendResponse(200,'search result',$data);

    }

    public function booked_appointments(){
        $booked_appointments = Auth::user()->appointments->where('date','>=',now());

        return ApiResponse::sendResponse(200,'your booked appointments',$booked_appointments);
    }
}
