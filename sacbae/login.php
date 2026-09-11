<?php
session_start();

/*
|--------------------------------------------------------------------------
| SACBAE - Login
| I.E.T. María Inmaculada
|--------------------------------------------------------------------------
*/

// Aquí posteriormente puedes conectar la autenticación con MySQL.

$error = ($_GET['error'] ?? '') === 'invalid'
    ? 'Usuario o contraseña incorrectos.'
    : '';
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Ingresar | SACBAE</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Google Fonts -->
    <link rel="preconnect"
          href="https://fonts.googleapis.com">

    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap"
        rel="stylesheet">

    <!-- CSS principal -->
    <link rel="stylesheet"
          href="assets/css/estilos.css">

    <!-- CSS Login -->
    <link rel="stylesheet"
          href="assets/css/login.css">

</head>


<body class="login-page">


<!-- =====================================================
     FONDO
===================================================== -->

<div class="login-background">

    <div class="login-grid"></div>

    <div class="login-glow login-glow-one"></div>

    <div class="login-glow login-glow-two"></div>

</div>


<!-- =====================================================
     NAVBAR
===================================================== -->

<nav class="navbar navbar-dark login-navbar">

    <div class="container">

        <a href="index.php"
           class="navbar-brand d-flex align-items-center gap-2">

            <div class="logo-box">

                <i class="bi bi-fingerprint"></i>

            </div>

            <div>

                <span class="brand-title">
                    SACBAE
                </span>

                <small>
                    Sistema Biométrico
                </small>

            </div>

        </a>


        <a href="index.php"
           class="back-home">

            <i class="bi bi-arrow-left"></i>

            Volver al inicio

        </a>

    </div>

</nav>


<!-- =====================================================
     LOGIN
===================================================== -->

<main class="login-container">

    <div class="login-card">


        <!-- =================================================
             LADO IZQUIERDO
        ================================================== -->

        <div class="login-info">

            <div class="login-info-content">

                <span class="login-label">

                    <span class="login-status"></span>

                    ACCESO INSTITUCIONAL

                </span>


                <h1>

                    Bienvenido a

                    <span>SACBAE</span>

                </h1>


                <p>

                    Sistema de Control de Asistencia
                    Biométrica de la I.E.T. María Inmaculada.

                </p>


                <!-- HUella -->

                <div class="login-fingerprint">

                    <div class="login-ring ring-a"></div>

                    <div class="login-ring ring-b"></div>

                    <div class="login-ring ring-c"></div>

                    <i class="bi bi-fingerprint"></i>

                </div>


                <div class="login-security">

                    <i class="bi bi-shield-check"></i>

                    <div>

                        <strong>
                            Acceso seguro
                        </strong>

                        <small>
                            Plataforma institucional protegida
                        </small>

                    </div>

                </div>

            </div>

        </div>


        <!-- =================================================
             FORMULARIO
        ================================================== -->

        <div class="login-form-container">

            <div class="login-form">


                <div class="form-heading">

                    <div class="form-icon">

                        <i class="bi bi-person-lock"></i>

                    </div>


                    <span>
                        PANEL DE ACCESO
                    </span>


                    <h2>
                        Iniciar sesión
                    </h2>


                    <p>
                        Ingresa tus credenciales para continuar
                    </p>

                </div>


                <?php if (!empty($error)): ?>

                    <div class="login-error">

                        <i class="bi bi-exclamation-circle"></i>

                        <?php echo htmlspecialchars($error); ?>

                    </div>

                <?php endif; ?>


                <!-- FORM -->

                <form action="procesar_login.php"
                      method="POST">


                    <!-- USUARIO -->

                    <div class="input-group-custom">

                        <label for="usuario">

                            Usuario

                        </label>


                        <div class="input-wrapper">

                            <i class="bi bi-person"></i>

                            <input
                                type="text"
                                id="usuario"
                                name="usuario"
                                placeholder="Ingrese su usuario"
                                autocomplete="username"
                                required>

                        </div>

                    </div>


                    <!-- PASSWORD -->

                    <div class="input-group-custom">

                        <label for="password">

                            Contraseña

                        </label>


                        <div class="input-wrapper">

                            <i class="bi bi-lock"></i>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Ingrese su contraseña"
                                autocomplete="current-password"
                                required>


                            <button
                                type="button"
                                class="password-toggle"
                                onclick="mostrarPassword()">

                                <i
                                    id="passwordIcon"
                                    class="bi bi-eye">
                                </i>

                            </button>

                        </div>

                    </div>


                    <!-- OPCIONES -->

                    <div class="login-options">

                        <label class="remember">

                            <input
                                type="checkbox"
                                name="recordar">

                            <span>
                                Recordarme
                            </span>

                        </label>


                        <a href="#"
                           onclick="return false;">

                            ¿Olvidaste tu contraseña?

                        </a>

                    </div>


                    <!-- BOTON -->

                    <button
                        type="submit"
                        class="btn-login-submit">

                        <span>
                            Ingresar al sistema
                        </span>

                        <i class="bi bi-arrow-right"></i>

                    </button>


                </form>


                <!-- INFO -->

                <div class="login-footer-info">

                    <i class="bi bi-info-circle"></i>

                    <span>
                        El acceso está restringido al personal
                        autorizado de la institución.
                    </span>

                </div>


            </div>

        </div>

    </div>

</main>


<!-- =====================================================
     FOOTER
===================================================== -->

<footer class="login-footer">

    <div class="container">

        <div class="login-footer-content">

            <span>
                <i class="bi bi-fingerprint"></i>
                SACBAE
            </span>

            <span>
                I.E.T. María Inmaculada
            </span>

            <span>
                © <?php echo date("Y"); ?>
            </span>

        </div>

    </div>

</footer>


<!-- =====================================================
     JAVASCRIPT
===================================================== -->

<script>

function mostrarPassword() {

    const password =
        document.getElementById("password");

    const icon =
        document.getElementById("passwordIcon");


    if (password.type === "password") {

        password.type = "text";

        icon.classList.remove("bi-eye");

        icon.classList.add("bi-eye-slash");

    } else {

        password.type = "password";

        icon.classList.remove("bi-eye-slash");

        icon.classList.add("bi-eye");

    }

}

</script>


</body>

</html>
