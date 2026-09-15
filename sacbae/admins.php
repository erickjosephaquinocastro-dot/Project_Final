<?php

declare(strict_types=1);

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'auth.php';
require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'database.php';
sacbaeRequireAdministrator();

$database = sacbaeDatabase();
$error = '';
$success = '';

if ($database === null) {
    $error = 'No fue posible conectar con la base de datos.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $fullName = trim((string) ($_POST['full_name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!preg_match('/^[A-Za-z0-9_.-]{3,64}$/', $username) || $fullName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
        $error = 'Usa un usuario válido, correo válido y una contraseña de al menos 8 caracteres.';
    } else {
        try {
            $statement = $database->prepare(
                'INSERT INTO users (username, full_name, email, password_hash, role)
                 VALUES (:username, :full_name, :email, :password_hash, \'admin\')'
            );
            $statement->execute([
                'username' => $username,
                'full_name' => $fullName,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ]);
            $success = 'Administrador creado. Ya puede iniciar sesión desde la página de acceso.';
        } catch (PDOException) {
            $error = 'El usuario o el correo ya están registrados.';
        }
    }
}

$administrators = $database === null ? [] : $database->query(
    'SELECT username, full_name, email, active, created_at FROM users ORDER BY full_name'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Administradores | SACBAE</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Custom Styles -->
    <link rel="stylesheet" href="assets/css/estilos.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body class="dash-page">
    <!-- Background -->
    <div class="dash-bg">
        <div class="dash-grid"></div>
        <div class="dash-glow dash-glow-1"></div>
        <div class="dash-glow dash-glow-2"></div>
    </div>

    <!-- Navbar -->
    <nav class="dash-navbar">
        <div class="container">
            <a href="<?= htmlspecialchars(sacbaeUrl('sacbae/'), ENT_QUOTES, 'UTF-8') ?>" class="navbar-brand d-flex align-items-center gap-2" style="text-decoration:none">
                <div class="logo-box"><i class="bi bi-fingerprint"></i></div>
                <div>
                    <span class="brand-title">SACBAE</span>
                    <small>Sistema Biométrico</small>
                </div>
            </a>
            <ul class="dash-nav-links">
                <li><a href="<?= htmlspecialchars(sacbaeUrl('modules/hikvision/web/dashboard.php'), ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-grid-1x2"></i> Panel</a></li>
                <li><a href="<?= htmlspecialchars(sacbaeUrl('sacbae/students.php'), ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-people"></i> Estudiantes</a></li>
                <li><a href="<?= htmlspecialchars(sacbaeUrl('sacbae/devices.php'), ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-hdd-network"></i> Dispositivos</a></li>
                <li><a href="<?= htmlspecialchars(sacbaeUrl('sacbae/admins.php'), ENT_QUOTES, 'UTF-8') ?>" class="active"><i class="bi bi-shield-lock"></i> Administradores</a></li>
                <li><a href="<?= htmlspecialchars(sacbaeUrl('sacbae/logout.php'), ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main -->
    <main class="dash-main">
        <div class="container">
            <!-- Page header -->
            <div class="dash-page-header">
                <span class="dash-page-label"><span class="dot"></span> GESTIÓN DE ACCESOS</span>
                <h1>Panel de <span>Administradores</span></h1>
                <p>Crea y administra las cuentas de acceso al sistema SACBAE.</p>
            </div>

            <!-- Alerts -->
            <?php if ($error !== ''): ?>
                <div class="dash-alert dash-alert-error mb-4">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success !== ''): ?>
                <div class="dash-alert dash-alert-success mb-4">
                    <i class="bi bi-check-circle-fill"></i>
                    <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <!-- Content cards -->
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="dash-card">
                        <div class="dash-card-header">
                            <h2 class="dash-card-title"><span class="dash-card-icon"><i class="bi bi-person-plus-fill"></i></span> Crear administrador</h2>
                        </div>
                        <form method="post">
                            <div class="dash-form-group mb-3">
                                <label class="form-label">Usuario</label>
                                <div class="dash-input-wrapper">
                                    <i class="bi bi-person dash-input-icon"></i>
                                    <input type="text" name="username" class="dash-input" maxlength="64" required placeholder="Nombre de usuario">
                                </div>
                            </div>
                            
                            <div class="dash-form-group mb-3">
                                <label class="form-label">Nombre completo</label>
                                <div class="dash-input-wrapper">
                                    <i class="bi bi-person-vcard dash-input-icon"></i>
                                    <input type="text" name="full_name" class="dash-input" maxlength="160" required placeholder="Nombres y apellidos">
                                </div>
                            </div>

                            <div class="dash-form-group mb-3">
                                <label class="form-label">Correo institucional</label>
                                <div class="dash-input-wrapper">
                                    <i class="bi bi-envelope dash-input-icon"></i>
                                    <input type="email" name="email" class="dash-input" maxlength="190" required placeholder="correo@institucion.edu.co">
                                </div>
                            </div>

                            <div class="dash-form-group mb-4">
                                <label class="form-label">Contraseña temporal</label>
                                <div class="dash-input-wrapper">
                                    <i class="bi bi-lock dash-input-icon"></i>
                                    <input type="password" name="password" class="dash-input" minlength="8" required placeholder="Mínimo 8 caracteres">
                                </div>
                            </div>

                            <button type="submit" class="dash-btn dash-btn-primary w-100">
                                <i class="bi bi-person-plus-fill"></i> Crear administrador
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-lg-8 mb-4">
                    <div class="dash-card h-100">
                        <div class="dash-card-header">
                            <h2 class="dash-card-title"><span class="dash-card-icon"><i class="bi bi-shield-lock"></i></span> Administradores del sistema</h2>
                        </div>
                        <div class="dash-table-wrapper">
                            <table class="dash-table">
                                <thead>
                                    <tr>
                                        <th>Usuario</th>
                                        <th>Nombre</th>
                                        <th>Correo</th>
                                        <th>Estado</th>
                                        <th>Creado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($administrators as $administrator): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($administrator['username'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                        <td><?= htmlspecialchars($administrator['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($administrator['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td>
                                            <?php if ($administrator['active']): ?>
                                                <span class="dash-badge dash-badge-active"><i class="bi bi-check-circle"></i> Activo</span>
                                            <?php else: ?>
                                                <span class="dash-badge dash-badge-inactive"><i class="bi bi-x-circle"></i> Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($administrator['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (count($administrators) === 0): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No hay administradores registrados en el sistema.</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="dash-footer mt-auto">
        <div class="container">
            <div class="dash-footer-content">
                <span class="dash-footer-brand"><i class="bi bi-fingerprint"></i> SACBAE</span>
                <span>I.E.T. María Inmaculada</span>
                <span>&copy; <?= date("Y") ?></span>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
