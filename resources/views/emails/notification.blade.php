<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0">
    <title>¡NUEVA NOTA CREADA!</title>
    <style>
    .success {
        background-color:#1a73e8;
        color: #FFFFFF !important;
        padding: 8px 20px;
        text-decoration:none;
        font-weight:bold;
        border-radius:5px;
        cursor:pointer;
    }

    .success:hover {
        background-color:#4285f4;
        color: #FFFFFF;
    }
    </style>
</head>
<body>
    <h1 style="text-align: center;">¡NUEVA NOTA CREADA!</h1>
    <p>Hola,</p>
    <p>Una nueva nota acaba de ser creada en el grupo <b>{{ $group->nombre }}</b> por <b>{{ $user->name }}</b>.</p>
    <br>
    <p>Saludos,</p>
    <p>IDBI</p>
</body>
</html>
