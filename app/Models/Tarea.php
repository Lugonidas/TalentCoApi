<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Tarea extends Model
{
        use HasFactory, HasApiTokens, Notifiable;

        protected $table = "evaluaciones";

        protected $fillable = [
                'id_docente',
                'id_curso',
                'tipo',
                'titulo',
                'archivo',
                'descripcion',
                'nota_maxima',
                'fecha_inicio',
                'fecha_fin',
                'estado',
        ];

        public function respuestas()
        {
                return $this->hasMany(RespuestaEstudiante::class, 'id_evaluacion');
        }
}
