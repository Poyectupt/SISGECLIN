<?php
$usuarios = $usuarios ?? [];
?>

<div class="head-title">
    <div class="left">
        <h1>Gestión de Usuarios</h1>
        <ul class="breadcrumb">
            <li><a href="index.php?module=dashboard">Inicio</a></li>
            <li><i class='bx bx-chevron-right'></i></li>
            <li><a class="active" href="#">Usuarios</a></li>
        </ul>
    </div>
    <div class="head-actions">
        <button class="btn-primary" onclick="openModal('modal-usuario')">
            <i class='bx bx-plus'></i> Nuevo Usuario
        </button>
    </div>
</div>

<div class="table-container">
    <div class="table-header">
        <h3><i class='bx bxs-user-account'></i> Listado de Usuarios</h3>
        <div class="table-stats">
            <?php $activos = 0; foreach($usuarios as $u) { if($u->activo) $activos++; } ?>
            <span class="badge badge-info"><?php echo count($usuarios); ?> usuarios</span>
            <span class="badge badge-success"><?php echo $activos; ?> activos</span>
        </div>
    </div>
    <div class="table-responsive">
        <table class="data-table" id="usuarios-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Usuario</th>
                    <th>Nombre Completo</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Último Acceso</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($usuarios)): ?>
                    <?php $i = 1; foreach ($usuarios as $u): ?>
                        <tr data-id="<?php echo $u->id; ?>" class="<?php echo !$u->activo ? 'row-muted' : ''; ?>">
                            <td><span class="cell-num"><?php echo $i++; ?></span></td>
                            <td>
                                <div class="cell-user">
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($u->nombre_completo); ?>&background=0d6efd&color=fff" alt="">
                                    <span><?php echo htmlspecialchars($u->username); ?></span>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($u->nombre_completo); ?></td>
                            <td><?php echo htmlspecialchars($u->email); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $u->rol === 'admin' ? 'danger' : ($u->rol === 'medico' ? 'primary' : 'secondary'); ?>">
                                    <?php echo ucfirst($u->rol); ?>
                                </span>
                            </td>
                            <td>
                                <span class="status status-<?php echo $u->activo ? 'activo' : 'inactivo'; ?>">
                                    <?php echo $u->activo ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </td>
                            <td><?php echo $u->ultimo_acceso ? date('d/m/Y H:i', strtotime($u->ultimo_acceso)) : 'Nunca'; ?></td>
                            <td class="actions">
                                <button class="btn-icon btn-edit" onclick="editUsuario(<?php echo $u->id; ?>)" title="Editar">
                                    <i class='bx bx-edit'></i>
                                </button>
                                <?php if ($u->activo): ?>
                                    <button class="btn-icon btn-warning" onclick="toggleUsuarioEstado(<?php echo $u->id; ?>, 'desactivar')" title="Desactivar">
                                        <i class='bx bx-user-x'></i>
                                    </button>
                                <?php else: ?>
                                    <button class="btn-icon btn-success" onclick="toggleUsuarioEstado(<?php echo $u->id; ?>, 'activar')" title="Activar">
                                        <i class='bx bx-user-check'></i>
                                    </button>
                                <?php endif; ?>
                                <button class="btn-icon btn-delete" onclick="confirmDeleteUsuario(<?php echo $u->id; ?>, '<?php echo htmlspecialchars($u->username); ?>')" title="Eliminar">
                                    <i class='bx bx-trash'></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="empty-row">
                        <div class="empty-state">
                            <i class='bx bx-user-x'></i>
                            <p>No hay usuarios registrados</p>
                        </div>
                    </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Paginación -->
    <?php if (isset($pagination)): ?>
        <?php include __DIR__ . '/pagination.php'; ?>
    <?php endif; ?>
</div>

<!-- MODAL USUARIO -->
<div class="modal" id="modal-usuario">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modal-usuario-title"><i class='bx bx-user-plus'></i> Nuevo Usuario</h2>
            <button class="modal-close" onclick="closeModal('modal-usuario')">&times;</button>
        </div>
        <form id="form-usuario" class="modal-form">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="id" id="usuario-id">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="usuario-username"><i class='bx bx-user'></i> Usuario *</label>
                    <input type="text" name="username" id="usuario-username" placeholder="Nombre de usuario" required>
                </div>
                <div class="form-group">
                    <label for="usuario-email"><i class='bx bx-envelope'></i> Email *</label>
                    <input type="email" name="email" id="usuario-email" placeholder="correo@ejemplo.com" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="usuario-nombre_completo"><i class='bx bx-user-detail'></i> Nombre Completo *</label>
                <input type="text" name="nombre_completo" id="usuario-nombre_completo" placeholder="Nombre y apellido" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="usuario-password"><i class='bx bx-lock'></i> Contraseña <?php echo isset($editMode) && $editMode ? '(dejar vacío para no cambiar)' : '*'; ?></label>
                    <input type="password" name="password" id="usuario-password" placeholder="••••••••" <?php echo isset($editMode) && $editMode ? '' : 'required'; ?>>
                </div>
                <div class="form-group">
                    <label for="usuario-rol"><i class='bx bx-shield'></i> Rol *</label>
                    <select name="rol" id="usuario-rol" required>
                        <option value="recepcionista">Recepcionista</option>
                        <option value="medico">Médico</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="usuario-activo"><i class='bx bx-check-circle'></i> Estado</label>
                <select name="activo" id="usuario-activo">
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('modal-usuario')">
                    <i class='bx bx-x'></i> Cancelar
                </button>
                <button type="submit" class="btn-primary">
                    <i class='bx bx-save'></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Los modales de confirmación están en dashboard.php como globales -->
