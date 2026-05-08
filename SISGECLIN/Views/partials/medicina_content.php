<?php
$medicamentos = $medicamentos ?? [];
$search       = $search       ?? '';
?>

<div class="head-title">
    <div class="left">
        <h1>Inventario de Medicamentos</h1>
        <ul class="breadcrumb">
            <li><a href="index.php?module=dashboard">Inicio</a></li>
            <li><i class='bx bx-chevron-right'></i></li>
            <li><a class="active" href="#">Medicamentos</a></li>
        </ul>
    </div>
    <div class="head-actions">
        <button class="btn-export" onclick="exportTable('medicamentos-table', 'medicamentos')">
            <i class='bx bx-export'></i> Exportar
        </button>
        <button class="btn-primary" onclick="openModal('modal-medicamento')">
            <i class='bx bx-plus'></i> Nuevo Medicamento
        </button>
    </div>
</div>

<div class="table-container">
    <div class="table-header">
        <h3><i class='bx bx-capsule'></i> Inventario de Medicamentos</h3>
        <div class="table-stats">
            <?php 
            $lowStock = 0;
            $expiringSoon = 0;
            foreach ($medicamentos as $m) {
                if ($m->cantidad <= $m->stock_minimo) $lowStock++;
                if ($m->fecha_vencimiento && strtotime($m->fecha_vencimiento) <= strtotime('+30 days')) $expiringSoon++;
            }
            ?>
            <span class="badge badge-info"><i class='bx bx-package'></i> <?php echo count($medicamentos); ?> productos</span>
            <?php if ($lowStock > 0): ?>
                <span class="badge badge-danger"><i class='bx bx-error'></i> <?php echo $lowStock; ?> stock bajo</span>
            <?php endif; ?>
            <?php if ($expiringSoon > 0): ?>
                <span class="badge badge-warning"><i class='bx bx-calendar-exclamation'></i> <?php echo $expiringSoon; ?> por vencer</span>
            <?php endif; ?>
        </div>
    </div>
    <div class="table-responsive">
        <table class="data-table" id="medicamentos-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Cantidad</th>
                    <th>Unidad</th>
                    <th>Precio</th>
                    <th>Vencimiento</th>
                    <th>Stock</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($medicamentos)): ?>
                    <?php $i = 1; foreach ($medicamentos as $m): ?>
                        <?php 
                        $isLowStock = $m->cantidad <= $m->stock_minimo;
                        $isExpiringSoon = $m->fecha_vencimiento && strtotime($m->fecha_vencimiento) <= strtotime('+30 days');
                        $isExpired = $m->fecha_vencimiento && strtotime($m->fecha_vencimiento) < time();
                        ?>
                        <tr data-id="<?php echo $m->id; ?>" class="<?php echo $isLowStock ? 'row-warning' : ''; ?> <?php echo $isExpired ? 'row-danger' : ''; ?>">
                            <td><span class="cell-num"><?php echo $i++; ?></span></td>
                            <td>
                                <div class="cell-medicine">
                                    <i class='bx bx-capsule'></i>
                                    <span><?php echo htmlspecialchars($m->nombre_medicamento); ?></span>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars(substr($m->descripcion ?? '', 0, 35)) . (strlen($m->descripcion ?? '') > 35 ? '...' : ''); ?></td>
                            <td>
                                <span class="cell-quantity <?php echo $isLowStock ? 'text-danger' : ''; ?>">
                                    <?php echo $m->cantidad; ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($m->unidad); ?></td>
                            <td><span class="cell-price">$<?php echo number_format($m->precio, 2); ?></span></td>
                            <td>
                                <?php if ($m->fecha_vencimiento): ?>
                                    <span class="cell-date <?php echo $isExpiringSoon ? 'text-warning' : ''; ?> <?php echo $isExpired ? 'text-danger' : ''; ?>">
                                        <i class='bx bx-calendar'></i> <?php echo date('d/m/Y', strtotime($m->fecha_vencimiento)); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($isExpired): ?>
                                    <span class="badge badge-danger"><i class='bx bx-error'></i> Vencido</span>
                                <?php elseif ($isLowStock): ?>
                                    <span class="badge badge-warning"><i class='bx bx-down-arrow'></i> Bajo</span>
                                <?php else: ?>
                                    <span class="badge badge-success"><i class='bx bx-check'></i> OK</span>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <button class="btn-icon btn-view" onclick="viewMedicamento(<?php echo $m->id; ?>)" title="Ver detalles">
                                    <i class='bx bx-show'></i>
                                </button>
                                <button class="btn-icon btn-edit" onclick="editMedicamento(<?php echo $m->id; ?>)" title="Editar">
                                    <i class='bx bx-edit'></i>
                                </button>
                                <button class="btn-icon btn-delete" onclick="confirmDeleteMedicamento(<?php echo $m->id; ?>)" title="Eliminar">
                                    <i class='bx bx-trash'></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="9" class="empty-row">
                        <div class="empty-state">
                            <i class='bx bx-package'></i>
                            <p>No hay medicamentos registrados</p>
                            <button class="btn-primary btn-sm" onclick="openModal('modal-medicamento')">Agregar Medicamento</button>
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

<!-- MODAL MEDICAMENTO -->
<div class="modal" id="modal-medicamento">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modal-medicamento-title"><i class='bx bx-plus-circle'></i> Nuevo Medicamento</h2>
            <button class="modal-close" onclick="closeModal('modal-medicamento')">&times;</button>
        </div>
        <form id="form-medicamento" class="modal-form">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="id" id="medicamento-id">
            
            <div class="form-group">
                <label for="medicamento-nombre_medicamento"><i class='bx bx-capsule'></i> Nombre del Medicamento *</label>
                <input type="text" name="nombre_medicamento" id="medicamento-nombre_medicamento" placeholder="Nombre comercial" required>
            </div>
            
            <div class="form-group">
                <label for="medicamento-descripcion"><i class='bx bx-detail'></i> Descripción</label>
                <textarea name="descripcion" id="medicamento-descripcion" rows="2" placeholder="Descripción del medicamento"></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="medicamento-cantidad"><i class='bx bx-package'></i> Cantidad *</label>
                    <input type="number" name="cantidad" id="medicamento-cantidad" min="0" placeholder="0" required>
                </div>
                <div class="form-group">
                    <label for="medicamento-unidad"><i class='bx bx-list-ul'></i> Unidad</label>
                    <select name="unidad" id="medicamento-unidad">
                        <option value="unidades">Unidades</option>
                        <option value="tabletas">Tabletas</option>
                        <option value="cápsulas">Cápsulas</option>
                        <option value="ml">Mililitros</option>
                        <option value="mg">Miligramos</option>
                        <option value="frascos">Frascos</option>
                        <option value="ampollas">Ampollas</option>
                        <option value="tubos">Tubos</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="medicamento-precio"><i class='bx bx-dollar-circle'></i> Precio</label>
                    <input type="number" name="precio" id="medicamento-precio" step="0.01" min="0" value="0" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label for="medicamento-stock_minimo"><i class='bx bx-warning'></i> Stock Mínimo</label>
                    <input type="number" name="stock_minimo" id="medicamento-stock_minimo" min="0" value="10" placeholder="10">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="medicamento-fecha_vencimiento"><i class='bx bx-calendar'></i> Fecha de Vencimiento</label>
                    <input type="date" name="fecha_vencimiento" id="medicamento-fecha_vencimiento">
                </div>
                <div class="form-group">
                    <label for="medicamento-proveedor"><i class='bx bx-building-house'></i> Proveedor</label>
                    <input type="text" name="proveedor" id="medicamento-proveedor" placeholder="Nombre del proveedor">
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('modal-medicamento')">
                    <i class='bx bx-x'></i> Cancelar
                </button>
                <button type="submit" class="btn-primary">
                    <i class='bx bx-save'></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL VER MEDICAMENTO -->
<div class="modal" id="modal-ver-medicamento">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class='bx bx-capsule'></i> Detalles del Medicamento</h2>
            <button class="modal-close" onclick="closeModal('modal-ver-medicamento')">&times;</button>
        </div>
        <div class="modal-body" id="ver-medicamento-content">
            <!-- Contenido dinámico -->
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeModal('modal-ver-medicamento')">Cerrar</button>
        </div>
    </div>
</div>
