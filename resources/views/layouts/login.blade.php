<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>@yield('title', 'Login')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        body {
            background-image: url('/images/loginWallpaper.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            min-height: 100vh;
        }

        .overlay {
            background-color: rgba(255, 255, 255, 0.85);
            min-height: 100vh;
        }
    </style>
</head>
<body>
    <div class="overlay d-flex align-items-center justify-content-center min-vh-100">
        @yield('content') <!-- Contenido del login -->
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
