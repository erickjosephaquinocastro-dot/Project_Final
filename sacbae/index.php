<?php
// SACBAE - Sistema de Control de Asistencia Biométrica
$anio = date("Y");
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SACBAE | I.E.T. María Inmaculada</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- CSS personalizado -->
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>

<body>
    <!-- MENÚ LATERAL INSTITUCIONAL -->
<div class="side-menu">

    <button class="side-menu-toggle" id="sideMenuToggle" type="button">
        <i class="bi bi-list"></i>
    </button>

    <div class="side-menu-panel" id="sideMenuPanel">

        <div class="side-menu-header">
            <div>
                <span>INSTITUCIÓN</span>
                <h3>María Inmaculada</h3>
            </div>

            <button id="sideMenuClose" type="button">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="side-menu-content">

            <!-- VISIÓN -->
            <div class="institution-card">
                <div class="institution-icon">
                    <i class="bi bi-eye"></i>
                </div>

                <div>
                    <h4>Visión</h4>
                    <p>
                        Ser una institución educativa reconocida por brindar
                        una formación integral, innovadora y de calidad,
                        preparando estudiantes para afrontar los desafíos
                        del futuro.
                    </p>
                </div>
            </div>

            <!-- MISIÓN -->
            <div class="institution-card">
                <div class="institution-icon">
                    <i class="bi bi-bullseye"></i>
                </div>

                <div>
                    <h4>Misión</h4>
                    <p>
                        Brindar una educación integral basada en valores,
                        conocimientos y competencias, promoviendo el
                        desarrollo personal, académico y tecnológico de
                        nuestros estudiantes.
                    </p>
                </div>
            </div>

            <!-- VALORES -->
            <div class="institution-card">
                <div class="institution-icon">
                    <i class="bi bi-heart"></i>
                </div>

                <div>
                    <h4>Valores</h4>

                    <div class="values-list">
                        <span>Conciencia de derechos</span>
                        <span>Libertad y responsabilidad</span>
                        <span>Diálogo y concertación</span>
                        <span>Respeto</span>
                        <span>Equidad en la enseñanza</span>
                        <span>Confianza en la persona</span>
                        <span>Justicia</span>
                        <span>Empatía</span>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

<!-- FONDO DEL MENÚ -->
<div class="side-menu-overlay" id="sideMenuOverlay"></div>
<script>
const sideMenuToggle = document.getElementById("sideMenuToggle");
const sideMenuPanel = document.getElementById("sideMenuPanel");
const sideMenuClose = document.getElementById("sideMenuClose");
const sideMenuOverlay = document.getElementById("sideMenuOverlay");

sideMenuToggle.addEventListener("click", function () {
    sideMenuPanel.classList.add("active");
    sideMenuOverlay.classList.add("active");
});

sideMenuClose.addEventListener("click", function () {
    sideMenuPanel.classList.remove("active");
    sideMenuOverlay.classList.remove("active");
});

sideMenuOverlay.addEventListener("click", function () {
    sideMenuPanel.classList.remove("active");
    sideMenuOverlay.classList.remove("active");
});
</script>
    <!-- ================= NAVBAR ================= -->

    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">

        <div class="container">

            <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">

                <div class="logo-box">
                    <i class="bi bi-fingerprint"></i>
                </div>

                <div>
                    <span class="brand-title">SACBAE</span>
                    <small>Sistema Biométrico</small>
                </div>

            </a>

            <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#menu">

                <span class="navbar-toggler-icon"></span>

            </button>

            <div class="collapse navbar-collapse" id="menu">

                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-3">

                    <li class="nav-item">
                        <a class="nav-link active" href="#inicio">
                            Inicio
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="#caracteristicas">
                            Características
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="#sistema">
                            Sistema
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="login.php" class="btn btn-login">
                            <i class="bi bi-box-arrow-in-right me-2"></i>
                            Ingresar
                        </a>
                    </li>

                </ul>

            </div>

        </div>

    </nav>


    <!-- ================= HERO ================= -->

    <section id="inicio" class="hero">

        <div class="hero-grid"></div>

        <div class="container">

            <div class="row align-items-center min-vh-100">

                <!-- TEXTO -->

                <div class="col-lg-7 hero-content">

                    <div class="status-badge mb-4">

                        <span class="status-dot"></span>

                        SISTEMA INSTITUCIONAL

                    </div>


                    <h1>

                        Control de Asistencia

                        <span>Biométrica</span>

                    </h1>


                    <h2>

                        I.E.T. María Inmaculada

                    </h2>


                    <p class="hero-description">

                        SACBAE es una plataforma web diseñada para optimizar
                        el registro, control y seguimiento de la asistencia
                        de estudiantes mediante tecnología biométrica
                        Hikvision.

                    </p>


                    <div class="hero-buttons mt-4">

                        <a href="login.php"
                            class="btn btn-primary btn-lg">

                            <i class="bi bi-shield-lock-fill me-2"></i>

                            Acceder al sistema

                            <i class="bi bi-arrow-right ms-2"></i>

                        </a>


                        <a href="#caracteristicas"
                            class="btn btn-outline-light btn-lg">

                            <i class="bi bi-info-circle me-2"></i>

                            Conocer SACBAE

                        </a>

                    </div>


                    <!-- INFORMACIÓN -->

                    <div class="hero-info mt-5">

                        <div>

                            <i class="bi bi-fingerprint"></i>

                            <div>
                                <strong>Biometría</strong>
                                <small>Registro automático</small>
                            </div>

                        </div>


                        <div>

                            <i class="bi bi-database-check"></i>

                            <div>
                                <strong>Información</strong>
                                <small>Datos centralizados</small>
                            </div>

                        </div>


                        <div>

                            <i class="bi bi-shield-check"></i>

                            <div>
                                <strong>Seguridad</strong>
                                <small>Acceso controlado</small>
                            </div>

                        </div>

                    </div>

                </div>


                <!-- TARJETA BIOMÉTRICA -->

                <div class="col-lg-5 mt-5 mt-lg-0">

                    <div class="biometric-wrapper">

                        <div class="glow"></div>

                        <div class="biometric-card">

                            <div class="card-header-custom">

                                <div class="device-status">

                                    <span></span>

                                    DISPOSITIVO CONECTADO

                                </div>

                                <i class="bi bi-three-dots"></i>

                            </div>


                            <div class="fingerprint-container">

                                <div class="fingerprint-ring ring-1"></div>

                                <div class="fingerprint-ring ring-2"></div>

                                <div class="fingerprint-ring ring-3"></div>

                                <i class="bi bi-fingerprint fingerprint-icon"></i>

                            </div>


                            <h3>
                                Sistema Biométrico
                            </h3>


                            <p>
                                Tecnología Hikvision
                            </p>


                            <div class="system-status">

                                <div class="status-icon">

                                    <i class="bi bi-check-lg"></i>

                                </div>

                                <div>

                                    <strong>Sistema operativo</strong>

                                    <small>
                                        Servicio disponible
                                    </small>

                                </div>

                                <span class="online">
                                    ONLINE
                                </span>

                            </div>


                            <div class="card-footer-custom">

                                <span>
                                    <i class="bi bi-clock"></i>
                                    Registro en tiempo real
                                </span>

                                <span>
                                    <i class="bi bi-wifi"></i>
                                    Conectado
                                </span>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>


    <!-- ================= CARACTERÍSTICAS ================= -->

    <section id="caracteristicas" class="features-section">

        <div class="container">

            <div class="section-heading text-center">

                <span>FUNCIONALIDADES</span>

                <h2>
                    Una solución para la gestión de asistencia
                </h2>

                <p>
                    SACBAE integra tecnología biométrica y una plataforma
                    web para facilitar la administración institucional.
                </p>

            </div>


            <div class="row g-4 mt-4">

                <div class="col-md-6 col-lg-3">

                    <div class="feature-card">

                        <div class="feature-icon">
                            <i class="bi bi-fingerprint"></i>
                        </div>

                        <h4>
                            Registro Biométrico
                        </h4>

                        <p>
                            Identificación mediante dispositivos
                            biométricos Hikvision.
                        </p>

                    </div>

                </div>


                <div class="col-md-6 col-lg-3">

                    <div class="feature-card">

                        <div class="feature-icon">
                            <i class="bi bi-calendar-check"></i>
                        </div>

                        <h4>
                            Control de Asistencia
                        </h4>

                        <p>
                            Registro organizado de entradas y
                            salidas de estudiantes.
                        </p>

                    </div>

                </div>


                <div class="col-md-6 col-lg-3">

                    <div class="feature-card">

                        <div class="feature-icon">
                            <i class="bi bi-bar-chart-line"></i>
                        </div>

                        <h4>
                            Reportes
                        </h4>

                        <p>
                            Consulta y seguimiento de información
                            relacionada con la asistencia.
                        </p>

                    </div>

                </div>


                <div class="col-md-6 col-lg-3">

                    <div class="feature-card">

                        <div class="feature-icon">
                            <i class="bi bi-shield-lock"></i>
                        </div>

                        <h4>
                            Seguridad
                        </h4>

                        <p>
                            Acceso controlado para proteger la
                            información institucional.
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </section>


    <!-- ================= SISTEMA ================= -->

    <section id="sistema" class="system-section">

        <div class="container">

            <div class="row align-items-center g-5">

                <div class="col-lg-6">

                    <span class="section-label">
                        SACBAE
                    </span>

                    <h2>
                        Tecnología al servicio
                        de la comunidad educativa
                    </h2>

                    <p>

                        El Sistema de Control de Asistencia Biométrica
                        permite modernizar el proceso tradicional de
                        registro de asistencia, reduciendo procesos
                        manuales y facilitando el acceso a información
                        organizada.

                    </p>


                    <div class="check-list">

                        <div>
                            <i class="bi bi-check-circle-fill"></i>
                            Registro mediante huella biométrica
                        </div>

                        <div>
                            <i class="bi bi-check-circle-fill"></i>
                            Información centralizada
                        </div>

                        <div>
                            <i class="bi bi-check-circle-fill"></i>
                            Consulta de registros
                        </div>

                        <div>
                            <i class="bi bi-check-circle-fill"></i>
                            Gestión institucional
                        </div>

                    </div>


                    <a href="login.php"
                        class="btn btn-primary mt-4">

                        Ingresar a SACBAE

                        <i class="bi bi-arrow-right ms-2"></i>

                    </a>

                </div>


                <div class="col-lg-6">

                    <div class="dashboard-preview">

                        <div class="dashboard-top">

                            <span>
                                <i class="bi bi-grid-1x2-fill"></i>
                                Panel SACBAE
                            </span>

                            <span class="dashboard-online">
                                ● Sistema activo
                            </span>

                        </div>


                        <div class="dashboard-body">

                            <div class="mini-stat">

                                <i class="bi bi-people"></i>

                                <div>
                                    <small>Estudiantes</small>
                                    <strong>Controlados</strong>
                                </div>

                            </div>


                            <div class="mini-stat">

                                <i class="bi bi-person-check"></i>

                                <div>
                                    <small>Asistencia</small>
                                    <strong>Biométrica</strong>
                                </div>

                            </div>


                            <div class="mini-stat">

                                <i class="bi bi-device-ssd"></i>

                                <div>
                                    <small>Dispositivo</small>
                                    <strong>Hikvision</strong>
                                </div>

                            </div>


                            <div class="activity">

                                <div class="activity-title">
                                    Actividad del sistema
                                </div>

                                <div class="activity-line">

                                    <span></span>

                                    Registro biométrico

                                    <small>
                                        En tiempo real
                                    </small>

                                </div>

                                <div class="activity-line">

                                    <span></span>

                                    Datos sincronizados

                                    <small>
                                        Sistema
                                    </small>

                                </div>

                                <div class="activity-line">

                                    <span></span>

                                    Sistema operativo

                                    <small>
                                        Activo
                                    </small>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>


    <!-- ================= CTA ================= -->

    <section class="cta-section">

        <div class="container">

            <div class="cta-box">

                <div>

                    <span>
                        I.E.T. MARÍA INMACULADA
                    </span>

                    <h2>
                        Ingresa al sistema SACBAE
                    </h2>

                    <p>
                        Administra y consulta la información de asistencia
                        desde una plataforma centralizada.
                    </p>

                </div>


                <a href="login.php"
                    class="btn btn-light btn-lg">

                    Acceder ahora

                    <i class="bi bi-arrow-right ms-2"></i>

                </a>

            </div>

        </div>

    </section>


    <!-- ================= FOOTER ================= -->

    <footer>

        <div class="container">

            <div class="row align-items-center">

                <div class="col-md-6">

                    <div class="footer-brand">

                        <i class="bi bi-fingerprint"></i>

                        <strong>SACBAE</strong>

                    </div>

                    <p>
                        Sistema de Control de Asistencia Biométrica
                    </p>

                </div>


                <div class="col-md-6 text-md-end">

                    <p class="mb-1">
                        I.E.T. María Inmaculada
                    </p>

                    <small>
                        &copy; <?php echo $anio; ?>
                        SACBAE. Todos los derechos reservados.
                    </small>

                </div>

            </div>

        </div>

    </footer>


    <!-- Bootstrap JS -->

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    </script>

</body>

</html>
