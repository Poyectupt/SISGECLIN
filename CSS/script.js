// =====================================================
// SISGECLIN - JavaScript Principal
// =====================================================

// Variables globales
let CSRF_TOKEN = '';
let MODULE = '';

// =====================================================
// INICIALIZACIÓN
// =====================================================
document.addEventListener('DOMContentLoaded', () => {
    // Obtener variables globales del script inline en dashboard.php
    if (typeof window.CSRF_TOKEN !== 'undefined') {
        CSRF_TOKEN = window.CSRF_TOKEN;
    }
    if (typeof window.MODULE !== 'undefined') {
        MODULE = window.MODULE;
    }
    
    // Debug: verificar que CSRF_TOKEN está disponible
    console.log('CSRF_TOKEN disponible:', CSRF_TOKEN ? 'Sí' : 'No');
    console.log('MODULE:', MODULE);
    
    // Inicializar modo oscuro desde localStorage
    initDarkMode();
    
    // Sidebar toggle
    initSidebar();
    
    // Inicializar formularios según el módulo
    initForms();
    
    // Cerrar modales con Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeAllModals();
    });
    
    // Cerrar modales clickeando fuera
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal')) {
            closeAllModals();
        }
    });
    
    // Profile menu
    initProfileMenu();
});

// =====================================================
// MODO OSCURO
// =====================================================
function initDarkMode() {
    const switchMode = document.querySelector('.switch-mode');
    const savedMode = localStorage.getItem('darkMode');
    
    // Aplicar modo guardado
    if (savedMode === 'true') {
        document.body.classList.add('dark-mode');
        if (switchMode) switchMode.classList.add('active');
    }
    
    // Toggle del botón
    if (switchMode) {
        switchMode.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            switchMode.classList.toggle('active');
            localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
        });
    }
}

// =====================================================
// SIDEBAR
// =====================================================
function initSidebar() {
    const menuBar = document.querySelector('#content nav .bx-menu');
    const sidebar = document.getElementById('sidebar');
    
    if (menuBar && sidebar) {
        menuBar.addEventListener('click', (e) => {
            e.stopPropagation();
            sidebar.classList.toggle('show');
            document.body.classList.toggle('sidebar-open');
        });
    }
    
    // Cerrar sidebar al hacer clic fuera en móvil
    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 768 && sidebar && sidebar.classList.contains('show')) {
            if (!sidebar.contains(e.target) && !e.target.closest('.bx-menu')) {
                sidebar.classList.remove('show');
                document.body.classList.remove('sidebar-open');
            }
        }
    });
    
    // Cerrar sidebar al hacer clic en un enlace del menú
    const sidebarLinks = document.querySelectorAll('#sidebar .side-menu a');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768 && sidebar) {
                sidebar.classList.remove('show');
                document.body.classList.remove('sidebar-open');
            }
        });
    });
}

// =====================================================
// PROFILE MENU
// =====================================================
function initProfileMenu() {
    const profileWrapper = document.querySelector('.profile-wrapper');
    const profileBtn = document.querySelector('.profile');
    const profileMenu = document.querySelector('.profile-menu');
    
    if (profileBtn && profileMenu) {
        profileBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            profileMenu.classList.toggle('show');
        });
        
        // Cerrar menú al hacer clic fuera
        document.addEventListener('click', (e) => {
            if (profileWrapper && !profileWrapper.contains(e.target)) {
                profileMenu.classList.remove('show');
            }
        });
        
        // Cerrar menú al hacer clic en una opción
        const menuLinks = profileMenu.querySelectorAll('a');
        menuLinks.forEach(link => {
            link.addEventListener('click', () => {
                profileMenu.classList.remove('show');
            });
        });
    }
}

// =====================================================
// MODALES
// =====================================================
function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

function closeAllModals() {
    document.querySelectorAll('.modal.show').forEach(modal => {
        modal.classList.remove('show');
    });
    document.body.style.overflow = '';
}

// =====================================================
// TOAST NOTIFICATIONS
// =====================================================
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container') || createToastContainer();
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <i class='bx ${type === 'success' ? 'bx-check-circle' : type === 'error' ? 'bx-x-circle' : 'bx-error'}'></i>
        <span>${message}</span>
    `;
    
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'toastSlideIn 0.3s ease reverse';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container';
    document.body.appendChild(container);
    return container;
}

// =====================================================
// AJAX HELPER
// =====================================================
async function apiRequest(url, data = {}) {
    const formData = new FormData();
    
    // Asegurar que el CSRF token esté incluido
    if (!data.csrf_token && CSRF_TOKEN) {
        data.csrf_token = CSRF_TOKEN;
    }
    
    for (const key in data) {
        formData.append(key, data[key]);
    }
    
    console.log('API Request:', url, 'CSRF Token:', CSRF_TOKEN ? 'presente' : 'ausente');
    
    try {
        const response = await fetch(url, { 
            method: 'POST', 
            body: formData
        });
        
        const text = await response.text();
        console.log('API Response:', url, text.substring(0, 300));
        
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('JSON Parse Error:', e, 'Response:', text);
            return { success: false, message: 'Error en la respuesta del servidor' };
        }
    } catch (error) {
        console.error('API Error:', error);
        return { success: false, message: 'Error de conexión' };
    }
}

// =====================================================
// EXPORTAR TABLA
// =====================================================
function exportTable(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('th, td');
        const rowData = [];
        cols.forEach(col => {
            let text = col.innerText.replace(/"/g, '""');
            rowData.push('"' + text + '"');
        });
        csv.push(rowData.join(','));
    });
    
    const csvContent = csv.join('\n');
    const blob = new Blob(['\ufeff' + csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `${filename}_${new Date().toISOString().slice(0,10)}.csv`;
    link.click();
    
    showToast('Archivo exportado correctamente');
}

// =====================================================
// INICIALIZAR FORMULARIOS
// =====================================================
function initForms() {
    // Formulario Paciente
    const formPaciente = document.getElementById('form-paciente');
    if (formPaciente) {
        formPaciente.addEventListener('submit', savePaciente);
    }
    
    // Formulario Consulta
    const formConsulta = document.getElementById('form-consulta');
    if (formConsulta) {
        formConsulta.addEventListener('submit', saveConsulta);
    }
    
    // Formulario Medicamento
    const formMedicamento = document.getElementById('form-medicamento');
    if (formMedicamento) {
        formMedicamento.addEventListener('submit', saveMedicamento);
    }
    
    // Formulario Empleado
    const formEmpleado = document.getElementById('form-empleado');
    if (formEmpleado) {
        formEmpleado.addEventListener('submit', saveEmpleado);
    }
    
    // Formulario Mensaje
    const formMensaje = document.getElementById('form-mensaje');
    if (formMensaje) {
        formMensaje.addEventListener('submit', sendMensaje);
    }
    
    // Formulario Usuario
    const formUsuario = document.getElementById('form-usuario');
    if (formUsuario) {
        formUsuario.addEventListener('submit', saveUsuario);
    }
    
    // Formulario Perfil
    const formPerfil = document.getElementById('form-perfil');
    if (formPerfil) {
        formPerfil.addEventListener('submit', updatePerfil);
    }
    
    // Formulario Password
    const formPassword = document.getElementById('form-password');
    if (formPassword) {
        formPassword.addEventListener('submit', cambiarPassword);
    }
}

// =====================================================
// PACIENTES CRUD
// =====================================================
async function savePaciente(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const id = formData.get('id');
    
    const action = id ? `update&id=${id}` : 'store';
    const result = await apiRequest(`index.php?module=pacientes&action=${action}`, Object.fromEntries(formData));
    
    if (result.success) {
        showToast(result.message);
        closeAllModals();
        setTimeout(() => location.reload(), 1500);
    } else {
        showToast(result.message, 'error');
    }
}

async function loadAndShowPaciente(id) {
    const result = await apiRequest(`index.php?module=pacientes&action=show&id=${id}`);
    if (result.success && result.data) {
        const d = result.data;
        const content = document.getElementById('ver-paciente-content');
        if (content) {
            content.innerHTML = `
                <div class="detail-grid">
                    <div class="detail-item"><strong>Nombre:</strong> ${d.nombre} ${d.apellido}</div>
                    <div class="detail-item"><strong>Cédula:</strong> ${d.cedula}</div>
                    <div class="detail-item"><strong>Teléfono:</strong> ${d.telefono || '-'}</div>
                    <div class="detail-item"><strong>Email:</strong> ${d.email || '-'}</div>
                    <div class="detail-item"><strong>Fecha Nacimiento:</strong> ${d.fecha_nacimiento || '-'}</div>
                    <div class="detail-item"><strong>Género:</strong> ${d.genero === 'M' ? 'Masculino' : d.genero === 'F' ? 'Femenino' : 'Otro'}</div>
                    <div class="detail-item"><strong>Tipo Sangre:</strong> ${d.tipo_sangre || '-'}</div>
                    <div class="detail-item"><strong>Dirección:</strong> ${d.direccion || '-'}</div>
                    <div class="detail-item"><strong>Alergias:</strong> ${d.alergias || '-'}</div>
                </div>
            `;
        }
        openModal('modal-ver-paciente');
    }
}

async function loadAndEditPaciente(id) {
    const result = await apiRequest(`index.php?module=pacientes&action=show&id=${id}`);
    if (result.success && result.data) {
        const d = result.data;
        document.getElementById('modal-paciente-title').innerHTML = '<i class="bx bx-edit"></i> Editar Paciente';
        document.getElementById('paciente-id').value = d.id;
        document.getElementById('paciente-nombre').value = d.nombre || '';
        document.getElementById('paciente-apellido').value = d.apellido || '';
        document.getElementById('paciente-cedula').value = d.cedula || '';
        document.getElementById('paciente-telefono').value = d.telefono || '';
        document.getElementById('paciente-email').value = d.email || '';
        document.getElementById('paciente-fecha_nacimiento').value = d.fecha_nacimiento || '';
        document.getElementById('paciente-genero').value = d.genero || 'M';
        document.getElementById('paciente-tipo_sangre').value = d.tipo_sangre || '';
        document.getElementById('paciente-direccion').value = d.direccion || '';
        document.getElementById('paciente-alergias').value = d.alergias || '';
        openModal('modal-paciente');
    }
}

function confirmDeletePaciente(id, name) {
    const itemName = document.getElementById('delete-item-name');
    if (itemName) itemName.textContent = name;
    
    openModal('modal-confirm-delete');
    
    const btnConfirm = document.getElementById('btn-confirm-delete');
    if (btnConfirm) {
        // Remover eventos anteriores clonando el botón
        const newBtn = btnConfirm.cloneNode(true);
        btnConfirm.parentNode.replaceChild(newBtn, btnConfirm);
        
        newBtn.addEventListener('click', async function() {
            newBtn.disabled = true;
            newBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Eliminando...';
            
            const result = await apiRequest(`index.php?module=pacientes&action=delete&id=${id}`, {
                csrf_token: CSRF_TOKEN
            });
            
            if (result.success) {
                showToast(result.message);
                closeAllModals();
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(result.message, 'error');
                newBtn.disabled = false;
                newBtn.innerHTML = '<i class="bx bx-trash"></i> Eliminar';
            }
        });
    }
}

// =====================================================
// CONSULTAS CRUD
// =====================================================
async function saveConsulta(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const id = formData.get('id');
    
    const action = id ? `update&id=${id}` : 'store';
    const result = await apiRequest(`index.php?module=consultas&action=${action}`, Object.fromEntries(formData));
    
    if (result.success) {
        showToast(result.message);
        closeAllModals();
        setTimeout(() => location.reload(), 1500);
    } else {
        showToast(result.message, 'error');
    }
}

async function viewConsulta(id) {
    const result = await apiRequest(`index.php?module=consultas&action=show&id=${id}`);
    if (result.success && result.data) {
        const d = result.data;
        const content = document.getElementById('ver-consulta-content');
        if (content) {
            content.innerHTML = `
                <div class="detail-grid">
                    <div class="detail-item"><strong>Paciente:</strong> ${d.nombre} ${d.apellido}</div>
                    <div class="detail-item"><strong>Cédula:</strong> ${d.cedula}</div>
                    <div class="detail-item"><strong>Fecha/Hora:</strong> ${d.fecha_hora}</div>
                    <div class="detail-item"><strong>Estado:</strong> <span class="status status-${d.estado}">${d.estado}</span></div>
                </div>
                <div class="detail-section" style="margin-top: 16px;">
                    <h4>Motivo</h4>
                    <p>${d.motivo}</p>
                </div>
                ${d.diagnostico ? `<div class="detail-section"><h4>Diagnóstico</h4><p>${d.diagnostico}</p></div>` : ''}
                ${d.tratamiento ? `<div class="detail-section"><h4>Tratamiento</h4><p>${d.tratamiento}</p></div>` : ''}
            `;
        }
        openModal('modal-ver-consulta');
    }
}

async function editConsulta(id) {
    const result = await apiRequest(`index.php?module=consultas&action=show&id=${id}`);
    if (result.success && result.data) {
        const d = result.data;
        document.getElementById('modal-consulta-title').innerHTML = '<i class="bx bx-edit"></i> Editar Consulta';
        document.getElementById('consulta-id').value = d.id;
        document.getElementById('consulta-paciente_id').value = d.paciente_id;
        document.getElementById('consulta-empleado_id').value = d.empleado_id;
        document.getElementById('consulta-fecha_hora').value = d.fecha_hora ? d.fecha_hora.replace(' ', 'T').substring(0, 16) : '';
        document.getElementById('consulta-motivo').value = d.motivo || '';
        document.getElementById('consulta-diagnostico').value = d.diagnostico || '';
        document.getElementById('consulta-tratamiento').value = d.tratamiento || '';
        document.getElementById('consulta-estado').value = d.estado || 'pendiente';
        openModal('modal-consulta');
    }
}

function confirmDeleteConsulta(id) {
    const itemName = document.getElementById('delete-item-name');
    if (itemName) itemName.textContent = 'Consulta #' + id;
    
    openModal('modal-confirm-delete');
    
    const btnConfirm = document.getElementById('btn-confirm-delete');
    if (btnConfirm) {
        const newBtn = btnConfirm.cloneNode(true);
        btnConfirm.parentNode.replaceChild(newBtn, btnConfirm);
        
        newBtn.addEventListener('click', async function() {
            newBtn.disabled = true;
            newBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Eliminando...';
            
            const result = await apiRequest(`index.php?module=consultas&action=delete&id=${id}`, {
                csrf_token: CSRF_TOKEN
            });
            
            if (result.success) {
                showToast(result.message);
                closeAllModals();
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(result.message, 'error');
                newBtn.disabled = false;
                newBtn.innerHTML = '<i class="bx bx-trash"></i> Eliminar';
            }
        });
    }
}

// =====================================================
// MEDICAMENTOS CRUD
// =====================================================
async function saveMedicamento(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const id = formData.get('id');
    
    const action = id ? `update&id=${id}` : 'store';
    const result = await apiRequest(`index.php?module=medicina&action=${action}`, Object.fromEntries(formData));
    
    if (result.success) {
        showToast(result.message);
        closeAllModals();
        setTimeout(() => location.reload(), 1500);
    } else {
        showToast(result.message, 'error');
    }
}

async function viewMedicamento(id) {
    const result = await apiRequest(`index.php?module=medicina&action=show&id=${id}`);
    if (result.success && result.data) {
        const d = result.data;
        const content = document.getElementById('ver-medicamento-content');
        if (content) {
            const isLowStock = d.cantidad <= d.stock_minimo;
            const isExpired = d.fecha_vencimiento && new Date(d.fecha_vencimiento) < new Date();
            
            content.innerHTML = `
                <div class="detail-grid">
                    <div class="detail-item">
                        <strong><i class='bx bx-capsule'></i> Nombre:</strong>
                        <span>${d.nombre_medicamento}</span>
                    </div>
                    <div class="detail-item">
                        <strong><i class='bx bx-package'></i> Cantidad:</strong>
                        <span class="${isLowStock ? 'text-danger' : ''}">${d.cantidad} ${d.unidad}</span>
                    </div>
                    <div class="detail-item">
                        <strong><i class='bx bx-dollar'></i> Precio:</strong>
                        <span class="text-success">$${parseFloat(d.precio).toFixed(2)}</span>
                    </div>
                    <div class="detail-item">
                        <strong><i class='bx bx-calendar'></i> Vencimiento:</strong>
                        <span class="${isExpired ? 'text-danger' : ''}">${d.fecha_vencimiento ? new Date(d.fecha_vencimiento).toLocaleDateString('es-DO') : 'N/A'}</span>
                    </div>
                    <div class="detail-item">
                        <strong><i class='bx bx-building'></i> Proveedor:</strong>
                        <span>${d.proveedor || '-'}</span>
                    </div>
                    <div class="detail-item">
                        <strong><i class='bx bx-warning'></i> Stock Mínimo:</strong>
                        <span>${d.stock_minimo}</span>
                    </div>
                </div>
                ${d.descripcion ? `<div class="detail-section"><h4>Descripción</h4><p>${d.descripcion}</p></div>` : ''}
            `;
        }
        openModal('modal-ver-medicamento');
    }
}

async function editMedicamento(id) {
    const result = await apiRequest(`index.php?module=medicina&action=show&id=${id}`);
    if (result.success && result.data) {
        const d = result.data;
        document.getElementById('modal-medicamento-title').innerHTML = '<i class="bx bx-edit"></i> Editar Medicamento';
        document.getElementById('medicamento-id').value = d.id;
        document.getElementById('medicamento-nombre_medicamento').value = d.nombre_medicamento || '';
        document.getElementById('medicamento-descripcion').value = d.descripcion || '';
        document.getElementById('medicamento-cantidad').value = d.cantidad || 0;
        document.getElementById('medicamento-unidad').value = d.unidad || 'unidades';
        document.getElementById('medicamento-precio').value = d.precio || 0;
        document.getElementById('medicamento-fecha_vencimiento').value = d.fecha_vencimiento || '';
        document.getElementById('medicamento-proveedor').value = d.proveedor || '';
        document.getElementById('medicamento-stock_minimo').value = d.stock_minimo || 10;
        openModal('modal-medicamento');
    }
}

function confirmDeleteMedicamento(id) {
    const itemName = document.getElementById('delete-item-name');
    if (itemName) itemName.textContent = 'Medicamento #' + id;
    
    openModal('modal-confirm-delete');
    
    const btnConfirm = document.getElementById('btn-confirm-delete');
    if (btnConfirm) {
        const newBtn = btnConfirm.cloneNode(true);
        btnConfirm.parentNode.replaceChild(newBtn, btnConfirm);
        
        newBtn.addEventListener('click', async function() {
            newBtn.disabled = true;
            newBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Eliminando...';
            
            const result = await apiRequest(`index.php?module=medicina&action=delete&id=${id}`, {
                csrf_token: CSRF_TOKEN
            });
            
            if (result.success) {
                showToast(result.message);
                closeAllModals();
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(result.message, 'error');
                newBtn.disabled = false;
                newBtn.innerHTML = '<i class="bx bx-trash"></i> Eliminar';
            }
        });
    }
}

// =====================================================
// EMPLEADOS CRUD
// =====================================================
async function saveEmpleado(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const id = formData.get('id');
    
    const action = id ? `update&id=${id}` : 'store';
    const result = await apiRequest(`index.php?module=nomina&action=${action}`, Object.fromEntries(formData));
    
    if (result.success) {
        showToast(result.message);
        closeAllModals();
        setTimeout(() => location.reload(), 1500);
    } else {
        showToast(result.message, 'error');
    }
}

async function viewEmpleado(id) {
    const result = await apiRequest(`index.php?module=nomina&action=show&id=${id}`);
    if (result.success && result.data) {
        const d = result.data;
        const content = document.getElementById('ver-empleado-content');
        if (content) {
            content.innerHTML = `
                <div class="detail-grid">
                    <div class="detail-item">
                        <strong><i class='bx bx-user'></i> Nombre:</strong>
                        <span>${d.nombre} ${d.apellido}</span>
                    </div>
                    <div class="detail-item">
                        <strong><i class='bx bx-id-card'></i> Cédula:</strong>
                        <span>${d.cedula}</span>
                    </div>
                    <div class="detail-item">
                        <strong><i class='bx bx-briefcase'></i> Cargo:</strong>
                        <span>${d.cargo}</span>
                    </div>
                    <div class="detail-item">
                        <strong><i class='bx bx-building'></i> Departamento:</strong>
                        <span>${d.departamento}</span>
                    </div>
                    <div class="detail-item">
                        <strong><i class='bx bx-dollar'></i> Salario:</strong>
                        <span class="text-success">$${parseFloat(d.salario).toFixed(2)}</span>
                    </div>
                    <div class="detail-item">
                        <strong><i class='bx bx-calendar'></i> Fecha Ingreso:</strong>
                        <span>${d.fecha_ingreso ? new Date(d.fecha_ingreso).toLocaleDateString('es-DO') : '-'}</span>
                    </div>
                    <div class="detail-item">
                        <strong><i class='bx bx-phone'></i> Teléfono:</strong>
                        <span>${d.telefono || '-'}</span>
                    </div>
                    <div class="detail-item">
                        <strong><i class='bx bx-envelope'></i> Email:</strong>
                        <span>${d.email || '-'}</span>
                    </div>
                    <div class="detail-item">
                        <strong><i class='bx bx-check-circle'></i> Estado:</strong>
                        <span class="status status-${d.estado}">${d.estado === 'activo' ? 'Activo' : 'Inactivo'}</span>
                    </div>
                </div>
            `;
        }
        openModal('modal-ver-empleado');
    }
}

async function editEmpleado(id) {
    const result = await apiRequest(`index.php?module=nomina&action=show&id=${id}`);
    if (result.success && result.data) {
        const d = result.data;
        document.getElementById('modal-empleado-title').innerHTML = '<i class="bx bx-edit"></i> Editar Empleado';
        document.getElementById('empleado-id').value = d.id;
        document.getElementById('empleado-nombre').value = d.nombre || '';
        document.getElementById('empleado-apellido').value = d.apellido || '';
        document.getElementById('empleado-cedula').value = d.cedula || '';
        document.getElementById('empleado-telefono').value = d.telefono || '';
        document.getElementById('empleado-cargo').value = d.cargo || '';
        document.getElementById('empleado-departamento').value = d.departamento || '';
        document.getElementById('empleado-salario').value = d.salario || 0;
        document.getElementById('empleado-fecha_ingreso').value = d.fecha_ingreso || '';
        document.getElementById('empleado-email').value = d.email || '';
        document.getElementById('empleado-estado').value = d.estado || 'activo';
        openModal('modal-empleado');
    }
}

function confirmDeleteEmpleado(id, name) {
    const itemName = document.getElementById('delete-item-name');
    if (itemName) itemName.textContent = name;
    
    openModal('modal-confirm-delete');
    
    const btnConfirm = document.getElementById('btn-confirm-delete');
    if (btnConfirm) {
        const newBtn = btnConfirm.cloneNode(true);
        btnConfirm.parentNode.replaceChild(newBtn, btnConfirm);
        
        newBtn.addEventListener('click', async function() {
            newBtn.disabled = true;
            newBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Eliminando...';
            
            const result = await apiRequest(`index.php?module=nomina&action=delete&id=${id}`, { csrf_token: CSRF_TOKEN });
            
            if (result.success) {
                showToast(result.message);
                closeAllModals();
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(result.message, 'error');
                newBtn.disabled = false;
                newBtn.innerHTML = '<i class="bx bx-trash"></i> Eliminar';
            }
        });
    }
}

// =====================================================
// MENSAJES
// =====================================================
async function sendMensaje(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    
    const result = await apiRequest('index.php?module=mensajes&action=store', Object.fromEntries(formData));
    
    if (result.success) {
        showToast(result.message);
        closeAllModals();
        setTimeout(() => location.reload(), 1500);
    } else {
        showToast(result.message, 'error');
    }
}

async function viewMensaje(id) {
    const result = await apiRequest(`index.php?module=mensajes&action=show&id=${id}`);
    if (result.success && result.data) {
        const d = result.data;
        document.getElementById('ver-mensaje-asunto').textContent = d.asunto;
        document.getElementById('ver-mensaje-remitente').textContent = d.remitente_nombre;
        document.getElementById('ver-mensaje-fecha').textContent = d.created_at;
        document.getElementById('ver-mensaje-contenido').textContent = d.contenido;
        document.getElementById('ver-mensaje-avatar').src = `https://ui-avatars.com/api/?name=${encodeURIComponent(d.remitente_nombre)}&background=3C91E6&color=fff`;
        openModal('modal-ver-mensaje');
    }
}

async function viewMensajeEnviado(id) {
    const result = await apiRequest(`index.php?module=mensajes&action=show&id=${id}`);
    if (result.success && result.data) {
        const d = result.data;
        document.getElementById('ver-mensaje-asunto').textContent = d.asunto;
        document.getElementById('ver-mensaje-remitente').textContent = 'Enviado a: ' + (d.destinatario_nombre || d.remitente_nombre);
        document.getElementById('ver-mensaje-fecha').textContent = d.created_at;
        document.getElementById('ver-mensaje-contenido').textContent = d.contenido;
        document.getElementById('ver-mensaje-avatar').src = `https://ui-avatars.com/api/?name=${encodeURIComponent(d.remitente_nombre)}&background=01cfad&color=fff`;
        openModal('modal-ver-mensaje');
    }
}

// =====================================================
// USUARIOS CRUD (Solo Admin)
// =====================================================
async function saveUsuario(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const id = formData.get('id');
    
    const action = id ? `update&id=${id}` : 'store';
    const result = await apiRequest(`index.php?module=usuarios&action=${action}`, Object.fromEntries(formData));
    
    if (result.success) {
        showToast(result.message);
        closeAllModals();
        setTimeout(() => location.reload(), 1500);
    } else {
        showToast(result.message, 'error');
    }
}

async function editUsuario(id) {
    const result = await apiRequest(`index.php?module=usuarios&action=show&id=${id}`);
    if (result.success && result.data) {
        const d = result.data;
        document.getElementById('modal-usuario-title').innerHTML = '<i class="bx bx-edit"></i> Editar Usuario';
        document.getElementById('usuario-id').value = d.id;
        document.getElementById('usuario-username').value = d.username || '';
        document.getElementById('usuario-email').value = d.email || '';
        document.getElementById('usuario-nombre_completo').value = d.nombre_completo || '';
        document.getElementById('usuario-password').value = '';
        document.getElementById('usuario-password').required = false;
        document.getElementById('usuario-rol').value = d.rol || 'recepcionista';
        document.getElementById('usuario-activo').value = d.activo ? '1' : '0';
        openModal('modal-usuario');
    }
}

function confirmDeleteUsuario(id, name) {
    const itemName = document.getElementById('delete-item-name');
    if (itemName) itemName.textContent = name;
    
    openModal('modal-confirm-delete');
    
    const btnConfirm = document.getElementById('btn-confirm-delete');
    if (btnConfirm) {
        const newBtn = btnConfirm.cloneNode(true);
        btnConfirm.parentNode.replaceChild(newBtn, btnConfirm);
        
        newBtn.addEventListener('click', async function() {
            newBtn.disabled = true;
            newBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Eliminando...';
            
            const result = await apiRequest(`index.php?module=usuarios&action=delete&id=${id}`, { csrf_token: CSRF_TOKEN });
            
            if (result.success) {
                showToast(result.message);
                closeAllModals();
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(result.message, 'error');
                newBtn.disabled = false;
                newBtn.innerHTML = '<i class="bx bx-trash"></i> Eliminar';
            }
        });
    }
}

async function toggleUsuarioEstado(id, accion) {
    const msg = accion === 'desactivar' 
        ? '¿Desactivar este usuario? No podrá acceder al sistema.'
        : '¿Activar este usuario? Podrá acceder al sistema nuevamente.';
    
    const estadoMsg = document.getElementById('estado-message');
    if (estadoMsg) estadoMsg.textContent = msg;
    
    openModal('modal-confirm-estado');
    
    const btnConfirm = document.getElementById('btn-confirm-estado');
    if (btnConfirm) {
        const newBtn = btnConfirm.cloneNode(true);
        btnConfirm.parentNode.replaceChild(newBtn, btnConfirm);
        
        newBtn.addEventListener('click', async function() {
            newBtn.disabled = true;
            newBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Procesando...';
            
            const result = await apiRequest(`index.php?module=usuarios&action=toggle&id=${id}`, { csrf_token: CSRF_TOKEN });
            
            if (result.success) {
                showToast(result.message);
                closeAllModals();
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(result.message, 'error');
                newBtn.disabled = false;
                newBtn.innerHTML = '<i class="bx bx-check"></i> Confirmar';
            }
        });
    }
}

// =====================================================
// PERFIL
// =====================================================
async function updatePerfil(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    
    // Agregar CSRF token si no está en el formulario
    if (!formData.has('csrf_token') && CSRF_TOKEN) {
        formData.append('csrf_token', CSRF_TOKEN);
    }
    
    const result = await apiRequest('index.php?module=perfil&action=update', Object.fromEntries(formData));
    
    if (result.success) {
        showToast(result.message);
        setTimeout(() => location.reload(), 1500);
    } else {
        showToast(result.message, 'error');
    }
}

async function cambiarPassword(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    
    // Agregar CSRF token si no está en el formulario
    if (!formData.has('csrf_token') && CSRF_TOKEN) {
        formData.append('csrf_token', CSRF_TOKEN);
    }
    
    const result = await apiRequest('index.php?module=perfil&action=password', Object.fromEntries(formData));
    
    if (result.success) {
        showToast(result.message);
        form.reset();
    } else {
        showToast(result.message, 'error');
    }
}


// =====================================================
// EXPORTAR DATOS (CSV)
// =====================================================
function exportarDatos(tipo, periodo = null) {
    let url = '';
    let filename = '';
    
    if (tipo === 'consultas') {
        url = `index.php?module=consultas&action=exportar&periodo=${periodo || 'todos'}`;
        filename = `consultas_${periodo || 'todos'}_${new Date().toISOString().slice(0,10)}.csv`;
    } else if (tipo === 'movimientos') {
        url = `index.php?module=inventario_movimientos&action=exportar&periodo=${periodo || 'todos'}`;
        filename = `movimientos_${periodo || 'todos'}_${new Date().toISOString().slice(0,10)}.csv`;
    }
    
    if (url) {
        window.location.href = url;
        showToast('Exportando datos...');
    }
}


// =====================================================
// BÚSQUEDA EN TIEMPO REAL
// =====================================================

/**
 * Buscar consultas en tiempo real
 */
function buscarConsultas(termino) {
    const tabla = document.getElementById('consultas-table');
    const filas = tabla.querySelectorAll('tbody tr');
    const clearBtn = document.getElementById('search-clear');
    let coincidencias = 0;

    // Mostrar/ocultar botón de limpiar
    if (termino.trim().length > 0) {
        clearBtn.classList.add('show');
    } else {
        clearBtn.classList.remove('show');
    }

    // Convertir término a minúsculas para búsqueda insensible a mayúsculas
    const terminoLower = termino.toLowerCase();

    filas.forEach(fila => {
        // Obtener todo el texto de la fila
        const textoFila = fila.textContent.toLowerCase();

        // Buscar en paciente, médico, motivo
        const paciente = fila.querySelector('.cell-user span')?.textContent.toLowerCase() || '';
        const medico = fila.querySelector('.cell-doctor')?.textContent.toLowerCase() || '';
        const motivo = fila.querySelectorAll('td')[4]?.textContent.toLowerCase() || '';

        // Verificar si coincide con el término de búsqueda
        if (paciente.includes(terminoLower) || 
            medico.includes(terminoLower) || 
            motivo.includes(terminoLower)) {
            fila.style.display = '';
            coincidencias++;
            // Efecto de resaltado suave
            fila.style.animation = 'fadeIn 0.3s ease';
        } else {
            fila.style.display = 'none';
        }
    });

    // Mostrar mensaje si no hay coincidencias
    if (coincidencias === 0 && terminoLower.length > 0) {
        const tbody = tabla.querySelector('tbody');
        if (!tbody.querySelector('.no-results')) {
            const noResults = document.createElement('tr');
            noResults.className = 'no-results';
            noResults.innerHTML = `
                <td colspan="7" style="text-align: center; padding: 40px; color: #999;">
                    <i class='bx bx-search-alt' style="font-size: 48px; margin-bottom: 16px; display: block; opacity: 0.5;"></i>
                    <p style="margin: 0; font-size: 16px;">No se encontraron resultados para "<strong>${termino}</strong>"</p>
                </td>
            `;
            tbody.appendChild(noResults);
        }
    } else {
        // Remover mensaje de no resultados si existe
        const noResults = tabla.querySelector('.no-results');
        if (noResults) {
            noResults.remove();
        }
    }

    // Actualizar contador
    const totalSpan = document.getElementById('total-consultas');
    if (totalSpan) {
        totalSpan.textContent = coincidencias;
    }
}

/**
 * Limpiar búsqueda
 */
function limpiarBusqueda() {
    const searchInput = document.getElementById('search-consultas');
    searchInput.value = '';
    searchInput.focus();
    buscarConsultas('');
}

// Agregar animación de fade in
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-5px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(style);

// =====================================================
// EXPORTACIÓN AVANZADA (PDF, EXCEL, CSV)
// =====================================================

/**
 * Exportar consultas en diferentes formatos
 */
function exportarConsultas(formato, periodo) {
    const url = `index.php?module=export&action=consultas&formato=${formato}&periodo=${periodo}`;
    window.location.href = url;
    showToast(`Exportando consultas a ${formato.toUpperCase()}...`);
}

/**
 * Exportar consultas con rango personalizado
 */
function exportarConsultasRango(formato) {
    let fechaInicio, fechaFin;
    
    if (formato === 'pdf') {
        fechaInicio = document.getElementById('pdf_fecha_inicio').value;
        fechaFin = document.getElementById('pdf_fecha_fin').value;
    } else if (formato === 'excel') {
        fechaInicio = document.getElementById('excel_fecha_inicio').value;
        fechaFin = document.getElementById('excel_fecha_fin').value;
    } else if (formato === 'csv') {
        fechaInicio = document.getElementById('csv_fecha_inicio').value;
        fechaFin = document.getElementById('csv_fecha_fin').value;
    }
    
    if (!fechaInicio || !fechaFin) {
        showToast('Por favor selecciona ambas fechas', 'error');
        return;
    }
    
    if (new Date(fechaInicio) > new Date(fechaFin)) {
        showToast('La fecha inicio no puede ser mayor que la fecha fin', 'error');
        return;
    }
    
    const url = `index.php?module=export&action=consultas&formato=${formato}&periodo=rango&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;
    window.location.href = url;
    showToast(`Exportando consultas a ${formato.toUpperCase()}...`);
    
    // Cerrar modales
    closeAllModals();
}

/**
 * Exportar pacientes en diferentes formatos
 */
function exportarPacientes(formato, periodo) {
    const url = `index.php?module=export&action=pacientes&formato=${formato}&periodo=${periodo}`;
    window.location.href = url;
    showToast(`Exportando pacientes a ${formato.toUpperCase()}...`);
}

/**
 * Inicializar fechas por defecto en modales de exportación
 */
document.addEventListener('DOMContentLoaded', function() {
    const hoy = new Date().toISOString().split('T')[0];
    const hace30dias = new Date(Date.now() - 30*24*60*60*1000).toISOString().split('T')[0];
    
    // PDF
    const pdfInicio = document.getElementById('pdf_fecha_inicio');
    const pdfFin = document.getElementById('pdf_fecha_fin');
    if (pdfInicio) {
        pdfInicio.value = hace30dias;
        pdfInicio.max = hoy;
    }
    if (pdfFin) {
        pdfFin.value = hoy;
        pdfFin.max = hoy;
    }
    
    // Excel
    const excelInicio = document.getElementById('excel_fecha_inicio');
    const excelFin = document.getElementById('excel_fecha_fin');
    if (excelInicio) {
        excelInicio.value = hace30dias;
        excelInicio.max = hoy;
    }
    if (excelFin) {
        excelFin.value = hoy;
        excelFin.max = hoy;
    }
    
    // CSV
    const csvInicio = document.getElementById('csv_fecha_inicio');
    const csvFin = document.getElementById('csv_fecha_fin');
    if (csvInicio) {
        csvInicio.value = hace30dias;
        csvInicio.max = hoy;
    }
    if (csvFin) {
        csvFin.value = hoy;
        csvFin.max = hoy;
    }
});


// =====================================================
// FILTROS DE MOVIMIENTOS EN TIEMPO REAL
// =====================================================
function aplicarFiltros() {
    const periodo = document.getElementById('filter-periodo')?.value || 'semanal';
    const tipo = document.getElementById('filter-tipo')?.value || '';
    
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
    const periodoSelect = document.getElementById('filter-periodo');
    const tipoSelect = document.getElementById('filter-tipo');
    
    if (periodoSelect) periodoSelect.value = 'semanal';
    if (tipoSelect) tipoSelect.value = '';
    
    window.location.href = 'index.php?module=inventario_movimientos';
}
