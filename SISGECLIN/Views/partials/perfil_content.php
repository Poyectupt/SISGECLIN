<?php
$usuario   = $usuario ?? null;
$rol       = $rol ?? 'recepcionista';
$isAdmin   = $rol === 'admin';
?>

<div class="head-title">
    <div class="left">
        <h1>Mi Perfil</h1>
        <ul class="breadcrumb">
            <li><a href="index.php?module=dashboard">Inicio</a></li>
            <li><i class='bx bx-chevron-right'></i></li>
            <li><a class="active" href="#">Perfil</a></li>
        </ul>
    </div>
</div>

<div class="profile-grid">
    <!-- Información Personal -->
    <div class="profile-card">
        <div class="profile-card-header">
            <h3><i class='bx bx-user-detail'></i> Información Personal</h3>
        </div>
        <div class="profile-card-body">
            <?php if ($usuario): ?>
            <div class="profile-avatar-section">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($usuario->nombre_completo); ?>&background=0d6efd&color=fff&size=120" alt="Avatar" class="profile-avatar">
                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($usuario->nombre_completo); ?></h2>
                    <span class="badge badge-<?php echo $usuario->rol === 'admin' ? 'danger' : ($usuario->rol === 'medico' ? 'primary' : 'secondary'); ?>">
                        <?php echo ucfirst($usuario->rol); ?>
                    </span>
                </div>
            </div>
            <?php endif; ?>
            
            <form id="form-perfil" class="profile-form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="form-group">
                    <label><i class='bx bx-user'></i> Nombre Completo</label>
                    <input type="text" name="nombre_completo" id="perfil-nombre_completo" value="<?php echo htmlspecialchars($usuario->nombre_completo ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label><i class='bx bx-envelope'></i> Email</label>
                    <input type="email" name="email" id="perfil-email" value="<?php echo htmlspecialchars($usuario->email ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label><i class='bx bx-user'></i> Usuario</label>
                    <input type="text" value="<?php echo htmlspecialchars($usuario->username ?? ''); ?>" disabled class="input-disabled">
                    <small class="text-muted">El nombre de usuario no se puede cambiar</small>
                </div>
                
                <?php if ($isAdmin): ?>
                <div class="form-group">
                    <label><i class='bx bx-shield'></i> Rol</label>
                    <select name="rol" id="perfil-rol">
                        <option value="recepcionista" <?php echo ($usuario->rol ?? '') === 'recepcionista' ? 'selected' : ''; ?>>Recepcionista</option>
                        <option value="medico" <?php echo ($usuario->rol ?? '') === 'medico' ? 'selected' : ''; ?>>Médico</option>
                        <option value="admin" <?php echo ($usuario->rol ?? '') === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                    </select>
                </div>
                <?php else: ?>
                <div class="form-group">
                    <label><i class='bx bx-shield'></i> Rol</label>
                    <input type="text" value="<?php echo ucfirst($usuario->rol ?? ''); ?>" disabled class="input-disabled">
                </div>
                <?php endif; ?>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <i class='bx bx-save'></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Cambiar Contraseña -->
    <div class="profile-card">
        <div class="profile-card-header">
            <h3><i class='bx bx-lock-alt'></i> Cambiar Contraseña</h3>
        </div>
        <div class="profile-card-body">
            <form id="form-password" class="profile-form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="form-group">
                    <label><i class='bx bx-lock-open'></i> Contraseña Actual</label>
                    <input type="password" name="password_actual" id="password_actual" required placeholder="••••••••">
                </div>
                
                <div class="form-group">
                    <label><i class='bx bx-lock'></i> Nueva Contraseña</label>
                    <input type="password" name="password_nuevo" id="password_nuevo" required placeholder="Mínimo 6 caracteres">
                </div>
                
                <div class="form-group">
                    <label><i class='bx bx-lock'></i> Confirmar Contraseña</label>
                    <input type="password" name="password_confirmar" id="password_confirmar" required placeholder="Repita la nueva contraseña">
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <i class='bx bx-key'></i> Cambiar Contraseña
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Información de Cuenta -->
    <div class="profile-card">
        <div class="profile-card-header">
            <h3><i class='bx bx-info-circle'></i> Información de Cuenta</h3>
        </div>
        <div class="profile-card-body">
            <div class="info-list">
                <div class="info-item">
                    <span class="info-label"><i class='bx bx-calendar'></i> Fecha de Registro</span>
                    <span class="info-value"><?php echo $usuario->created_at ? date('d/m/Y', strtotime($usuario->created_at)) : '-'; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class='bx bx-time'></i> Último Acceso</span>
                    <span class="info-value"><?php echo $usuario->ultimo_acceso ? date('d/m/Y H:i', strtotime($usuario->ultimo_acceso)) : 'Nunca'; ?></span>
                </div>
                <?php if ($isAdmin): ?>
                <div class="info-item">
                    <span class="info-label"><i class='bx bx-check-circle'></i> Estado</span>
                    <select name="activo" id="perfil-activo" class="form-control-sm">
                        <option value="1" <?php echo $usuario->activo ? 'selected' : ''; ?>>Activo</option>
                        <option value="0" <?php echo !$usuario->activo ? 'selected' : ''; ?>>Inactivo</option>
                    </select>
                </div>
                <?php else: ?>
                <div class="info-item">
                    <span class="info-label"><i class='bx bx-check-circle'></i> Estado</span>
                    <span class="status status-<?php echo $usuario->activo ? 'activo' : 'inactivo'; ?>">
                        <?php echo $usuario->activo ? 'Activo' : 'Inactivo'; ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.profile-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 24px;
}

.profile-card {
    background: var(--bg-card);
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow);
    overflow: hidden;
}

.profile-card-header {
    padding: 16px 24px;
    background: var(--bg-body);
    border-bottom: 1px solid var(--border-color);
}

.profile-card-header h3 {
    font-size: 16px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.profile-card-body {
    padding: 24px;
}

.profile-avatar-section {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 24px;
    padding-bottom: 24px;
    border-bottom: 1px solid var(--border-color);
}

.profile-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    border: 3px solid var(--primary);
}

.profile-info h2 {
    font-size: 20px;
    margin-bottom: 8px;
}

.profile-form {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.input-disabled {
    background: var(--bg-body) !important;
    cursor: not-allowed;
    opacity: 0.7;
}

.form-actions {
    margin-top: 8px;
}

.info-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid var(--border-color);
}

.info-item:last-child {
    border-bottom: none;
}

.info-label {
    color: var(--text-secondary);
    display: flex;
    align-items: center;
    gap: 8px;
}

.info-value {
    font-weight: 500;
}

.btn-warning {
    background: var(--warning);
    color: #212529;
}

.btn-warning:hover {
    background: #e0a800;
}

.btn-success {
    background: var(--success);
    color: #fff;
}

.btn-success:hover {
    background: #157347;
}
</style>
