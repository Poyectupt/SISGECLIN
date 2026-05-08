<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$username        = $username        ?? $_SESSION['username']        ?? 'Usuario';
$nombre_completo = $nombre_completo ?? $_SESSION['nombre_completo'] ?? $username;
$rol             = $rol             ?? $_SESSION['rol']             ?? 'recepcionista';
$module          = $module          ?? 'dashboard';

// Asegurar que el token CSRF existe
if (empty($csrf_token)) {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    $csrf_token = $_SESSION['csrf_token'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISGECLIN - Sistema de Gestión Clínica</title>
    <link rel="shortcut icon" href="Img/image-Photoroom.png" type="image/x-icon">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="CSS/styleS.css">
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="CSS/pagination.css">
    <link rel="stylesheet" href="CSS/dark-mode-improvements.css">
</head>
<body>
    <!-- SIDEBAR -->
    <section id="sidebar">
        <a href="index.php" class="brand">
            <i class='bx bxs-clinic'></i>
            <span>SISGECLIN</span>
        </a>
        <ul class="side-menu">
            <li class="<?php echo $module === 'dashboard' ? 'active' : ''; ?>">
                <a href="index.php?module=dashboard">
                    <i class='bx bxs-dashboard'></i>
                    <span>Panel de Control</span>
                </a>
            </li>
            <li class="<?php echo $module === 'pacientes' ? 'active' : ''; ?>">
                <a href="index.php?module=pacientes">
                    <i class='bx bxs-user-detail'></i>
                    <span>Pacientes</span>
                </a>
            </li>
            <li class="<?php echo $module === 'consultas' ? 'active' : ''; ?>">
                <a href="index.php?module=consultas">
                    <i class='bx bx-clipboard'></i>
                    <span>Consultas</span>
                </a>
            </li>
            <li class="<?php echo $module === 'medicina' ? 'active' : ''; ?>">
                <a href="index.php?module=medicina">
                    <i class='bx bxs-capsule'></i>
                    <span>Medicamentos</span>
                </a>
            </li>
            <li class="<?php echo $module === 'inventario_movimientos' ? 'active' : ''; ?>">
                <a href="index.php?module=inventario_movimientos">
                    <i class='bx bx-transfer'></i>
                    <span>Movimientos</span>
                </a>
            </li>
            <li class="<?php echo $module === 'mensajes' ? 'active' : ''; ?>">
                <a href="index.php?module=mensajes">
                    <i class='bx bxs-envelope'></i>
                    <span>Mensajes</span>
                </a>
            </li>
            <?php if ($rol === 'admin'): ?>
            <li class="<?php echo $module === 'nomina' ? 'active' : ''; ?>">
                <a href="index.php?module=nomina">
                    <i class='bx bxs-group'></i>
                    <span>Nómina</span>
                </a>
            </li>
            <li class="<?php echo $module === 'usuarios' ? 'active' : ''; ?>">
                <a href="index.php?module=usuarios">
                    <i class='bx bxs-user-account'></i>
                    <span>Usuarios</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>
        <ul class="side-menu bottom">
            <li class="<?php echo $module === 'perfil' ? 'active' : ''; ?>">
                <a href="index.php?module=perfil">
                    <i class='bx bxs-user-circle'></i>
                    <span>Mi Perfil</span>
                </a>
            </li>
            <li>
                <a href="index.php?logout=1" class="logout">
                    <i class='bx bx-power-off'></i>
                    <span>Cerrar Sesión</span>
                </a>
            </li>
        </ul>
    </section>

    <!-- CONTENT -->
    <section id="content">
        <!-- NAVBAR -->
        <nav>
            <i class='bx bx-menu'></i>
            <span class="nav-title"><?php echo ucfirst($module); ?></span>
            
            <form action="index.php" method="get" class="search-form">
                <input type="hidden" name="module" value="<?php echo htmlspecialchars($module); ?>">
                <div class="form-input">
                    <input type="search" name="search" placeholder="Buscar..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
                    <button type="submit"><i class='bx bx-search'></i></button>
                </div>
            </form>
            
            <div class="nav-actions">
                <div class="switch-mode" title="Cambiar tema">
                    <i class='bx bxs-moon'></i>
                    <i class='bx bx-sun'></i>
                    <div class="ball"></div>
                </div>
                
                <a href="index.php?module=mensajes" class="notification" title="Mensajes">
                    <i class='bx bxs-bell'></i>
                    <span class="num"><?php echo $stats['mensajes'] ?? 0; ?></span>
                </a>
                
                <div class="profile-wrapper">
                    <a href="index.php?module=perfil" class="profile" title="Mi perfil">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($nombre_completo); ?>&background=0d6efd&color=fff" alt="Perfil">
                    </a>
                    
                    <div class="profile-menu">
                        <ul>
                            <li><strong><?php echo htmlspecialchars($nombre_completo); ?></strong></li>
                            <li><small><?php echo htmlspecialchars(ucfirst($rol)); ?></small></li>
                            <li><a href="index.php?module=perfil"><i class='bx bx-user'></i> Mi Perfil</a></li>
                            <li><a href="index.php?logout=1" class="logout-link"><i class='bx bx-log-out'></i> Cerrar Sesión</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <!-- MAIN -->
        <main>
            <?php if ($module === 'dashboard'): ?>
                <?php include_once __DIR__ . '/partials/dashboard_content.php'; ?>
            <?php elseif ($module === 'pacientes'): ?>
                <?php include_once __DIR__ . '/partials/pacientes_content.php'; ?>
            <?php elseif ($module === 'consultas'): ?>
                <?php include_once __DIR__ . '/partials/consultas_content.php'; ?>
            <?php elseif ($module === 'medicina'): ?>
                <?php include_once __DIR__ . '/partials/medicina_content.php'; ?>
            <?php elseif ($module === 'inventario_movimientos'): ?>
                <?php include_once __DIR__ . '/partials/inventario_movimientos_content.php'; ?>
            <?php elseif ($module === 'mensajes'): ?>
                <?php include_once __DIR__ . '/partials/mensajes_content.php'; ?>
            <?php elseif ($module === 'nomina'): ?>
                <?php include_once __DIR__ . '/partials/nomina_content.php'; ?>
            <?php elseif ($module === 'usuarios'): ?>
                <?php include_once __DIR__ . '/partials/usuarios_content.php'; ?>
            <?php elseif ($module === 'perfil'): ?>
                <?php include_once __DIR__ . '/partials/perfil_content.php'; ?>
            <?php else: ?>
                <div class="head-title"><h1>Módulo no encontrado</h1></div>
            <?php endif; ?>
        </main>
    </section>

    <!-- TOAST CONTAINER -->
    <div id="toast-container" class="toast-container"></div>

    <!-- MODAL CONFIRMAR ELIMINACIÓN (Global) -->
    <div class="modal" id="modal-confirm-delete">
        <div class="modal-content modal-sm">
            <div class="modal-header modal-header-danger">
                <h2><i class='bx bx-error-circle'></i> Confirmar Eliminación</h2>
                <button class="modal-close" onclick="closeModal('modal-confirm-delete')">&times;</button>
            </div>
            <div class="modal-body text-center">
                <p>¿Está seguro de eliminar este registro?</p>
                <p class="text-muted" id="delete-item-name"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('modal-confirm-delete')">Cancelar</button>
                <button type="button" class="btn-danger" id="btn-confirm-delete">
                    <i class='bx bx-trash'></i> Eliminar
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL CONFIRMAR CAMBIO DE ESTADO (Global) -->
    <div class="modal" id="modal-confirm-estado">
        <div class="modal-content modal-sm">
            <div class="modal-header modal-header-warning">
                <h2><i class='bx bx-info-circle'></i> Cambiar Estado</h2>
                <button class="modal-close" onclick="closeModal('modal-confirm-estado')">&times;</button>
            </div>
            <div class="modal-body text-center">
                <p id="estado-message">¿Desea cambiar el estado de este usuario?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('modal-confirm-estado')">Cancelar</button>
                <button type="button" class="btn-warning" id="btn-confirm-estado">
                    <i class='bx bx-check'></i> Confirmar
                </button>
            </div>
        </div>
    </div>

    <script src="CSS/script.js"></script>
    <script>
        // Variable global con el token CSRF
        window.CSRF_TOKEN = '<?php echo $csrf_token; ?>';
        window.MODULE = '<?php echo $module; ?>';
        
        // Inicializar variables globales
        var CSRF_TOKEN = window.CSRF_TOKEN;
        var MODULE = window.MODULE;
        
        console.log('Token CSRF cargado:', CSRF_TOKEN ? 'OK (' + CSRF_TOKEN.substring(0, 8) + '...)' : 'ERROR - Token vacío');
    </script>
</body>
</html>
