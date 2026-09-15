<?php

declare(strict_types=1);

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'auth.php';
require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'database.php';
sacbaeRequireAuthentication();
sacbaeRequireAdministrator();

$database = sacbaeDatabase();
$error = '';
$success = '';

if ($database === null) {
    $error = 'No fue posible conectar con la base de datos sacbae.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $personId = trim((string) ($_POST['person_id'] ?? ''));
    $dni = trim((string) ($_POST['dni'] ?? ''));
    $fullName = trim((string) ($_POST['full_name'] ?? ''));
    $email = trim((string) ($_POST['institutional_email'] ?? ''));

    if ($personId === '' || $dni === '' || $fullName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Completa todos los campos con datos válidos.';
    } else {
        try {
            $statement = $database->prepare(
                'INSERT INTO students (person_id, dni, full_name, institutional_email)
                 VALUES (:person_id, :dni, :full_name, :institutional_email)'
            );
            $statement->execute([
                'person_id' => $personId,
                'dni' => $dni,
                'full_name' => $fullName,
                'institutional_email' => $email,
            ]);
            $success = 'Estudiante registrado correctamente.';
        } catch (PDOException) {
            $error = 'El person ID, DNI o correo ya está registrado.';
        }
    }
}

$students = $database === null ? [] : $database->query(
    'SELECT person_id, dni, full_name, institutional_email FROM students WHERE active = 1 ORDER BY full_name'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Estudiantes | SACBAE</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom CSS -->
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
                <li><a href="<?= htmlspecialchars(sacbaeUrl('modules/hikvision/web/dashboard.php'), ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-grid"></i> Panel</a></li>
                <li><a href="<?= htmlspecialchars(sacbaeUrl('sacbae/students.php'), ENT_QUOTES, 'UTF-8') ?>" class="active"><i class="bi bi-people"></i> Estudiantes</a></li>
                <li><a href="<?= htmlspecialchars(sacbaeUrl('sacbae/devices.php'), ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-cpu"></i> Dispositivos</a></li>
                <li><a href="<?= htmlspecialchars(sacbaeUrl('sacbae/admins.php'), ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-person-badge"></i> Administradores</a></li>
                <li><a href="<?= htmlspecialchars(sacbaeUrl('sacbae/logout.php'), ENT_QUOTES, 'UTF-8') ?>" class="text-danger"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main -->
    <main class="dash-main">
        <div class="container">
            <!-- Page header -->
            <div class="dash-page-header">
                <span class="dash-page-label"><span class="dot"></span> GESTIÓN ACADÉMICA</span>
                <h1>Gestión de <span>Estudiantes</span></h1>
                <p>Registra y administra la información de los estudiantes del sistema biométrico.</p>
            </div>

            <!-- Alerts -->
            <?php if ($error !== ''): ?>
                <div class="dash-alert-error">
                    <i class="bi bi-exclamation-circle"></i>
                    <div><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            <?php endif; ?>
            
            <?php if ($success !== ''): ?>
                <div class="dash-alert-success">
                    <i class="bi bi-check-circle"></i>
                    <div><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            <?php endif; ?>

            <!-- Form Card -->
            <div class="dash-card mb-4">
                <div class="dash-card-header">
                    <h2 class="dash-card-title"><span class="dash-card-icon"><i class="bi bi-person-plus"></i></span> Registrar estudiante</h2>
                </div>
                <div class="dash-card-body">
                    <form method="post">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="dash-form-group">
                                    <label class="form-label">Person ID del biométrico</label>
                                    <div class="dash-input-wrapper">
                                        <i class="bi bi-person-badge dash-input-icon"></i>
                                        <input type="text" name="person_id" class="dash-input" maxlength="64" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="dash-form-group">
                                    <label class="form-label">DNI</label>
                                    <div class="dash-input-wrapper">
                                        <i class="bi bi-card-text dash-input-icon"></i>
                                        <input type="text" name="dni" class="dash-input" maxlength="32" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="dash-form-group">
                                    <label class="form-label">Nombre completo</label>
                                    <div class="dash-input-wrapper">
                                        <i class="bi bi-person dash-input-icon"></i>
                                        <input type="text" name="full_name" class="dash-input" maxlength="160" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="dash-form-group">
                                    <label class="form-label">Correo institucional</label>
                                    <div class="dash-input-wrapper">
                                        <i class="bi bi-envelope dash-input-icon"></i>
                                        <input type="email" name="institutional_email" class="dash-input" maxlength="190" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" class="dash-btn dash-btn-primary w-100">
                                    <i class="bi bi-person-plus"></i> Registrar estudiante
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table Card -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <h2 class="dash-card-title"><span class="dash-card-icon"><i class="bi bi-people"></i></span> Estudiantes registrados</h2>
                </div>
                <div class="dash-card-body p-0">
                    <div class="dash-table-wrapper">
                        <table class="dash-table">
                            <thead>
                                <tr>
                                    <th>Person ID</th>
                                    <th>DNI</th>
                                    <th>Nombre</th>
                                    <th>Correo institucional</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?= htmlspecialchars($student['person_id'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($student['dni'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($student['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($student['institutional_email'], ENT_QUOTES, 'UTF-8') ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($students)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No hay estudiantes registrados.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="dash-footer">
        <div class="container">
            <div class="dash-footer-content">
                <span class="dash-footer-brand"><i class="bi bi-fingerprint"></i> SACBAE</span>
                <span>I.E.T. María Inmaculada</span>
                <span>&copy; <?php echo date("Y"); ?></span>
            </div>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
