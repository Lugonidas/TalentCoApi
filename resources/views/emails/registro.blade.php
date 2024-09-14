<!DOCTYPE html>
<html>

<head>
    <title>Bienvenido a la Plataforma</title>
</head>

<body>
    <h1>Hola, {{ $usuario->name }} {{ $usuario->apellido }}!</h1>
    <p>Gracias por registrarte en nuestra plataforma.</p>

    @if ($verificationUrl)
        <p>Para completar tu registro, por favor verifica tu correo haciendo clic en el siguiente enlace:</p>
        <a href="{{ $verificationUrl }}">Verificar mi correo</a>
    @else
        <p>Tu registro ha sido completado exitosamente.</p>
    @endif

    <p>Si no has solicitado este registro, por favor ignora este correo.</p>
</body>

</html>
