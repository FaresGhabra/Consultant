<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function experts(){
        return $this->belongsToMany(Expert::class)->withPivot(['title','description','cost']);
    }
}
