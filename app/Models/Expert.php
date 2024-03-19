<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expert extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'available_time'
    ];

    //one to one relation with expert to make expert can have normal user functionality
    public function user(){
        return $this->belongsTo(User::class);
    }

    public function experiences(){
        return $this->belongsToMany(Experience::class,)->withPivot(['id','title','description','cost']);
    }

    public function appointments(){
        return $this->hasMany(Appointment::class);
    }

    public function rates(){
        return $this->hasMany(Rate::class);
    }

    // many to many relation with users where pivot is favorites table
     public function users(){
        return $this->belongsToMany(User::class,'favorites');
     }

}
