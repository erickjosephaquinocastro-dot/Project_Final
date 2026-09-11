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
<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Estudiantes | SACBAE</title>
<style>body{font-family:Arial,sans-serif;max-width:1000px;margin:32px auto;padding:0 18px;color:#17212b}form,table{width:100%;margin-top:18px}form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}label{display:grid;gap:5px;font-weight:700}input{padding:9px;border:1px solid #cbd5e1;border-radius:6px}button{width:max-content;padding:10px 16px;background:#0369a1;color:#fff;border:0;border-radius:6px;font-weight:700;cursor:pointer}.full{grid-column:1/-1}.error{color:#b91c1c}.success{color:#15803d}table{border-collapse:collapse}th,td{border-bottom:1px solid #dbe3ea;padding:9px;text-align:left}@media(max-width:600px){form{grid-template-columns:1fr}}</style></head>
<body><h1>Estudiantes</h1><p><a href="<?= htmlspecialchars(sacbaeUrl('modules/hikvision/web/dashboard.php'), ENT_QUOTES, 'UTF-8') ?>">Volver al panel</a></p>
<?php if ($error !== ''): ?><p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?><?php if ($success !== ''): ?><p class="success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
<form method="post"><label>Person ID del biométrico<input name="person_id" maxlength="64" required></label><label>DNI<input name="dni" maxlength="32" required></label><label>Nombre completo<input name="full_name" maxlength="160" required></label><label>Correo institucional<input name="institutional_email" type="email" maxlength="190" required></label><div class="full"><button>Registrar estudiante</button></div></form>
<table><thead><tr><th>Person ID</th><th>DNI</th><th>Nombre</th><th>Correo institucional</th></tr></thead><tbody><?php foreach ($students as $student): ?><tr><td><?= htmlspecialchars($student['person_id'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($student['dni'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($student['full_name'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($student['institutional_email'], ENT_QUOTES, 'UTF-8') ?></td></tr><?php endforeach; ?></tbody></table>
</body></html>
