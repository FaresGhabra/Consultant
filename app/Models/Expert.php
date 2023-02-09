<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expert extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'address',
        'available_time',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function experiences(){
        return $this->belongsToMany(Experience::class)->withPivot('id','description','cost');
    }

    public function appointments(){
        return $this->hasManyThrough(Appointment::class,Experience_expert::class,'expert_id','experience_expert_id');
    }
}

