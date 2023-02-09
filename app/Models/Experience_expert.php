<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Experience_expert extends Model
{
    use HasFactory;
    protected $fillable = [
        'expert_id',
        'experience_id',
        'description',
        'cost'
    ];
    
    protected $table = 'experience_expert';
    
    public function experts(){
        return $this->hasMany(Expert::class);
    }
    public function experiences(){
        return $this->hasMany(Experience::class);
    }
}
