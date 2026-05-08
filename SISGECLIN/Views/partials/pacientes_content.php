<?php
$pacientes = $pacientes ?? [];
$search    = $search    ?? '';
?>

<div class="head-title">
    <div class="left">
        <h1>Gestión de Pacientes</h1>
        <ul class="breadcrumb">
            <li><a href="index.php?module=dashboard">Inicio</a></li>
            <li><i class='bx bx-chevron-right'></i></li>
            <li><a class="active" href="#">Pacientes</a></li>
        </ul>
    </div>
    <div class="head-actions">
        <button class="btn-export" onclick="window.location.href='index.php?module=pacientes&action=exportar'">
            <i class='bx bx-export'></i> Exportar
        </button>
        <button class="btn-primary" onclick="openModal('modal-paciente')">
            <i class='bx bx-plus'></i> Nuevo Paciente
        </button>
    </div>
</div>

<div class="table-container">
    <div class="table-header">
        <h3><i class='bx bx-user-detail'></i> Listado de Pacientes</h3>
        <div class="table-stats">
            <span class="badge badge-info"><i class='bx bx-user'></i> <?php echo count($pacientes); ?> registros</span>
        </div>
    </div>
    <div class="table-responsive">
        <table class="data-table" id="pacientes-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre Completo</th>
                    <th>Cédula</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th>Género</th>
                    <th>Tipo Sangre</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($pacientes)): ?>
                    <?php $i = 1; foreach ($pacientes as $p): ?>
                        <tr data-id="<?php echo $p->id; ?>">
                            <td><span class="cell-num"><?php echo $i++; ?></span></td>
                            <td>
                                <div class="cell-user">
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($p->nombre . '+' . $p->apellido); ?>&background=005a14&color=fff" alt="">
                                    <span><?php echo htmlspecialchars($p->nombre . ' ' . $p->apellido); ?></span>
                                </div>
                            </td>
                            <td><code><?php echo htmlspecialchars($p->cedula); ?></code></td>
                            <td><?php echo htmlspecialchars($p->telefono ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($p->email ?? '-'); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $p->genero === 'M' ? 'primary' : ($p->genero === 'F' ? 'pink' : 'secondary'); ?>">
                                    <?php echo $p->genero === 'M' ? 'Masculino' : ($p->genero === 'F' ? 'Femenino' : 'Otro'); ?>
                                </span>
                            </td>
                            <td><span class="blood-type"><?php echo htmlspecialchars($p->tipo_sangre ?? '-'); ?></span></td>
                            <td class="actions">
                                <button class="btn-icon btn-view" onclick="loadAndShowPaciente(<?php echo $p->id; ?>)" title="Ver detalles">
                                    <i class='bx bx-show'></i>
                                </button>
                                <button class="btn-icon btn-edit" onclick="loadAndEditPaciente(<?php echo $p->id; ?>)" title="Editar">
                                    <i class='bx bx-edit'></i>
                                </button>
                                <button class="btn-icon btn-delete" onclick="confirmDeletePaciente(<?php echo $p->id; ?>, '<?php echo htmlspecialchars($p->nombre . ' ' . $p->apellido); ?>')" title="Eliminar">
                                    <i class='bx bx-trash'></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="empty-row">
                        <div class="empty-state">
                            <i class='bx bx-user-x'></i>
                            <p>No hay pacientes registrados</p>
                            <button class="btn-primary btn-sm" onclick="openModal('modal-paciente')">Agregar Paciente</button>
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

<!-- MODAL PACIENTE - CREAR/EDITAR -->
<div class="modal" id="modal-paciente">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modal-paciente-title"><i class='bx bx-user-plus'></i> Nuevo Paciente</h2>
            <button class="modal-close" onclick="closeModal('modal-paciente')">&times;</button>
        </div>
        <form id="form-paciente" class="modal-form">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="id" id="paciente-id">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="paciente-nombre"><i class='bx bx-user'></i> Nombre *</label>
                    <input type="text" name="nombre" id="paciente-nombre" placeholder="Nombre del paciente" required>
                </div>
                <div class="form-group">
                    <label for="paciente-apellido"><i class='bx bx-user'></i> Apellido *</label>
                    <input type="text" name="apellido" id="paciente-apellido" placeholder="Apellido del paciente" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="paciente-cedula"><i class='bx bx-id-card'></i> Cédula *</label>
                    <input type="text" name="cedula" id="paciente-cedula" placeholder="000-0000000-0" required>
                </div>
                <div class="form-group">
                    <label for="paciente-telefono"><i class='bx bx-phone'></i> Teléfono</label>
                    <input type="tel" name="telefono" id="paciente-telefono" placeholder="809-000-0000">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="paciente-email"><i class='bx bx-envelope'></i> Email</label>
                    <input type="email" name="email" id="paciente-email" placeholder="correo@ejemplo.com">
                </div>
                <div class="form-group">
                    <label for="paciente-fecha_nacimiento"><i class='bx bx-calendar'></i> Fecha Nacimiento</label>
                    <input type="date" name="fecha_nacimiento" id="paciente-fecha_nacimiento">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="paciente-genero"><i class='bx bx-male-female'></i> Género</label>
                    <select name="genero" id="paciente-genero">
                        <option value="M">Masculino</option>
                        <option value="F">Femenino</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="paciente-tipo_sangre"><i class='bx bx-droplet'></i> Tipo Sangre</label>
                    <select name="tipo_sangre" id="paciente-tipo_sangre">
                        <option value="">Seleccionar</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="paciente-direccion"><i class='bx bx-map'></i> Dirección</label>
                <textarea name="direccion" id="paciente-direccion" rows="2" placeholder="Dirección del paciente"></textarea>
            </div>
            
            <div class="form-group">
                <label for="paciente-alergias"><i class='bx bx-error'></i> Alergias</label>
                <textarea name="alergias" id="paciente-alergias" rows="2" placeholder="Indique alergias conocidas"></textarea>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('modal-paciente')">
                    <i class='bx bx-x'></i> Cancelar
                </button>
                <button type="submit" class="btn-primary">
                    <i class='bx bx-save'></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL VER PACIENTE -->
<div class="modal" id="modal-ver-paciente">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class='bx bx-user-detail'></i> Detalles del Paciente</h2>
            <button class="modal-close" onclick="closeModal('modal-ver-paciente')">&times;</button>
        </div>
        <div class="modal-body" id="ver-paciente-content">
            <!-- Contenido dinámico -->
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeModal('modal-ver-paciente')">Cerrar</button>
        </div>
    </div>
</div>

<!-- El modal de confirmación está en dashboard.php como global -->
