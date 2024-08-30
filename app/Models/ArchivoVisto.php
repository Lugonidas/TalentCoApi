<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchivoVisto extends Model
{
    use HasFactory;

    protected $table = 'archivos_vistos';

    protected $fillable = [
        'id_usuario',
        'id_archivo',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function archivo()
    {
        return $this->belongsTo(ArchivoLeccion::class, 'id_archivo');
    }
}
