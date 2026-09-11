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
<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Administradores | SACBAE</title>
<style>body{font-family:Arial,sans-serif;max-width:1000px;margin:32px auto;padding:0 18px;color:#17212b}form,table{width:100%;margin-top:18px}form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}label{display:grid;gap:5px;font-weight:700}input{padding:9px;border:1px solid #cbd5e1;border-radius:6px}button{width:max-content;padding:10px 16px;background:#0369a1;color:#fff;border:0;border-radius:6px;font-weight:700;cursor:pointer}.full{grid-column:1/-1}.error{color:#b91c1c}.success{color:#15803d}table{border-collapse:collapse}th,td{border-bottom:1px solid #dbe3ea;padding:9px;text-align:left}@media(max-width:600px){form{grid-template-columns:1fr}}</style></head>
<body><h1>Administradores</h1><p><a href="<?= htmlspecialchars(sacbaeUrl('modules/hikvision/web/dashboard.php'), ENT_QUOTES, 'UTF-8') ?>">Volver al panel</a></p>
<?php if ($error !== ''): ?><p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?><?php if ($success !== ''): ?><p class="success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
<form method="post"><label>Usuario<input name="username" maxlength="64" required></label><label>Nombre completo<input name="full_name" maxlength="160" required></label><label>Correo institucional<input name="email" type="email" maxlength="190" required></label><label>Contraseña temporal<input name="password" type="password" minlength="8" required></label><div class="full"><button>Crear administrador</button></div></form>
<table><thead><tr><th>Usuario</th><th>Nombre</th><th>Correo</th><th>Estado</th><th>Creado</th></tr></thead><tbody><?php foreach ($administrators as $administrator): ?><tr><td><?= htmlspecialchars($administrator['username'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($administrator['full_name'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($administrator['email'], ENT_QUOTES, 'UTF-8') ?></td><td><?= $administrator['active'] ? 'Activo' : 'Inactivo' ?></td><td><?= htmlspecialchars($administrator['created_at'], ENT_QUOTES, 'UTF-8') ?></td></tr><?php endforeach; ?></tbody></table>
</body></html>
