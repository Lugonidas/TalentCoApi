<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class RespuestaEstudiante extends Model
{
    use HasFactory, HasApiTokens, Notifiable;

    protected $table = "respuestas_estudiantes";

    protected $fillable = [
        'id_evaluacion',
        'id_estudiante',
        'archivo',
        'texto_respuesta',
        'fecha_entrega',
    ];

    // Relación con el modelo Tarea
    public function tarea()
    {
        return $this->belongsTo(Tarea::class, 'id_evaluacion');
    }

    // Relación con el modelo User (o Estudiante)
    public function estudiante()
    {
        return $this->belongsTo(User::class, 'id_estudiante');
    }

    public function nota()
    {
        return $this->hasOne(NotasEstudiantes::class, 'id_estudiante', 'id_estudiante')
            ->whereColumn('id_evaluacion', 'id_evaluacion');
    }
}
