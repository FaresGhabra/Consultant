<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'experience_expert_id',
        'user_id',
        'date',
        'day',
        'hour'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function experience_expert(){
        return $this->belongsTo(Experience_expert::class);
    }
}
