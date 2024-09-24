<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class NotasEstudiantes extends Model
{
    use HasFactory, HasApiTokens, Notifiable;
    protected $fillable = [
        'id_evaluacion',
        'id_estudiante',
        'nota',
    ];


    public function estudiante()
    {
        return $this->belongsTo(User::class, 'id_estudiante');
    }
    
    public function tarea()
    {
        return $this->belongsTo(Tarea::class, 'id_evaluacion');
    }
    
}
