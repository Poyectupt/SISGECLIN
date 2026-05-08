<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISGECLIN - Iniciar Sesión</title>
    <link rel="shortcut icon" href="Img/image-Photoroom.png" type="image/x-icon">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="CSS/login.css">
</head>
<body>
    <section class="contenedor">
        <form method="post" autocomplete="off">
            <img src="Img/image-Photoroom.png" alt="Logo SISGECLIN">
            
            <h1>SISGECLIN</h1>
            <span class="subtitle">Sistema de Gestión Clínica</span>
            
            <?php if (isset($error) && $error): ?>
                <div class="alert alert-error">
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">
            
            <div class="cont-input">
                <input type="text" name="username" id="username" placeholder=" " required autocomplete="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                <label for="username"><i class='bx bx-user'></i> Usuario</label>
            </div>
            
            <div class="cont-input">
                <input type="password" name="password" id="password" placeholder=" " required autocomplete="current-password">
                <label for="password"><i class='bx bx-lock-alt'></i> Contraseña</label>
            </div>
            
            <div class="boton">
                <button type="submit" class="button">
                    <i class='bx bx-log-in'></i> Iniciar Sesión
                </button>
            </div>
            
            <div class="footer-form">
                <p>SISGECLIN</p>
                <small>Sistema de Gestión Clínica v1.0.0</small>
            </div>
        </form>
    </section>
</body>
</html>
