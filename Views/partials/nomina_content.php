<?php
$empleados = $empleados ?? [];
$search    = $search    ?? '';
?>

<div class="head-title">
    <div class="left">
        <h1>Nómina de Empleados</h1>
        <ul class="breadcrumb">
            <li><a href="index.php?module=dashboard">Inicio</a></li>
            <li><i class='bx bx-chevron-right'></i></li>
            <li><a class="active" href="#">Nómina</a></li>
        </ul>
    </div>
    <div class="head-actions">
        <button class="btn-export" onclick="window.location.href='index.php?module=nomina&action=exportar'">
            <i class='bx bx-export'></i> Exportar
        </button>
        <button class="btn-primary" onclick="openModal('modal-empleado')">
            <i class='bx bx-plus'></i> Nuevo Empleado
        </button>
    </div>
</div>

<div class="table-container">
    <div class="table-header">
        <h3><i class='bx bx-group'></i> Listado de Empleados</h3>
        <div class="table-stats">
            <?php 
            $activos = 0;
            $totalSalarios = 0;
            foreach ($empleados as $e) {
                if ($e->estado === 'activo') $activos++;
                $totalSalarios += $e->salario;
            }
            ?>
            <span class="badge badge-info"><i class='bx bx-user'></i> <?php echo count($empleados); ?> empleados</span>
            <span class="badge badge-success"><i class='bx bx-check-circle'></i> <?php echo $activos; ?> activos</span>
        </div>
    </div>
    <div class="table-responsive">
        <table class="data-table" id="nomina-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre Completo</th>
                    <th>Cédula</th>
                    <th>Cargo</th>
                    <th>Departamento</th>
                    <th>Salario</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($empleados)): ?>
                    <?php $i = 1; foreach ($empleados as $e): ?>
                        <tr data-id="<?php echo $e->id; ?>" class="<?php echo $e->estado === 'inactivo' ? 'row-muted' : ''; ?>">
                            <td><span class="cell-num"><?php echo $i++; ?></span></td>
                            <td>
                                <div class="cell-user">
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($e->nombre . '+' . $e->apellido); ?>&background=01cfad&color=fff" alt="">
                                    <div>
                                        <span class="name"><?php echo htmlspecialchars($e->nombre . ' ' . $e->apellido); ?></span>
                                        <?php if ($e->email): ?>
                                            <small class="text-muted"><?php echo htmlspecialchars($e->email); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td><code><?php echo htmlspecialchars($e->cedula); ?></code></td>
                            <td>
                                <span class="cell-role">
                                    <i class='bx bx-briefcase'></i> <?php echo htmlspecialchars($e->cargo); ?>
                                </span>
                            </td>
                            <td>
                                <span class="cell-dept">
                                    <i class='bx bx-building'></i> <?php echo htmlspecialchars($e->departamento); ?>
                                </span>
                            </td>
                            <td><span class="cell-salary">$<?php echo number_format($e->salario, 2); ?></span></td>
                            <td>
                                <span class="status status-<?php echo $e->estado; ?>">
                                    <?php echo $e->estado === 'activo' ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </td>
                            <td class="actions">
                                <button class="btn-icon btn-view" onclick="viewEmpleado(<?php echo $e->id; ?>)" title="Ver detalles">
                                    <i class='bx bx-show'></i>
                                </button>
                                <button class="btn-icon btn-edit" onclick="editEmpleado(<?php echo $e->id; ?>)" title="Editar">
                                    <i class='bx bx-edit'></i>
                                </button>
                                <button class="btn-icon btn-delete" onclick="confirmDeleteEmpleado(<?php echo $e->id; ?>, '<?php echo htmlspecialchars($e->nombre . ' ' . $e->apellido); ?>')" title="Eliminar">
                                    <i class='bx bx-trash'></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="empty-row">
                        <div class="empty-state">
                            <i class='bx bx-group'></i>
                            <p>No hay empleados registrados</p>
                            <button class="btn-primary btn-sm" onclick="openModal('modal-empleado')">Agregar Empleado</button>
                        </div>
                    </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL EMPLEADO -->
<div class="modal" id="modal-empleado">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modal-empleado-title"><i class='bx bx-user-plus'></i> Nuevo Empleado</h2>
            <button class="modal-close" onclick="closeModal('modal-empleado')">&times;</button>
        </div>
        <form id="form-empleado" class="modal-form">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="id" id="empleado-id">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="empleado-nombre"><i class='bx bx-user'></i> Nombre *</label>
                    <input type="text" name="nombre" id="empleado-nombre" placeholder="Nombre" required>
                </div>
                <div class="form-group">
                    <label for="empleado-apellido"><i class='bx bx-user'></i> Apellido *</label>
                    <input type="text" name="apellido" id="empleado-apellido" placeholder="Apellido" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="empleado-cedula"><i class='bx bx-id-card'></i> Cédula *</label>
                    <input type="text" name="cedula" id="empleado-cedula" placeholder="000-0000000-0" required>
                </div>
                <div class="form-group">
                    <label for="empleado-telefono"><i class='bx bx-phone'></i> Teléfono</label>
                    <input type="tel" name="telefono" id="empleado-telefono" placeholder="809-000-0000">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="empleado-cargo"><i class='bx bx-briefcase'></i> Cargo *</label>
                    <select name="cargo" id="empleado-cargo" required>
                        <option value="">Seleccionar cargo</option>
                        <optgroup label="Dirección y Administración">
                            <option value="Director General">Director General</option>
                            <option value="Director Médico">Director Médico</option>
                            <option value="Director Administrativo">Director Administrativo</option>
                            <option value="Administrador">Administrador</option>
                            <option value="Subdirector">Subdirector</option>
                        </optgroup>
                        <optgroup label="Médicos Especialistas">
                            <option value="Médico General">Médico General</option>
                            <option value="Médico Internista">Médico Internista</option>
                            <option value="Cirujano General">Cirujano General</option>
                            <option value="Pediatra">Pediatra</option>
                            <option value="Ginecólogo">Ginecólogo</option>
                            <option value="Obstetra">Obstetra</option>
                            <option value="Cardiólogo">Cardiólogo</option>
                            <option value="Neurólogo">Neurólogo</option>
                            <option value="Traumatólogo">Traumatólogo</option>
                            <option value="Oftalmólogo">Oftalmólogo</option>
                            <option value="Otorrinolaringólogo">Otorrinolaringólogo</option>
                            <option value="Dermatólogo">Dermatólogo</option>
                            <option value="Urólogo">Urólogo</option>
                            <option value="Psiquiatra">Psiquiatra</option>
                            <option value="Anestesiólogo">Anestesiólogo</option>
                            <option value="Radiólogo">Radiólogo</option>
                            <option value="Patólogo">Patólogo</option>
                            <option value="Oncólogo">Oncólogo</option>
                            <option value="Endocrinólogo">Endocrinólogo</option>
                            <option value="Gastroenterólogo">Gastroenterólogo</option>
                            <option value="Nefrólogo">Nefrólogo</option>
                            <option value="Neumólogo">Neumólogo</option>
                            <option value="Reumatólogo">Reumatólogo</option>
                            <option value="Hematólogo">Hematólogo</option>
                            <option value="Infectólogo">Infectólogo</option>
                        </optgroup>
                        <optgroup label="Enfermería">
                            <option value="Jefe de Enfermería">Jefe de Enfermería</option>
                            <option value="Supervisor de Enfermería">Supervisor de Enfermería</option>
                            <option value="Enfermera Licenciada">Enfermera Licenciada</option>
                            <option value="Enfermera Auxiliar">Enfermera Auxiliar</option>
                            <option value="Enfermera de Quirófano">Enfermera de Quirófano</option>
                            <option value="Enfermera de UCI">Enfermera de UCI</option>
                            <option value="Enfermera de Emergencias">Enfermera de Emergencias</option>
                            <option value="Enfermera Pediátrica">Enfermera Pediátrica</option>
                        </optgroup>
                        <optgroup label="Servicios de Apoyo Clínico">
                            <option value="Técnico de Laboratorio">Técnico de Laboratorio</option>
                            <option value="Técnico Radiólogo">Técnico Radiólogo</option>
                            <option value="Técnico de Farmacia">Técnico de Farmacia</option>
                            <option value="Farmacéutico">Farmacéutico</option>
                            <option value="Fisioterapeuta">Fisioterapeuta</option>
                            <option value="Terapeuta Respiratorio">Terapeuta Respiratorio</option>
                            <option value="Nutricionista">Nutricionista</option>
                            <option value="Psicólogo Clínico">Psicólogo Clínico</option>
                            <option value="Trabajador Social">Trabajador Social</option>
                        </optgroup>
                        <optgroup label="Servicios Generales">
                            <option value="Recepcionista">Recepcionista</option>
                            <option value="Secretaria">Secretaria</option>
                            <option value="Asistente Administrativo">Asistente Administrativo</option>
                            <option value="Contador">Contador</option>
                            <option value="Auxiliar Contable">Auxiliar Contable</option>
                            <option value="Recursos Humanos">Recursos Humanos</option>
                            <option value="Compras">Compras</option>
                            <option value="Almacenista">Almacenista</option>
                        </optgroup>
                        <optgroup label="Servicios de Apoyo">
                            <option value="Camillero">Camillero</option>
                            <option value="Personal de Limpieza">Personal de Limpieza</option>
                            <option value="Personal de Mantenimiento">Personal de Mantenimiento</option>
                            <option value="Técnico de Mantenimiento">Técnico de Mantenimiento</option>
                            <option value="Electricista">Electricista</option>
                            <option value="Plomero">Plomero</option>
                            <option value="Personal de Seguridad">Personal de Seguridad</option>
                            <option value="Vigilante">Vigilante</option>
                            <option value="Conductor">Conductor</option>
                            <option value="Cocinero">Cocinero</option>
                            <option value="Auxiliar de Cocina">Auxiliar de Cocina</option>
                            <option value="Personal de Lavandería">Personal de Lavandería</option>
                        </optgroup>
                        <optgroup label="Tecnología">
                            <option value="Jefe de Sistemas">Jefe de Sistemas</option>
                            <option value="Técnico de Sistemas">Técnico de Sistemas</option>
                            <option value="Soporte Técnico">Soporte Técnico</option>
                            <option value="Ingeniero Biomédico">Ingeniero Biomédico</option>
                        </optgroup>
                    </select>
                </div>
                <div class="form-group">
                    <label for="empleado-departamento"><i class='bx bx-building'></i> Departamento *</label>
                    <select name="departamento" id="empleado-departamento" required>
                        <option value="">Seleccionar</option>
                        <option value="Administración">Administración</option>
                        <option value="Consultas">Consultas</option>
                        <option value="Enfermería">Enfermería</option>
                        <option value="Laboratorio">Laboratorio</option>
                        <option value="Farmacia">Farmacia</option>
                        <option value="Recursos Humanos">Recursos Humanos</option>
                        <option value="Mantenimiento">Mantenimiento</option>
                        <option value="Seguridad">Seguridad</option>
                        <option value="Emergencias">Emergencias</option>
                        <option value="Quirófano">Quirófano</option>
                        <option value="UCI">UCI</option>
                        <option value="Radiología">Radiología</option>
                        <option value="Fisioterapia">Fisioterapia</option>
                        <option value="Nutrición">Nutrición</option>
                        <option value="Trabajo Social">Trabajo Social</option>
                        <option value="Sistemas">Sistemas</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="empleado-salario"><i class='bx bx-dollar-circle'></i> Salario</label>
                    <input type="number" name="salario" id="empleado-salario" step="0.01" min="0" value="0" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label for="empleado-fecha_ingreso"><i class='bx bx-calendar'></i> Fecha de Ingreso *</label>
                    <input type="date" name="fecha_ingreso" id="empleado-fecha_ingreso" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="empleado-email"><i class='bx bx-envelope'></i> Email</label>
                    <input type="email" name="email" id="empleado-email" placeholder="correo@ejemplo.com">
                </div>
                <div class="form-group">
                    <label for="empleado-estado"><i class='bx bx-info-circle'></i> Estado</label>
                    <select name="estado" id="empleado-estado">
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('modal-empleado')">
                    <i class='bx bx-x'></i> Cancelar
                </button>
                <button type="submit" class="btn-primary">
                    <i class='bx bx-save'></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL VER EMPLEADO -->
<div class="modal" id="modal-ver-empleado">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class='bx bx-user-detail'></i> Detalles del Empleado</h2>
            <button class="modal-close" onclick="closeModal('modal-ver-empleado')">&times;</button>
        </div>
        <div class="modal-body" id="ver-empleado-content">
            <!-- Contenido dinámico -->
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeModal('modal-ver-empleado')">Cerrar</button>
        </div>
    </div>
</div>
