<?php

namespace App\Http\Controllers;

use App\Models\Experience;
use App\Models\Experience_expert;
use App\Models\Expert;
use Illuminate\Http\Request;

class ExperienceController extends Controller
{
    public function getexperinces(){
        return Experience::orderBy('id')->get();
    }



    public function getexperts(Request $request){
        
        $experts1 = Experience_expert::where('experience_id',$request->experience_id)->get(['id','expert_id'])->toArray();
        
        if(!$experts1)
            return response()->json(['message'=>'there is no experts for this experience.. google your problem']);

        $keyarr = array('expert_id','name','experience_expert_id','description','cost','address','photo','email','phone');
        foreach($experts1 as $index){
            $expid = $index['expert_id'];
            $ex = Expert::find($expid);
            $us = $ex->user;
            $experts = [];
            foreach($ex->experiences as $experience){
                $arr = [];
                if(file_exists(public_path('imegs/'.$us->phone.".jpg")))
                array_push($arr,
                $ex->id,
                $us->name,
                $experience->pivot->id,
                $experience->pivot->description,
                $experience->pivot->cost,
                $ex->address,
                base64_encode(file_get_contents(base_path("public\imegs\\".$us->phone.".jpg"))),
                $us->email,
                $us->phone
            );
            else
            array_push($arr,
            $ex->id,
            $us->name,
            $experience->pivot->id,
            $experience->pivot->description,
            $experience->pivot->cost,
            $ex->address,
            null,
            $us->email,
            $us->phone
            );

            $arr = array_combine($keyarr,$arr);
            array_push($experts,$arr);
            }
        }
        
        return $experts;
    }
}
