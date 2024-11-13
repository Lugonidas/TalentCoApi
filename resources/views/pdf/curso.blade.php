<!DOCTYPE html>
<html>

<head>
    <title>Curso: {{ $curso->titulo }}</title>
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

        ul {
            list-style: none;
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

        .tareas {
            margin-top: 30px;
            width: 100%;
            border-collapse: collapse;
        }

        .tareas th,
        .tareas td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .tareas th {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>

<body>

    <h1>{{ $curso->titulo }}</h1>
    <p><strong>Descripción:</strong> {{ $curso->descripcion }}</p>
    <p><strong>Duración:</strong> {{ $curso->duracion }} horas</p>
    <p><strong>Docente:</strong> {{ $curso->docente->name }}</p>

    {{-- Verificar si hay estudiantes inscritos --}}
    @if (count($curso->estudiantes) > 0)
        <h3>No. de estudiantes inscritos: {{ count($curso->estudiantes) }} </h3>
        <table class="estudiantes">
            <thead>
                <tr>
                    <th>Cédula</th>
                    <th>Nombre Completo</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($curso->estudiantes as $estudiante)
                    <tr>
                        <td>{{ $estudiante->numero_documento }}</td>
                        <td>{{ $estudiante->name }} {{ $estudiante->apellido }}</td>
                        <td>{{ $estudiante->email }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No hay estudiantes inscritos en este curso.</p>
    @endif


    <div class="footer">
        <p>Generado por {{ config('app.name') }}</p>
    </div>
</body>

</html>
