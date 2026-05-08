<?php
$stats              = $stats              ?? ['pacientes' => 0, 'consultas' => 0, 'inventario' => 0, 'mensajes' => 0];
$consultasRecientes = $consultasRecientes ?? [];
$bitacora           = $bitacora           ?? null;
$rol                = $rol                ?? 'recepcionista';
?>

<div class="head-title">
    <div class="left">
        <h1>Panel de Control</h1>
        <ul class="breadcrumb">
            <li><a href="index.php?module=dashboard">Inicio</a></li>
            <li><i class='bx bx-chevron-right'></i></li>
            <li><a class="active" href="#">Dashboard</a></li>
        </ul>
    </div>
</div>

<ul class="box-info">
    <li onclick="location.href='index.php?module=pacientes'" style="cursor:pointer;">
        <i class='bx bxs-user-detail'></i>
        <span class="text">
            <h3><?php echo $stats['pacientes']; ?></h3>
            <p>Pacientes Registrados</p>
        </span>
    </li>
    <li onclick="location.href='index.php?module=consultas'" style="cursor:pointer;">
        <i class='bx bxs-calendar-check'></i>
        <span class="text">
            <h3><?php echo $stats['consultas']; ?></h3>
            <p>Consultas Hoy</p>
        </span>
    </li>
    <li onclick="location.href='index.php?module=medicina'" style="cursor:pointer;" class="<?php echo $stats['inventario'] > 0 ? 'alert-box' : ''; ?>">
        <i class='bx bxs-capsule'></i>
        <span class="text">
            <h3><?php echo $stats['inventario']; ?></h3>
            <p>Alertas de Stock</p>
        </span>
    </li>
</ul>

<div class="table-data">
    <div class="order">
        <div class="head">
            <h3>Consultas Recientes</h3>
            <a href="index.php?module=consultas" style="color: var(--primary); font-size: 13px;">Ver todas</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Paciente</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($consultasRecientes)): ?>
                    <?php foreach ($consultasRecientes as $c): ?>
                        <tr>
                            <td>
                                <div class="cell-user">
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($c->pac_nombre . '+' . $c->pac_apellido); ?>&background=0d6efd&color=fff&size=32" alt="">
                                    <span><?php echo htmlspecialchars($c->pac_nombre . ' ' . $c->pac_apellido); ?></span>
                                </div>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($c->fecha_hora)); ?></td>
                            <td><span class="status status-<?php echo $c->estado; ?>"><?php echo ucfirst(str_replace('_', ' ', $c->estado)); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="3" style="text-align:center; padding: 30px; color: var(--text-muted);">No hay consultas recientes</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="todo">
        <div class="head">
            <h3>Acciones Rápidas</h3>
        </div>
        <ul class="todo-list">
            <li class="completed" onclick="location.href='index.php?module=pacientes'" style="cursor:pointer;">
                <p><i class='bx bx-plus-circle'></i> Registrar Paciente</p>
            </li>
            <li class="completed" onclick="location.href='index.php?module=consultas'" style="cursor:pointer;">
                <p><i class='bx bx-plus-circle'></i> Nueva Consulta</p>
            </li>
            <li class="not-completed" onclick="location.href='index.php?module=medicina'" style="cursor:pointer;">
                <p><i class='bx bx-package'></i> Ver Inventario</p>
            </li>
            <li class="completed" onclick="location.href='index.php?module=mensajes'" style="cursor:pointer;">
                <p><i class='bx bx-envelope'></i> Mensajes (<?php echo $stats['mensajes']; ?> nuevos)</p>
            </li>
        </ul>
    </div>
</div>

<?php if ($rol === 'admin' && $bitacora !== null): ?>
<!-- BITÁCORA - Solo visible para admin -->
<div class="table-container" style="margin-top: 24px;">
    <div class="table-header">
        <h3><i class='bx bx-history'></i> Bitácora del Sistema</h3>
        <span class="badge badge-info">Últimos 15 registros</span>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Fecha/Hora</th>
                    <th>Usuario</th>
                    <th>Acción</th>
                    <th>Detalles</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($bitacora)): ?>
                    <?php foreach ($bitacora as $b): ?>
                        <?php
                        // Determinar color de badge según la acción
                        $badgeClass = 'badge-primary';
                        if (strpos($b->accion, 'ELIMINAR') !== false) $badgeClass = 'badge-danger';
                        elseif (strpos($b->accion, 'CREAR') !== false) $badgeClass = 'badge-success';
                        elseif (strpos($b->accion, 'EDITAR') !== false || strpos($b->accion, 'ACTUALIZAR') !== false) $badgeClass = 'badge-warning';
                        elseif (strpos($b->accion, 'INICIO') !== false || strpos($b->accion, 'CIERRE') !== false) $badgeClass = 'badge-info';
                        elseif (strpos($b->accion, 'DESACTIVAR') !== false) $badgeClass = 'badge-danger';
                        elseif (strpos($b->accion, 'ACTIVAR') !== false) $badgeClass = 'badge-success';
                        ?>
                        <tr>
                            <td>
                                <div class="cell-date">
                                    <i class='bx bx-calendar'></i> <?php echo date('d/m/Y', strtotime($b->created_at)); ?>
                                    <br><small><i class='bx bx-time-five'></i> <?php echo date('H:i:s', strtotime($b->created_at)); ?></small>
                                </div>
                            </td>
                            <td>
                                <div class="cell-user">
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($b->nombre_completo); ?>&background=0d6efd&color=fff&size=32" alt="">
                                    <span><?php echo htmlspecialchars($b->nombre_completo); ?></span>
                                </div>
                            </td>
                            <td><span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($b->accion); ?></span></td>
                            <td><?php echo htmlspecialchars($b->detalles ?? '-'); ?></td>
                            <td><code><?php echo htmlspecialchars($b->ip); ?></code></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="empty-row">
                        <div class="empty-state">
                            <i class='bx bx-history'></i>
                            <p>No hay registros en la bitácora</p>
                        </div>
                    </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
