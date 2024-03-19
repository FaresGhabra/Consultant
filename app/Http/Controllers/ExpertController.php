<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Expert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpertController extends Controller
{
    public function expert_details(Request $request){
        $request->validate([
            'expert_id' => 'required|integer'
        ]);

        $data = Expert::where('id',$request->expert_id)->with(['experiences','rates'])->get();

        return ApiResponse::sendResponse(200,"success", $data) ;
    }

    public function edit_available_time(Request $request){

        $request->validate([
            'available_time' => 'json'
        ]);

        $expert = Auth::user()->expert;
        $expert->available_time = $request->available_time;
        $expert->save();

        return ApiResponse::sendResponse(200,'available time updated successfully');
    }

    public function show_rates(){
        return ApiResponse::sendResponse(200,'success',Auth::user()->expert->rates);
    }

    public function booked_appointments(){
        $data = Auth::user()->expert->appointments->where('date','>=',now());
       return ApiResponse::sendResponse(200,'your appointments',$data);
    }

    public function available_time(){
        $booked_appointments = Auth::user()->expert->appointments->where('date','>=',now())->toArray();
        $available_time = Auth::user()->expert->available_time;
        $available_time = json_decode($available_time,true);
        
        foreach($booked_appointments as $appointment)
        {
            $appointment_interval = [
                'start'=>strtotime($appointment['date'])  ,
                'end'=>strtotime($appointment['date']) +  ($appointment['duration'] * 60 )
            ];

            $start_parse = date_parse(gmdate('Y-m-d H:i',$appointment_interval['start']));
            $end_parse  = date_parse(gmdate('Y-m-d H:i',$appointment_interval['end']));
            $appointment_day = date('D',$appointment_interval['start']);
            
            $appointment_interval['start'] = ($start_parse['hour'] * 60) + $start_parse['minute'];
            $appointment_interval['end'] = ($end_parse[ 'hour'] * 60 )+ $end_parse['minute'];

            for($i=0;$i<sizeof($available_time[$appointment_day]);$i++){
                if($available_time[$appointment_day][$i]['start'] == $appointment_interval['start'] && $appointment_interval['end'] == $available_time[$appointment_day][$i]['end']){
                    array_splice($available_time[$appointment_day],$i,1);
                }
                else if($available_time[$appointment_day][$i]['start'] < $appointment_interval['start'] && $appointment_interval['end'] == $available_time[$appointment_day][$i]['end']){
                    $available_time[$appointment_day][$i]['end'] = $appointment_interval['start'];
                }
                else if($available_time[$appointment_day][$i]['start'] == $appointment_interval['start'] && $appointment_interval['end'] < $available_time[$appointment_day][$i]['end']){
                    $available_time[$appointment_day][$i]['start'] = $appointment_interval['end'];
                }
                else if($available_time[$appointment_day][$i]['start'] < $appointment_interval['start'] && $appointment_interval['end'] < $available_time[$appointment_day][$i]['end']){
                    array_push($available_time[$appointment_day],['start'=>$appointment_interval['end'],'end'=>$available_time[$appointment_day][$i]['end']]);
                    $available_time[$appointment_day][$i]['end'] = $appointment_interval['start'];
                }
            }
        }
        return ApiResponse::sendResponse(200,'Expert available time',$available_time);
    }
}
