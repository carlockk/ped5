<?php
session_start();
define('WP_USE_THEMES', false); // Evita cargar los temas de WordPress
require_once('../wp-load.php'); // Incluye el archivo wp-load.php

// Verificar si se ha enviado el formulario de inicio de sesión
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Autenticar al usuario utilizando la función wp_authenticate()
    $user = wp_authenticate($username, $password);

    // Verificar si la autenticación fue exitosa
    if (!is_wp_error($user)) {
        // Credenciales válidas, redirigir al sitio web
        wp_set_auth_cookie($user->ID); // Establece la cookie de autenticación de WordPress
        wp_redirect('ped5.php'); // Redirige a la página deseada
        exit();
    } else {
        // Credenciales inválidas, mostrar mensaje de error
        $error = 'Credenciales inválidas';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login.</title>
    <meta name="viewport" content="width=device-width, initial-scale=0.8">
    
<!-- aqui pongo el fondo segun la hora -->
<style>
        body {
            margin: 0;
            padding: 0;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-color: #000;
        }
    </style>
    <script>
        function setDayNightBackground() {
            var date = new Date();
            var hour = date.getHours();

            var backgroundImage = '';

            if (hour >= 6 && hour < 18) { // Horas de día (6:00 - 17:59)
                backgroundImage = 'img/dia.jpg';
            } else { // Horas de noche (18:00 - 5:59)
                backgroundImage = 'img/noche.jpg';
            }

            document.body.style.backgroundImage = "url('" + backgroundImage + "')";
        }

        window.onload = function() {
            setDayNightBackground();
            crearLuciernagas();
        };
    </script>
    <!-- aqui termino el fondo segun la hora -->
    
    
    <script type="text/javascript">
        function refreshPage() {
            location.reload();
        }

        setInterval(refreshPage, 120000); // 120000 milisegundos = 2 minutos
    </script>
    
    
    <link rel="stylesheet" type="text/css" href="css_print/login.css">
    
</head>
<body>
    <table style="width: 100%; height: 90vh; display: flex; justify-content: center; align-items: center;">
        <tr>
            <td>
               <div class="container">
    <h1><a href="https://www.coffeewaffles.cl">
        <?php
        // Obtener la URL del favicon del sitio
        $favicon_url = get_site_icon_url();
        
        if (!empty($favicon_url)): ?>
            <img src="<?php echo $favicon_url; ?>" alt="Logotipo de WordPress" style="width: auto; height: auto; max-width: 100px; max-height: 100px;">
        <?php endif; ?>
    </a></h1>
                    <?php if (isset($error)): ?>
                        <p class="error"><?php echo $error; ?></p>
                    <?php endif; ?>
                    <form method="POST" action="">
                        <label for="username">Usuario:</label>
                        <input type="text" id="username" name="username" required><br><br>
                        <label for="password">Contraseña:</label>
                        <input type="password" id="password" name="password" required><br><br>
                        <input type="submit" name="login" value="Iniciar sesión" style="margin: 0 auto; display: block;">
                    </form>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>

