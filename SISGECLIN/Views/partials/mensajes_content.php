<?php
$mensajesRecibidos = $mensajesRecibidos ?? [];
$mensajesEnviados  = $mensajesEnviados  ?? [];
$usuarios          = $usuarios          ?? [];
$noLeidos          = 0;
foreach ($mensajesRecibidos as $m) {
    if (!$m->leido) $noLeidos++;
}
?>

<div class="head-title">
    <div class="left">
        <h1>Mensajes</h1>
        <ul class="breadcrumb">
            <li><a href="index.php?module=dashboard">Inicio</a></li>
            <li><i class='bx bx-chevron-right'></i></li>
            <li><a class="active" href="#">Mensajes</a></li>
        </ul>
    </div>
    <div class="head-actions">
        <button class="btn-primary" onclick="openModal('modal-mensaje')">
            <i class='bx bx-plus'></i> Nuevo Mensaje
        </button>
        <?php if (($rol ?? '') === 'admin'): ?>
        <button class="btn-masivo-trigger" onclick="openModal('modal-mensaje-masivo')" title="Enviar mensaje a todos los usuarios">
            <i class='bx bx-broadcast'></i> Mensaje General
        </button>
        <?php endif; ?>
    </div>
</div>

<div class="messages-grid">
    <!-- BANDEJA DE ENTRADA -->
    <div class="messages-box">
        <div class="messages-box-header">
            <h3><i class='bx bx-inbox'></i> Recibidos</h3>
            <?php if ($noLeidos > 0): ?>
                <span class="badge badge-danger"><?php echo $noLeidos; ?> nuevos</span>
            <?php endif; ?>
        </div>
        <div class="messages-list">
            <?php if (!empty($mensajesRecibidos)): ?>
                <?php foreach ($mensajesRecibidos as $m): ?>
                    <div class="message-card <?php echo $m->leido ? '' : 'unread'; ?>" onclick="viewMensaje(<?php echo $m->id; ?>)">
                        <div class="message-avatar">
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($m->remitente_nombre); ?>&background=3C91E6&color=fff" alt="">
                        </div>
                        <div class="message-info">
                            <div class="message-top">
                                <strong class="message-from"><?php echo htmlspecialchars($m->remitente_nombre); ?></strong>
                                <small class="message-time"><?php echo timeAgo($m->created_at); ?></small>
                            </div>
                            <div class="message-subject"><?php echo htmlspecialchars($m->asunto); ?></div>
                            <div class="message-preview"><?php echo htmlspecialchars(substr($m->contenido, 0, 80)); ?>...</div>
                        </div>
                        <?php if (!$m->leido): ?>
                            <span class="unread-indicator"></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-messages">
                    <i class='bx bx-inbox'></i>
                    <p>No hay mensajes recibidos</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ENVIADOS -->
    <div class="messages-box">
        <div class="messages-box-header">
            <h3><i class='bx bx-send'></i> Enviados</h3>
            <span class="badge badge-info"><?php echo count($mensajesEnviados); ?></span>
        </div>
        <div class="messages-list">
            <?php if (!empty($mensajesEnviados)): ?>
                <?php foreach ($mensajesEnviados as $m): ?>
                    <div class="message-card" onclick="viewMensajeEnviado(<?php echo $m->id; ?>)">
                        <div class="message-avatar">
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($m->destinatario_nombre); ?>&background=01cfad&color=fff" alt="">
                        </div>
                        <div class="message-info">
                            <div class="message-top">
                                <strong class="message-to">Para: <?php echo htmlspecialchars($m->destinatario_nombre); ?></strong>
                                <small class="message-time"><?php echo timeAgo($m->created_at); ?></small>
                            </div>
                            <div class="message-subject"><?php echo htmlspecialchars($m->asunto); ?></div>
                            <div class="message-preview"><?php echo htmlspecialchars(substr($m->contenido, 0, 80)); ?>...</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-messages">
                    <i class='bx bx-send'></i>
                    <p>No hay mensajes enviados</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- MODAL NUEVO MENSAJE -->
<div class="modal" id="modal-mensaje">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class='bx bx-envelope'></i> Nuevo Mensaje</h2>
            <button class="modal-close" onclick="closeModal('modal-mensaje')">&times;</button>
        </div>
        <form id="form-mensaje" class="modal-form">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="form-group">
                <label for="mensaje-destinatario_id"><i class='bx bx-user'></i> Destinatario *</label>
                <select name="destinatario_id" id="mensaje-destinatario_id" required>
                    <option value="">Seleccionar destinatario</option>
                    <?php foreach ($usuarios as $u): ?>
                        <option value="<?php echo $u->id; ?>">
                            <?php echo htmlspecialchars($u->nombre_completo . ' (' . ucfirst($u->rol) . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="mensaje-asunto"><i class='bx bx-heading'></i> Asunto *</label>
                <input type="text" name="asunto" id="mensaje-asunto" placeholder="Asunto del mensaje" required>
            </div>
            
            <div class="form-group">
                <label for="mensaje-contenido"><i class='bx bx-message-detail'></i> Mensaje *</label>
                <textarea name="contenido" id="mensaje-contenido" rows="5" placeholder="Escriba su mensaje aquí..." required></textarea>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('modal-mensaje')">
                    <i class='bx bx-x'></i> Cancelar
                </button>
                <button type="submit" class="btn-primary">
                    <i class='bx bx-send'></i> Enviar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL VER MENSAJE -->
<div class="modal" id="modal-ver-mensaje">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="ver-mensaje-asunto">Asunto</h2>
            <button class="modal-close" onclick="closeModal('modal-ver-mensaje')">&times;</button>
        </div>
        <div class="modal-body">
            <div class="message-detail-header">
                <img id="ver-mensaje-avatar" src="" alt="" class="message-detail-avatar">
                <div class="message-detail-meta">
                    <p><strong>De:</strong> <span id="ver-mensaje-remitente"></span></p>
                    <p><strong>Fecha:</strong> <span id="ver-mensaje-fecha"></span></p>
                </div>
            </div>
            <div class="message-detail-body" id="ver-mensaje-contenido"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeModal('modal-ver-mensaje')">Cerrar</button>
        </div>
    </div>
</div>

<!-- MODAL ENVÍO MASIVO -->
<div class="modal" id="modal-mensaje-masivo">
    <div class="modal-content masivo-modal">

        <!-- Header -->
        <div class="masivo-header">
            <div class="masivo-header-icon">
                <i class='bx bx-broadcast'></i>
            </div>
            <div class="masivo-header-text">
                <h2>Mensaje General</h2>
                <p>Envía un comunicado a un grupo de usuarios</p>
            </div>
            <button class="masivo-close" onclick="closeModal('modal-mensaje-masivo')">&times;</button>
        </div>

        <form id="form-mensaje-masivo">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

            <div class="masivo-body">

                <!-- Paso 1 -->
                <div class="masivo-section">
                    <div class="masivo-section-label">
                        <span class="masivo-step">1</span>
                        <span>¿A quién va dirigido?</span>
                    </div>
                    <div class="masivo-group-pills">
                        <label class="masivo-pill">
                            <input type="radio" name="destinatarios" value="todos" checked>
                            <span>
                                <i class='bx bx-globe'></i>
                                <strong>Todos</strong>
                            </span>
                        </label>
                        <label class="masivo-pill">
                            <input type="radio" name="destinatarios" value="medicos">
                            <span>
                                <i class='bx bx-plus-medical'></i>
                                <strong>Médicos</strong>
                            </span>
                        </label>
                        <label class="masivo-pill">
                            <input type="radio" name="destinatarios" value="recepcionistas">
                            <span>
                                <i class='bx bx-phone-call'></i>
                                <strong>Recepción</strong>
                            </span>
                        </label>
                        <label class="masivo-pill">
                            <input type="radio" name="destinatarios" value="admins">
                            <span>
                                <i class='bx bx-shield-alt-2'></i>
                                <strong>Admins</strong>
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Paso 2 -->
                <div class="masivo-section">
                    <div class="masivo-section-label">
                        <span class="masivo-step">2</span>
                        <span>Asunto</span>
                    </div>
                    <input type="text" name="asunto" id="masivo-asunto"
                        class="masivo-input"
                        placeholder="Ej: Reunión de personal, Aviso importante..." required>
                </div>

                <!-- Paso 3 -->
                <div class="masivo-section">
                    <div class="masivo-section-label">
                        <span class="masivo-step">3</span>
                        <span>Mensaje</span>
                    </div>
                    <textarea name="contenido" id="masivo-contenido"
                        class="masivo-textarea"
                        placeholder="Redacte aquí el mensaje para todos los destinatarios..."
                        required></textarea>
                </div>

                <!-- Aviso -->
                <div class="masivo-aviso">
                    <i class='bx bx-info-circle'></i>
                    <span>Cada usuario recibirá este mensaje en su bandeja de forma individual.</span>
                </div>

            </div>

            <!-- Footer -->
            <div class="masivo-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('modal-mensaje-masivo')">
                    <i class='bx bx-x'></i> Cancelar
                </button>
                <button type="submit" class="masivo-btn-send" id="btn-enviar-masivo">
                    <i class='bx bx-broadcast'></i> Enviar Mensaje
                </button>
            </div>
        </form>

    </div>
</div>

<script>
// Envío masivo
document.getElementById('form-mensaje-masivo')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-enviar-masivo');
    btn.disabled = true;
    btn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Enviando...';

    const formData = new FormData(this);
    const result = await apiRequest('index.php?module=mensajes&action=storeMasivo', Object.fromEntries(formData));

    if (result.success) {
        showToast(result.message);
        closeAllModals();
        this.reset();
        setTimeout(() => location.reload(), 1500);
    } else {
        showToast(result.message, 'error');
    }

    btn.disabled = false;
    btn.innerHTML = '<i class="bx bx-broadcast"></i> Enviar a Todos';
});
</script>

<?php
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) return 'Hace un momento';
    if ($diff < 3600) return 'Hace ' . floor($diff / 60) . ' min';
    if ($diff < 86400) return 'Hace ' . floor($diff / 3600) . ' h';
    if ($diff < 604800) return 'Hace ' . floor($diff / 86400) . ' días';
    return date('d/m/Y', $timestamp);
}
?>
