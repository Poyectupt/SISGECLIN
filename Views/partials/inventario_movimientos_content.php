<?php
$movimientos = $movimientos ?? [];
$estadisticas = $estadisticas ?? [];
$periodo = $periodo ?? 'semanal';
$tipo_filtro = $tipo_filtro ?? '';
?>

<div class="head-title">
    <div class="left">
        <h1>Movimientos de Inventario</h1>
        <ul class="breadcrumb">
            <li><a href="index.php?module=dashboard">Inicio</a></li>
            <li><i class='bx bx-chevron-right'></i></li>
            <li><a href="index.php?module=medicina">Medicamentos</a></li>
            <li><i class='bx bx-chevron-right'></i></li>
            <li><a class="active" href="#">Movimientos</a></li>
        </ul>
    </div>
    <div class="head-actions">
        <div class="btn-group">
            <button class="btn-export" onclick="openModal('modal-exportar-movimientos')" title="Exportar por rango de fechas">
                <i class='bx bx-calendar-alt'></i> Por Fechas
            </button>
            <button class="btn-export" onclick="window.location.href='index.php?module=inventario_movimientos&action=exportar&periodo=semanal'" title="Exportar última semana">
                <i class='bx bx-download'></i> Semana
            </button>
            <button class="btn-export" onclick="window.location.href='index.php?module=inventario_movimientos&action=exportar&periodo=mensual'" title="Exportar último mes">
                <i class='bx bx-download'></i> Mes
            </button>
            <button class="btn-export" onclick="window.location.href='index.php?module=inventario_movimientos&action=exportar&periodo=todos'" title="Exportar todo">
                <i class='bx bx-export'></i> Todo
            </button>
        </div>
        <button class="btn-success" onclick="openModal('modal-entrada')">
            <i class='bx bx-plus-circle'></i> Registrar Entrada
        </button>
        <button class="btn-warning" onclick="openModal('modal-salida')">
            <i class='bx bx-minus-circle'></i> Registrar Salida
        </button>
    </div>
</div>

<!-- Estadísticas -->
<div class="stats-cards" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 24px;">
    <?php
    $totalEntradas = 0;
    $totalSalidas = 0;
    foreach ($estadisticas as $stat) {
        if ($stat->tipo_movimiento === 'entrada') {
            $totalEntradas += $stat->total_cantidad;
        } else {
            $totalSalidas += $stat->total_cantidad;
        }
    }
    ?>
    <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 24px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
        <div style="display: flex; align-items: center; gap: 16px;">
            <i class='bx bx-trending-up' style="font-size: 48px; opacity: 0.8;"></i>
            <div>
                <h3 style="margin: 0; font-size: 32px; font-weight: 700;"><?php echo $totalEntradas; ?></h3>
                <p style="margin: 4px 0 0 0; opacity: 0.9;">Total Entradas (<?php echo $periodo === 'semanal' ? 'Semana' : 'Mes'; ?>)</p>
            </div>
        </div>
    </div>
    
    <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 24px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
        <div style="display: flex; align-items: center; gap: 16px;">
            <i class='bx bx-trending-down' style="font-size: 48px; opacity: 0.8;"></i>
            <div>
                <h3 style="margin: 0; font-size: 32px; font-weight: 700;"><?php echo $totalSalidas; ?></h3>
                <p style="margin: 4px 0 0 0; opacity: 0.9;">Total Salidas (<?php echo $periodo === 'semanal' ? 'Semana' : 'Mes'; ?>)</p>
            </div>
        </div>
    </div>
    
    <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 24px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
        <div style="display: flex; align-items: center; gap: 16px;">
            <i class='bx bx-transfer' style="font-size: 48px; opacity: 0.8;"></i>
            <div>
                <h3 style="margin: 0; font-size: 32px; font-weight: 700;"><?php echo count($movimientos); ?></h3>
                <p style="margin: 4px 0 0 0; opacity: 0.9;">Movimientos Recientes</p>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="filters-section">
    <div class="filters-header">
        <h3><i class='bx bx-filter-alt'></i> Filtros de Movimientos</h3>
        <?php if ($tipo_filtro || $periodo !== 'semanal'): ?>
            <span class="filter-badge"><i class='bx bx-check-circle'></i> Activos</span>
        <?php endif; ?>
    </div>
    <div class="filters-row">

        <div class="flt-group">
            <label class="flt-label" for="filter-periodo">
                <i class='bx bx-calendar'></i> Período
            </label>
            <select id="filter-periodo" class="flt-select" onchange="aplicarFiltros()">
                <option value="semanal" <?php echo $periodo === 'semanal' ? 'selected' : ''; ?>>Última Semana</option>
                <option value="mensual" <?php echo $periodo === 'mensual' ? 'selected' : ''; ?>>Último Mes</option>
            </select>
        </div>

        <div class="flt-group">
            <label class="flt-label" for="filter-tipo">
                <i class='bx bx-transfer'></i> Tipo
            </label>
            <select id="filter-tipo" class="flt-select" onchange="aplicarFiltros()">
                <option value="">Todos</option>
                <option value="entrada" <?php echo $tipo_filtro === 'entrada' ? 'selected' : ''; ?>>Entradas</option>
                <option value="salida" <?php echo $tipo_filtro === 'salida' ? 'selected' : ''; ?>>Salidas</option>
            </select>
        </div>

        <div class="flt-group">
            <label class="flt-label flt-label-hidden">‎</label>
            <button class="flt-btn-reset" onclick="resetearFiltros()">
                <i class='bx bx-refresh'></i> Resetear
            </button>
        </div>

    </div>
</div>

<!-- Tabla de Movimientos -->
<div class="table-container">
    <div class="table-header">
        <h3><i class='bx bx-history'></i> Historial de Movimientos</h3>
        <div class="table-stats">
            <span class="badge badge-info">
                <i class='bx bx-transfer'></i>
                <?php echo count($movimientos); ?> movimientos
            </span>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Fecha/Hora</th>
                    <th>Medicamento</th>
                    <th>Tipo</th>
                    <th>Cantidad</th>
                    <th>Stock Anterior</th>
                    <th>Stock Nuevo</th>
                    <th>Usuario</th>
                    <th>Motivo</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($movimientos)): ?>
                    <?php $i = 1; foreach ($movimientos as $m): ?>
                        <tr>
                            <td><span class="cell-num"><?php echo $i++; ?></span></td>
                            <td>
                                <div class="cell-date">
                                    <i class='bx bx-calendar'></i> <?php echo date('d/m/Y', strtotime($m->created_at)); ?>
                                    <br><small><i class='bx bx-time-five'></i> <?php echo date('H:i', strtotime($m->created_at)); ?></small>
                                </div>
                            </td>
                            <td>
                                <div class="cell-medicine">
                                    <i class='bx bx-capsule'></i>
                                    <span><?php echo htmlspecialchars($m->nombre_medicamento); ?></span>
                                </div>
                            </td>
                            <td>
                                <?php if ($m->tipo_movimiento === 'entrada'): ?>
                                    <span class="badge badge-success"><i class='bx bx-up-arrow-alt'></i> Entrada</span>
                                <?php else: ?>
                                    <span class="badge badge-danger"><i class='bx bx-down-arrow-alt'></i> Salida</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong class="<?php echo $m->tipo_movimiento === 'entrada' ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo $m->tipo_movimiento === 'entrada' ? '+' : '-'; ?><?php echo $m->cantidad; ?>
                                </strong>
                            </td>
                            <td><?php echo $m->cantidad_anterior; ?></td>
                            <td><strong><?php echo $m->cantidad_nueva; ?></strong></td>
                            <td>
                                <div class="cell-user">
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($m->usuario_nombre); ?>&background=0d6efd&color=fff&size=32" alt="">
                                    <span><?php echo htmlspecialchars($m->usuario_nombre); ?></span>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($m->motivo ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="9" class="empty-row">
                        <div class="empty-state">
                            <i class='bx bx-package'></i>
                            <p>No hay movimientos registrados</p>
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

<!-- MODAL ENTRADA -->
<div class="modal" id="modal-entrada">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class='bx bx-plus-circle'></i> Registrar Entrada de Inventario</h2>
            <button class="modal-close" onclick="closeModal('modal-entrada')">&times;</button>
        </div>
        <form id="form-entrada" class="modal-form">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="form-group">
                <label for="entrada-medicamento_id"><i class='bx bx-capsule'></i> Medicamento *</label>
                <select name="medicamento_id" id="entrada-medicamento_id" required>
                    <option value="">Seleccionar medicamento</option>
                    <?php
                    $medicamentos = (new \App\Models\Medicamento())->getAll();
                    foreach ($medicamentos as $med):
                    ?>
                        <option value="<?php echo $med->id; ?>">
                            <?php echo htmlspecialchars($med->nombre_medicamento); ?> (Stock: <?php echo $med->cantidad; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="entrada-cantidad"><i class='bx bx-package'></i> Cantidad *</label>
                <input type="number" name="cantidad" id="entrada-cantidad" min="1" placeholder="0" required>
            </div>
            
            <div class="form-group">
                <label for="entrada-motivo"><i class='bx bx-detail'></i> Motivo</label>
                <textarea name="motivo" id="entrada-motivo" rows="3" placeholder="Compra, donación, etc.">Entrada de inventario</textarea>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('modal-entrada')">
                    <i class='bx bx-x'></i> Cancelar
                </button>
                <button type="submit" class="btn-success">
                    <i class='bx bx-save'></i> Registrar Entrada
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL SALIDA -->
<div class="modal" id="modal-salida">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class='bx bx-minus-circle'></i> Registrar Salida de Inventario</h2>
            <button class="modal-close" onclick="closeModal('modal-salida')">&times;</button>
        </div>
        <form id="form-salida" class="modal-form">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="form-group">
                <label for="salida-medicamento_id"><i class='bx bx-capsule'></i> Medicamento *</label>
                <select name="medicamento_id" id="salida-medicamento_id" required>
                    <option value="">Seleccionar medicamento</option>
                    <?php foreach ($medicamentos as $med): ?>
                        <option value="<?php echo $med->id; ?>">
                            <?php echo htmlspecialchars($med->nombre_medicamento); ?> (Stock: <?php echo $med->cantidad; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="salida-cantidad"><i class='bx bx-package'></i> Cantidad *</label>
                <input type="number" name="cantidad" id="salida-cantidad" min="1" placeholder="0" required>
            </div>
            
            <div class="form-group">
                <label for="salida-motivo"><i class='bx bx-detail'></i> Motivo</label>
                <textarea name="motivo" id="salida-motivo" rows="3" placeholder="Dispensación, vencimiento, etc.">Salida de inventario</textarea>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('modal-salida')">
                    <i class='bx bx-x'></i> Cancelar
                </button>
                <button type="submit" class="btn-warning">
                    <i class='bx bx-save'></i> Registrar Salida
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EXPORTAR CON FECHAS -->
<div class="modal" id="modal-exportar-movimientos">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class='bx bx-download'></i> Exportar Movimientos por Rango de Fechas</h2>
            <button class="modal-close" onclick="closeModal('modal-exportar-movimientos')">&times;</button>
        </div>
        <form id="form-exportar-movimientos" class="modal-form" method="GET" action="index.php">
            <input type="hidden" name="module" value="inventario_movimientos">
            <input type="hidden" name="action" value="exportar">
            <input type="hidden" name="periodo" value="rango">
            
            <div class="form-group">
                <label for="mov_fecha_inicio"><i class='bx bx-calendar'></i> Fecha Inicio *</label>
                <input type="date" name="fecha_inicio" id="mov_fecha_inicio" required>
            </div>
            
            <div class="form-group">
                <label for="mov_fecha_fin"><i class='bx bx-calendar-check'></i> Fecha Fin *</label>
                <input type="date" name="fecha_fin" id="mov_fecha_fin" required>
            </div>
            
            <div class="form-group">
                <label for="mov_tipo"><i class='bx bx-filter'></i> Tipo de Movimiento</label>
                <select name="tipo" id="mov_tipo">
                    <option value="">Todos</option>
                    <option value="entrada">Solo Entradas</option>
                    <option value="salida">Solo Salidas</option>
                </select>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('modal-exportar-movimientos')">
                    <i class='bx bx-x'></i> Cancelar
                </button>
                <button type="submit" class="btn-primary">
                    <i class='bx bx-download'></i> Exportar
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
    
    const movFechaInicio = document.getElementById('mov_fecha_inicio');
    const movFechaFin = document.getElementById('mov_fecha_fin');
    
    if (movFechaInicio) {
        movFechaInicio.value = hace30dias;
        movFechaInicio.max = hoy;
    }
    if (movFechaFin) {
        movFechaFin.value = hoy;
        movFechaFin.max = hoy;
    }
});

// Formulario de entrada
document.getElementById('form-entrada')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const result = await apiRequest('index.php?module=inventario_movimientos&action=registrarEntrada', Object.fromEntries(formData));
    
    if (result.success) {
        showToast(result.message);
        closeAllModals();
        setTimeout(() => location.reload(), 1500);
    } else {
        showToast(result.message, 'error');
    }
});

// Formulario de salida
document.getElementById('form-salida')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const result = await apiRequest('index.php?module=inventario_movimientos&action=registrarSalida', Object.fromEntries(formData));
    
    if (result.success) {
        showToast(result.message);
        closeAllModals();
        setTimeout(() => location.reload(), 1500);
    } else {
        showToast(result.message, 'error');
    }
});

// Funciones de filtros
function aplicarFiltros() {
    const periodo = document.getElementById('filter-periodo').value;
    const tipo = document.getElementById('filter-tipo').value;
    
    let url = 'index.php?module=inventario_movimientos';
    if (periodo) {
        url += '&periodo=' + encodeURIComponent(periodo);
    }
    if (tipo) {
        url += '&tipo=' + encodeURIComponent(tipo);
    }
    
    window.location.href = url;
}

function resetearFiltros() {
    document.getElementById('filter-periodo').value = 'semanal';
    document.getElementById('filter-tipo').value = '';
    window.location.href = 'index.php?module=inventario_movimientos';
}
</script>
