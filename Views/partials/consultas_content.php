<?php
$consultas  = $consultas  ?? [];
$pacientes  = $pacientes  ?? [];
$medicos    = $medicos    ?? [];
$pagination = $pagination ?? null;
?>

<div class="head-title">
    <div class="left">
        <h1>Gestión de Consultas</h1>
        <ul class="breadcrumb">
            <li><a href="index.php?module=dashboard">Inicio</a></li>
            <li><i class='bx bx-chevron-right'></i></li>
            <li><a class="active" href="#">Consultas</a></li>
        </ul>
    </div>
    <div class="head-actions">
        <div class="btn-group">
            <button class="btn-export" onclick="openModal('modal-exportar-consultas')" title="Exportar por rango de fechas">
                <i class='bx bx-calendar-alt'></i> Por Fechas
            </button>
            <button class="btn-export" onclick="window.location.href='index.php?module=consultas&action=exportar&periodo=semanal'" title="Exportar última semana">
                <i class='bx bx-download'></i> Semana
            </button>
            <button class="btn-export" onclick="window.location.href='index.php?module=consultas&action=exportar&periodo=mensual'" title="Exportar último mes">
                <i class='bx bx-download'></i> Mes
            </button>
            <button class="btn-export" onclick="window.location.href='index.php?module=consultas&action=exportar&periodo=todos'" title="Exportar todo">
                <i class='bx bx-export'></i> Todo
            </button>
        </div>
        <button class="btn-primary" onclick="openModal('modal-consulta')">
            <i class='bx bx-plus'></i> Nueva Consulta
        </button>
    </div>
</div>

<div class="table-container">
    <div class="table-header">
        <h3><i class='bx bx-clipboard'></i> Listado de Consultas</h3>
        <div class="table-stats">
            <span class="badge badge-info">
                <i class='bx bx-calendar-check'></i>
                <?php echo $pagination ? $pagination['total'] : count($consultas); ?> consultas
            </span>
        </div>
    </div>
    <div class="table-responsive">
        <table class="data-table" id="consultas-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Paciente</th>
                    <th>Médico</th>
                    <th>Fecha/Hora</th>
                    <th>Motivo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($consultas)): ?>
                    <?php $i = 1; foreach ($consultas as $c): ?>
                        <tr data-id="<?php echo $c->id; ?>">
                            <td><span class="cell-num"><?php echo $i++; ?></span></td>
                            <td>
                                <div class="cell-user">
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($c->nombre . '+' . $c->apellido); ?>&background=3C91E6&color=fff" alt="">
                                    <span><?php echo htmlspecialchars($c->nombre . ' ' . $c->apellido); ?></span>
                                </div>
                            </td>
                            <td><span class="cell-doctor"><i class='bx bx-user-md'></i> <?php echo htmlspecialchars($c->medico ?? '-'); ?></span></td>
                            <td>
                                <div class="cell-date">
                                    <i class='bx bx-calendar'></i> <?php echo date('d/m/Y', strtotime($c->fecha_hora)); ?>
                                    <br><small><i class='bx bx-time-five'></i> <?php echo date('H:i', strtotime($c->fecha_hora)); ?></small>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars(substr($c->motivo, 0, 40)) . (strlen($c->motivo) > 40 ? '...' : ''); ?></td>
                            <td>
                                <span class="status status-<?php echo $c->estado; ?>">
                                    <?php 
                                    $estados = [
                                        'pendiente' => 'Pendiente',
                                        'en_proceso' => 'En Proceso',
                                        'completada' => 'Completada',
                                        'cancelada' => 'Cancelada'
                                    ];
                                    echo $estados[$c->estado] ?? ucfirst($c->estado); 
                                    ?>
                                </span>
                            </td>
                            <td class="actions">
                                <button class="btn-icon btn-view" onclick="viewConsulta(<?php echo $c->id; ?>)" title="Ver detalles">
                                    <i class='bx bx-show'></i>
                                </button>
                                <button class="btn-icon btn-edit" onclick="editConsulta(<?php echo $c->id; ?>)" title="Editar">
                                    <i class='bx bx-edit'></i>
                                </button>
                                <button class="btn-icon btn-delete" onclick="confirmDeleteConsulta(<?php echo $c->id; ?>)" title="Eliminar">
                                    <i class='bx bx-trash'></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="empty-row">
                        <div class="empty-state">
                            <i class='bx bx-calendar-x'></i>
                            <p>No hay consultas registradas</p>
                            <button class="btn-primary btn-sm" onclick="openModal('modal-consulta')">Nueva Consulta</button>
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

<!-- MODAL CONSULTA -->
<div class="modal" id="modal-consulta">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h2 id="modal-consulta-title"><i class='bx bx-plus-circle'></i> Nueva Consulta</h2>
            <button class="modal-close" onclick="closeModal('modal-consulta')">&times;</button>
        </div>
        <form id="form-consulta" class="modal-form">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="id" id="consulta-id">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="consulta-paciente_id"><i class='bx bx-user'></i> Paciente *</label>
                    <select name="paciente_id" id="consulta-paciente_id" required>
                        <option value="">Seleccionar paciente</option>
                        <?php foreach ($pacientes as $p): ?>
                            <option value="<?php echo $p->id; ?>"><?php echo htmlspecialchars($p->nombre . ' ' . $p->apellido . ' (' . $p->cedula . ')'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="consulta-empleado_id"><i class='bx bx-plus-medical'></i> Médico *</label>
                    <select name="empleado_id" id="consulta-empleado_id" required>
                        <option value="">Seleccionar médico</option>
                        <?php if (!empty($medicos)): ?>
                            <?php foreach ($medicos as $m): ?>
                                <option value="<?php echo $m->id; ?>">
                                    <?php echo htmlspecialchars($m->nombre_completo . ' — ' . $m->cargo); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="" disabled>No hay médicos disponibles</option>
                        <?php endif; ?>
                    </select>
                    <?php if (empty($medicos)): ?>
                        <small style="color: #dc3545; display: block; margin-top: 5px;">
                            <i class='bx bx-error-circle'></i> 
                            No hay empleados médicos activos. Ve a <a href="index.php?module=nomina" style="color: #3C91E6;">Nómina</a> para agregar personal médico.
                        </small>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="consulta-fecha_hora"><i class='bx bx-calendar'></i> Fecha y Hora *</label>
                    <input type="datetime-local" name="fecha_hora" id="consulta-fecha_hora" required>
                </div>
                <div class="form-group">
                    <label for="consulta-estado"><i class='bx bx-info-circle'></i> Estado</label>
                    <select name="estado" id="consulta-estado">
                        <option value="pendiente">Pendiente</option>
                        <option value="en_proceso">En Proceso</option>
                        <option value="completada">Completada</option>
                        <option value="cancelada">Cancelada</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="consulta-motivo"><i class='bx bx-comment-detail'></i> Motivo de la Consulta *</label>
                <textarea name="motivo" id="consulta-motivo" rows="3" placeholder="Describa el motivo de la consulta" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="consulta-diagnostico"><i class='bx bx-clipboard'></i> Diagnóstico</label>
                <textarea name="diagnostico" id="consulta-diagnostico" rows="3" placeholder="Diagnóstico médico"></textarea>
            </div>
            
            <div class="form-group">
                <label for="consulta-tratamiento"><i class='bx bx-capsule'></i> Tratamiento</label>
                <textarea name="tratamiento" id="consulta-tratamiento" rows="3" placeholder="Tratamiento indicado"></textarea>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('modal-consulta')">
                    <i class='bx bx-x'></i> Cancelar
                </button>
                <button type="submit" class="btn-primary">
                    <i class='bx bx-save'></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL VER CONSULTA -->
<div class="modal" id="modal-ver-consulta">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h2><i class='bx bx-clipboard'></i> Detalles de la Consulta</h2>
            <button class="modal-close" onclick="closeModal('modal-ver-consulta')">&times;</button>
        </div>
        <div class="modal-body" id="ver-consulta-content">
            <!-- Contenido dinámico -->
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeModal('modal-ver-consulta')">Cerrar</button>
        </div>
    </div>
</div>

<!-- MODAL EXPORTAR CON FECHAS -->
<div class="modal" id="modal-exportar-consultas">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class='bx bx-download'></i> Exportar Consultas por Rango de Fechas</h2>
            <button class="modal-close" onclick="closeModal('modal-exportar-consultas')">&times;</button>
        </div>
        <form id="form-exportar-consultas" class="modal-form" method="GET" action="index.php">
            <input type="hidden" name="module" value="consultas">
            <input type="hidden" name="action" value="exportar">
            <input type="hidden" name="periodo" value="rango">
            
            <div class="form-group">
                <label for="fecha_inicio"><i class='bx bx-calendar'></i> Fecha Inicio *</label>
                <input type="date" name="fecha_inicio" id="fecha_inicio" required>
            </div>
            
            <div class="form-group">
                <label for="fecha_fin"><i class='bx bx-calendar-check'></i> Fecha Fin *</label>
                <input type="date" name="fecha_fin" id="fecha_fin" required>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('modal-exportar-consultas')">
                    <i class='bx bx-x'></i> Cancelar
                </button>
                <button type="submit" class="btn-primary">
                    <i class='bx bx-download'></i> Exportar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EXPORTAR RANGO PDF -->
<div class="modal" id="modal-exportar-rango-pdf">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class='bx bx-file-pdf'></i> Exportar a PDF - Rango Personalizado</h2>
            <button class="modal-close" onclick="closeModal('modal-exportar-rango-pdf')">&times;</button>
        </div>
        <form id="form-exportar-rango-pdf" class="modal-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="pdf_fecha_inicio"><i class='bx bx-calendar'></i> Fecha Inicio *</label>
                    <input type="date" id="pdf_fecha_inicio" required>
                </div>
                <div class="form-group">
                    <label for="pdf_fecha_fin"><i class='bx bx-calendar-check'></i> Fecha Fin *</label>
                    <input type="date" id="pdf_fecha_fin" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('modal-exportar-rango-pdf')">
                    <i class='bx bx-x'></i> Cancelar
                </button>
                <button type="button" class="btn-primary" onclick="exportarConsultasRango('pdf')">
                    <i class='bx bx-file-pdf'></i> Exportar PDF
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EXPORTAR RANGO EXCEL -->
<div class="modal" id="modal-exportar-rango-excel">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class='bx bx-file-spreadsheet'></i> Exportar a Excel - Rango Personalizado</h2>
            <button class="modal-close" onclick="closeModal('modal-exportar-rango-excel')">&times;</button>
        </div>
        <form id="form-exportar-rango-excel" class="modal-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="excel_fecha_inicio"><i class='bx bx-calendar'></i> Fecha Inicio *</label>
                    <input type="date" id="excel_fecha_inicio" required>
                </div>
                <div class="form-group">
                    <label for="excel_fecha_fin"><i class='bx bx-calendar-check'></i> Fecha Fin *</label>
                    <input type="date" id="excel_fecha_fin" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('modal-exportar-rango-excel')">
                    <i class='bx bx-x'></i> Cancelar
                </button>
                <button type="button" class="btn-primary" onclick="exportarConsultasRango('excel')">
                    <i class='bx bx-file-spreadsheet'></i> Exportar Excel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EXPORTAR RANGO CSV -->
<div class="modal" id="modal-exportar-rango-csv">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class='bx bx-file-csv'></i> Exportar a CSV - Rango Personalizado</h2>
            <button class="modal-close" onclick="closeModal('modal-exportar-rango-csv')">&times;</button>
        </div>
        <form id="form-exportar-rango-csv" class="modal-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="csv_fecha_inicio"><i class='bx bx-calendar'></i> Fecha Inicio *</label>
                    <input type="date" id="csv_fecha_inicio" required>
                </div>
                <div class="form-group">
                    <label for="csv_fecha_fin"><i class='bx bx-calendar-check'></i> Fecha Fin *</label>
                    <input type="date" id="csv_fecha_fin" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('modal-exportar-rango-csv')">
                    <i class='bx bx-x'></i> Cancelar
                </button>
                <button type="button" class="btn-primary" onclick="exportarConsultasRango('csv')">
                    <i class='bx bx-file-csv'></i> Exportar CSV
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Establecer fecha de hoy como máximo y fecha de inicio por defecto
document.addEventListener('DOMContentLoaded', function() {
    const hoy = new Date().toISOString().split('T')[0];
    const hace30dias = new Date(Date.now() - 30*24*60*60*1000).toISOString().split('T')[0];
    
    const fechaInicio = document.getElementById('fecha_inicio');
    const fechaFin = document.getElementById('fecha_fin');
    
    if (fechaInicio) {
        fechaInicio.value = hace30dias;
        fechaInicio.max = hoy;
    }
    if (fechaFin) {
        fechaFin.value = hoy;
        fechaFin.max = hoy;
    }
});
</script>
