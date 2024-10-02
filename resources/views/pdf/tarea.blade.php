<!DOCTYPE html>
<html>

<head>
    <title>Tarea: {{ $tarea->titulo }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
            background-color: #f8f8f8;
            color: #333;
        }

        h1 {
            text-align: center;
            color: #007bff;
        }

        p {
            font-size: 16px;
            line-height: 1.5;
        }

        .estudiantes {
            margin-top: 30px;
            border-collapse: collapse;
            width: 100%;
        }

        .estudiantes th,
        .estudiantes td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .estudiantes th {
            background-color: #007bff;
            color: white;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #999;
        }

        .nota-alta {
            background-color: #d4edda;
        }

        .nota-baja {
            background-color: #f8d7da;
        }
    </style>
</head>

<body>
    <h1>{{ $tarea->titulo }}</h1>
    <p><strong>Descripción:</strong> {{ $tarea->descripcion }}</p>

    {{-- Verificar si hay respuestas --}}
    @if (count($tarea->respuestas) > 0)
        <h3>No. de respuestas recibidas: {{ count($tarea->respuestas) }}</h3>
        <table class="estudiantes">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre Completo</th>
                    <th>Documento</th>
                    <th>Respuesta</th>
                    <th>Fecha de Entrega</th>
                    <th>Nota</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tarea->respuestas as $respuesta)
                    <tr>
                        <td>{{ $respuesta->estudiante->id }}</td>
                        <td>{{ $respuesta->estudiante->name }} {{ $respuesta->estudiante->apellido }}</td>
                        <td>{{ $respuesta->estudiante->numero_documento }}</td>
                        <td>{{ $respuesta->texto_respuesta }}</td>
                        <td>{{ $respuesta->fecha_entrega }}</td>
                        <td class="{{ $respuesta->nota && $respuesta->nota->nota >= 70 ? 'nota-alta' : 'nota-baja' }}">
                            {{ $respuesta->nota ? $respuesta->nota->nota : 'No calificado aún' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No hay respuestas para esta tarea.</p>
    @endif

    <div class="footer">
        <p>Generado por {{ config('app.name') }}</p>
    </div>
</body>

</html>
