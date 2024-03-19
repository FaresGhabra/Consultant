<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'gender',
        'photo',
        'wallet'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    //one to one relation with expert to make expert can have normal user functionality
    public function expert(){
        return $this->hasOne(Expert::class);
    }

    public function appointments(){
        return $this->hasMany(Appointment::class)->with('expert');
    }

    public function rates(){
        return $this->hasMany(Rate::class);
    }

    // many to many relation with experts where pivot is favorites table
    public function experts(){
        return $this->belongsToMany(Expert::class,'favorites');
    }
}
