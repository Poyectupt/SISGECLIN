# SISGECLIN v2.0 — Documentación Técnica

**Sistema de Gestión Clínica**  
Versión: 2.0 | Fecha: Mayo 2026  
Plataforma: PHP 8+ / MySQL / XAMPP

---

## Tabla de Contenidos

1. [Descripción General](#1-descripción-general)
2. [Requisitos del Sistema](#2-requisitos-del-sistema)
3. [Instalación](#3-instalación)
4. [Arquitectura del Sistema](#4-arquitectura-del-sistema)
5. [Estructura de Archivos](#5-estructura-de-archivos)
6. [Base de Datos](#6-base-de-datos)
7. [Módulos del Sistema](#7-módulos-del-sistema)
8. [Rutas (Routing)](#8-rutas-routing)
9. [Modelos](#9-modelos)
10. [Controladores](#10-controladores)
11. [Vistas](#11-vistas)
12. [Seguridad](#12-seguridad)
13. [JavaScript y AJAX](#13-javascript-y-ajax)
14. [Estilos CSS](#14-estilos-css)
15. [Roles y Permisos](#15-roles-y-permisos)
16. [Exportación CSV](#16-exportación-csv)
17. [Paginación](#17-paginación)

---

## 1. Descripción General

SISGECLIN es un sistema web de gestión clínica desarrollado en PHP puro con patrón MVC (Modelo-Vista-Controlador). Permite administrar pacientes, consultas médicas, inventario de medicamentos, nómina de empleados, mensajería interna y usuarios del sistema.

**Tecnologías utilizadas:**

| Componente | Tecnología |
|---|---|
| Backend | PHP 8.0+ |
| Base de datos | MySQL 5.7+ / MariaDB |
| Frontend | HTML5, CSS3, JavaScript (Vanilla) |
| Servidor | Apache (XAMPP) |
| Iconos | Boxicons 2.1.4 |
| Avatares | UI Avatars API |

---

## 2. Requisitos del Sistema

- XAMPP 8.0 o superior (Apache + MySQL + PHP)
- PHP 8.0 o superior
- MySQL 5.7 o MariaDB 10.4+
- Navegador moderno (Chrome, Firefox, Edge)
- Conexión a internet (solo para cargar iconos Boxicons y avatares)

---

## 3. Instalación

### Paso 1 — Copiar archivos
```
Copiar la carpeta SISGECLIN a:
C:\xampp\htdocs\SISGECLIN\
```

### Paso 2 — Crear la base de datos
```sql
-- En phpMyAdmin o MySQL Workbench:
1. Crear base de datos con nombre: sisgeclin
2. Importar el archivo: database/sisgeclin_completo.sql
```

### Paso 3 — Verificar configuración
```php
// Archivo: config/database.php
return [
    'host'     => 'localhost',
    'database' => 'sisgeclin',
    'user'     => 'root',
    'password' => '',        // Vacío en XAMPP por defecto
    'charset'  => 'utf8mb4'
];
```

### Paso 4 — Iniciar XAMPP
```
1. Abrir XAMPP Control Panel
2. Iniciar Apache
3. Iniciar MySQL
```

### Paso 5 — Acceder al sistema
```
URL: http://localhost/SISGECLIN/
Usuario: admin
Contraseña: Admin123
```

### Paso 6 — Si la contraseña no funciona
```
Abrir: http://localhost/SISGECLIN/diagnostico_password.php
Click en "Actualizar Todas las Contraseñas"
```

---

## 4. Arquitectura del Sistema

El sistema sigue el patrón **MVC (Modelo-Vista-Controlador)**:

```
Solicitud HTTP
      │
      ▼
  index.php  ──── Router (switch/case por módulo)
      │
      ▼
 Controller  ──── Procesa la lógica de negocio
      │
      ├──── Model  ──── Accede a la base de datos (PDO)
      │
      └──── View   ──── Renderiza el HTML al usuario
```

### Flujo de una petición típica

```
Usuario hace click en "Eliminar Paciente"
      │
      ▼
JavaScript (script.js) → fetch POST a index.php?module=pacientes&action=delete&id=5
      │
      ▼
index.php → PacientesController::delete(5)
      │
      ▼
Paciente::delete(5) → DELETE FROM pacientes WHERE id = 5
      │
      ▼
JSON response → { success: true, message: "Paciente eliminado" }
      │
      ▼
JavaScript muestra toast y recarga la página
```

---

## 5. Estructura de Archivos

```
SISGECLIN/
│
├── index.php                    # Punto de entrada único (Front Controller)
│
├── config/
│   └── database.php             # Configuración de conexión a BD
│
├── Core/                        # Clases base del framework
│   ├── Controller.php           # Clase base para controladores
│   ├── Database.php             # Conexión PDO y métodos de consulta
│   ├── Security.php             # CSRF, hash, sanitización
│   └── Session.php              # Manejo de sesiones
│
├── Models/                      # Modelos (acceso a datos)
│   ├── Bitacora.php             # Registro de auditoría
│   ├── Consulta.php             # Consultas médicas
│   ├── Empleado.php             # Empleados / Nómina
│   ├── InventarioMovimiento.php # Movimientos de inventario
│   ├── Medicamento.php          # Medicamentos / Inventario
│   ├── Mensaje.php              # Mensajería interna
│   ├── Paciente.php             # Pacientes
│   └── User.php                 # Usuarios del sistema
│
├── Controllers/                 # Controladores
│   ├── AuthController.php       # Login / Logout
│   ├── ConsultasController.php  # Gestión de consultas
│   ├── DashboardController.php  # Panel de control
│   ├── InventarioMovimientosController.php
│   ├── MedicinaController.php   # Medicamentos
│   ├── MensajesController.php   # Mensajería
│   ├── NominaController.php     # Nómina de empleados
│   ├── PacientesController.php  # Pacientes
│   ├── PerfilController.php     # Perfil de usuario
│   └── UsuariosController.php   # Gestión de usuarios
│
├── Views/                       # Vistas
│   ├── dashboard.php            # Layout principal (sidebar + navbar)
│   ├── login.php                # Pantalla de login
│   └── partials/                # Contenido de cada módulo
│       ├── consultas_content.php
│       ├── dashboard_content.php
│       ├── inventario_movimientos_content.php
│       ├── medicina_content.php
│       ├── mensajes_content.php
│       ├── nomina_content.php
│       ├── pacientes_content.php
│       ├── pagination.php       # Componente de paginación
│       ├── perfil_content.php
│       └── usuarios_content.php
│
├── CSS/
│   ├── style.css                # Estilos principales
│   ├── styleS.css               # Estilos del dashboard
│   ├── login.css                # Estilos del login
│   ├── pagination.css           # Estilos de paginación y botones
│   ├── Normalize.css            # Reset CSS
│   └── script.js                # JavaScript principal
│
├── Img/
│   ├── image-Photoroom.ico      # Favicon
│   └── image-Photoroom.png      # Logo
│
├── database/
│   ├── sisgeclin_completo.sql   # Base de datos completa (usar este)
│   ├── sisgeclin.sql            # Base de datos original
│   └── actualizacion_sistema.sql
│
├── Helpers/
│   └── Captcha.php              # Helper de captcha
│
└── vendor/                      # Dependencias (si aplica)
```

---

## 6. Base de Datos

**Nombre:** `sisgeclin`  
**Archivo:** `database/sisgeclin_completo.sql`

### Tablas principales

#### `usuarios`
| Campo | Tipo | Descripción |
|---|---|---|
| id | INT AUTO_INCREMENT | Clave primaria |
| username | VARCHAR(50) | Nombre de usuario único |
| email | VARCHAR(100) | Email único |
| password | VARCHAR(255) | Hash bcrypt |
| nombre_completo | VARCHAR(100) | Nombre completo |
| rol | ENUM | admin, medico, recepcionista |
| activo | TINYINT(1) | 1=activo, 0=inactivo |
| created_at | TIMESTAMP | Fecha de creación |

#### `pacientes`
| Campo | Tipo | Descripción |
|---|---|---|
| id | INT AUTO_INCREMENT | Clave primaria |
| nombre | VARCHAR(100) | Nombre |
| apellido | VARCHAR(100) | Apellido |
| cedula | VARCHAR(20) UNIQUE | Cédula de identidad |
| telefono | VARCHAR(20) | Teléfono |
| email | VARCHAR(100) | Email |
| fecha_nacimiento | DATE | Fecha de nacimiento |
| genero | ENUM | M, F, Otro |
| tipo_sangre | VARCHAR(5) | O+, A-, etc. |
| direccion | TEXT | Dirección |
| alergias | TEXT | Alergias conocidas |
| created_at | TIMESTAMP | Fecha de registro |

#### `consultas`
| Campo | Tipo | Descripción |
|---|---|---|
| id | INT AUTO_INCREMENT | Clave primaria |
| paciente_id | INT FK | Referencia a pacientes |
| medico_id | INT FK | Referencia a usuarios |
| fecha_hora | DATETIME | Fecha y hora de consulta |
| motivo | TEXT | Motivo de la consulta |
| diagnostico | TEXT | Diagnóstico médico |
| tratamiento | TEXT | Tratamiento indicado |
| estado | ENUM | pendiente, en_proceso, completada, cancelada |
| created_at | TIMESTAMP | Fecha de registro |

#### `inventario`
| Campo | Tipo | Descripción |
|---|---|---|
| id | INT AUTO_INCREMENT | Clave primaria |
| nombre_medicamento | VARCHAR(200) | Nombre del medicamento |
| descripcion | TEXT | Descripción |
| cantidad | INT | Stock actual |
| unidad | VARCHAR(50) | unidades, cajas, etc. |
| precio | DECIMAL(10,2) | Precio unitario |
| fecha_vencimiento | DATE | Fecha de vencimiento |
| proveedor | VARCHAR(200) | Proveedor |
| stock_minimo | INT | Alerta cuando cantidad <= este valor |
| created_at | TIMESTAMP | Fecha de registro |

#### `inventario_movimientos`
| Campo | Tipo | Descripción |
|---|---|---|
| id | INT AUTO_INCREMENT | Clave primaria |
| medicamento_id | INT FK | Referencia a inventario |
| tipo_movimiento | ENUM | entrada, salida |
| cantidad | INT | Cantidad del movimiento |
| cantidad_anterior | INT | Stock antes del movimiento |
| cantidad_nueva | INT | Stock después del movimiento |
| motivo | TEXT | Razón del movimiento |
| usuario_id | INT FK | Quién registró el movimiento |
| consulta_id | INT FK | Consulta relacionada (opcional) |
| created_at | TIMESTAMP | Fecha del movimiento |

#### `nomina`
| Campo | Tipo | Descripción |
|---|---|---|
| id | INT AUTO_INCREMENT | Clave primaria |
| nombre | VARCHAR(100) | Nombre |
| apellido | VARCHAR(100) | Apellido |
| cedula | VARCHAR(20) UNIQUE | Cédula |
| cargo | VARCHAR(100) | Cargo hospitalario |
| departamento | VARCHAR(100) | Departamento |
| salario | DECIMAL(10,2) | Salario mensual |
| fecha_ingreso | DATE | Fecha de ingreso |
| telefono | VARCHAR(20) | Teléfono |
| email | VARCHAR(100) | Email |
| estado | ENUM | activo, inactivo |
| created_at | TIMESTAMP | Fecha de registro |

#### `mensajes`
| Campo | Tipo | Descripción |
|---|---|---|
| id | INT AUTO_INCREMENT | Clave primaria |
| remitente_id | INT FK | Usuario que envía |
| destinatario_id | INT FK | Usuario que recibe |
| asunto | VARCHAR(200) | Asunto del mensaje |
| contenido | TEXT | Cuerpo del mensaje |
| leido | TINYINT(1) | 0=no leído, 1=leído |
| created_at | TIMESTAMP | Fecha de envío |

#### `bitacora`
| Campo | Tipo | Descripción |
|---|---|---|
| id | INT AUTO_INCREMENT | Clave primaria |
| usuario_id | INT FK | Usuario que realizó la acción |
| accion | VARCHAR(100) | Tipo de acción |
| tabla | VARCHAR(100) | Tabla afectada |
| registro_id | INT | ID del registro afectado |
| detalles | TEXT | Descripción detallada |
| created_at | TIMESTAMP | Fecha de la acción |

---

## 7. Módulos del Sistema

| Módulo | URL | Acceso | Descripción |
|---|---|---|---|
| Dashboard | `?module=dashboard` | Todos | Panel con KPIs y estadísticas |
| Pacientes | `?module=pacientes` | Todos | CRUD de pacientes |
| Consultas | `?module=consultas` | Todos | CRUD de consultas médicas |
| Medicamentos | `?module=medicina` | Todos | CRUD de inventario |
| Movimientos | `?module=inventario_movimientos` | Todos | Entradas/salidas de stock |
| Mensajes | `?module=mensajes` | Todos | Mensajería interna |
| Nómina | `?module=nomina` | Solo admin | Gestión de empleados |
| Usuarios | `?module=usuarios` | Solo admin | Gestión de usuarios del sistema |
| Perfil | `?module=perfil` | Todos | Editar datos personales |

---

## 8. Rutas (Routing)

El sistema usa un único punto de entrada `index.php` con parámetros GET:

```
index.php?module={modulo}&action={accion}&id={id}
```

### Tabla de rutas completa

| Módulo | Acción | Método HTTP | Descripción |
|---|---|---|---|
| auth | login | GET/POST | Pantalla de login |
| auth | logout | GET | Cerrar sesión |
| dashboard | index | GET | Panel principal |
| pacientes | index | GET | Listar pacientes |
| pacientes | store | POST | Crear paciente |
| pacientes | update | POST | Editar paciente |
| pacientes | delete | POST | Eliminar paciente |
| pacientes | show | GET | Ver paciente (JSON) |
| pacientes | exportar | GET | Exportar CSV |
| consultas | index | GET | Listar consultas |
| consultas | store | POST | Crear consulta |
| consultas | update | POST | Editar consulta |
| consultas | delete | POST | Eliminar consulta |
| consultas | show | GET | Ver consulta (JSON) |
| consultas | exportar | GET | Exportar CSV |
| medicina | index | GET | Listar medicamentos |
| medicina | store | POST | Crear medicamento |
| medicina | update | POST | Editar medicamento |
| medicina | delete | POST | Eliminar medicamento |
| medicina | show | GET | Ver medicamento (JSON) |
| inventario_movimientos | index | GET | Listar movimientos |
| inventario_movimientos | registrarEntrada | POST | Registrar entrada |
| inventario_movimientos | registrarSalida | POST | Registrar salida |
| inventario_movimientos | exportar | GET | Exportar CSV |
| nomina | index | GET | Listar empleados |
| nomina | store | POST | Crear empleado |
| nomina | update | POST | Editar empleado |
| nomina | delete | POST | Eliminar empleado |
| nomina | exportar | GET | Exportar CSV |
| usuarios | index | GET | Listar usuarios |
| usuarios | store | POST | Crear usuario |
| usuarios | update | POST | Editar usuario |
| usuarios | delete | POST | Eliminar usuario |
| usuarios | toggle | POST | Activar/desactivar |
| mensajes | index | GET | Listar mensajes |
| mensajes | store | POST | Enviar mensaje |
| mensajes | read | POST | Marcar como leído |
| mensajes | delete | POST | Eliminar mensaje |
| perfil | index | GET | Ver perfil |
| perfil | update | POST | Actualizar perfil |
| perfil | password | POST | Cambiar contraseña |

---

## 9. Modelos

Todos los modelos usan la clase `Database` para ejecutar consultas PDO.

### Clase Database (Core/Database.php)

```php
// Métodos disponibles:
Database::fetch($sql, $params)       // Retorna un objeto (una fila)
Database::fetchAll($sql, $params)    // Retorna array de objetos
Database::query($sql, $params)       // Ejecuta INSERT/UPDATE/DELETE
Database::lastInsertId()             // Retorna el último ID insertado
```

### Métodos comunes en los modelos

| Método | Descripción |
|---|---|
| `getAll()` | Obtener todos los registros |
| `getAllPaginated($limit, $offset)` | Obtener con paginación |
| `findById($id)` | Buscar por ID |
| `create($data)` | Insertar nuevo registro |
| `update($id, $data)` | Actualizar registro |
| `delete($id)` | Eliminar registro |
| `search($term)` | Buscar por término |
| `searchPaginated($term, $limit, $offset)` | Buscar con paginación |
| `count()` | Contar total de registros |
| `countSearch($term)` | Contar resultados de búsqueda |
| `getByDateRange($inicio, $fin)` | Filtrar por rango de fechas |

### Eliminación en cascada

Los modelos `Paciente` y `Medicamento` implementan eliminación en cascada:

```php
// Paciente::delete() — elimina primero las consultas relacionadas
public function delete($id) {
    Database::query("DELETE FROM consultas WHERE paciente_id = :id", ['id' => $id]);
    return Database::query("DELETE FROM pacientes WHERE id = :id", ['id' => $id]);
}

// Medicamento::delete() — elimina movimientos y relaciones
public function delete($id) {
    Database::query("DELETE FROM inventario_movimientos WHERE medicamento_id = :id", ['id' => $id]);
    Database::query("DELETE FROM consultas_medicamentos WHERE medicamento_id = :id", ['id' => $id]);
    return Database::query("DELETE FROM inventario WHERE id = :id", ['id' => $id]);
}
```

---

## 10. Controladores

Todos los controladores extienden `App\Core\Controller` y siguen la misma estructura:

```php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Security;
use App\Models\Paciente;

class PacientesController extends Controller
{
    private Paciente $model;

    public function __construct() {
        $this->model = new Paciente();
    }

    public function index()   { /* Listar */ }
    public function store()   { /* Crear  */ }
    public function show($id) { /* Ver    */ }
    public function update($id) { /* Editar */ }
    public function delete($id) { /* Eliminar */ }
    public function exportar()  { /* CSV */ }
}
```

### Método `view()` heredado de Controller

```php
// Renderiza una vista pasando variables
$this->view('dashboard', [
    'username'   => $user['username'],
    'rol'        => $user['rol'],
    'csrf_token' => $csrfToken,
    'module'     => 'pacientes',
    'pacientes'  => $pacientes,
]);
```

### Respuestas JSON

Todas las operaciones AJAX retornan JSON:

```php
// Éxito
Security::jsonResponse(['success' => true, 'message' => 'Operación exitosa']);

// Error
Security::jsonResponse(['success' => false, 'message' => 'Error'], 500);
```

---

## 11. Vistas

### Layout principal (Views/dashboard.php)

Contiene la estructura completa de la página:
- Sidebar con menú de navegación
- Navbar superior con búsqueda, modo oscuro, notificaciones y perfil
- Área de contenido principal (incluye el partial del módulo activo)
- Modales globales (confirmación de eliminación, cambio de estado)
- Toast container para notificaciones
- Variables JavaScript globales (`CSRF_TOKEN`, `MODULE`)

### Partials (Views/partials/)

Cada módulo tiene su propio archivo partial que contiene:
- Encabezado con título y botones de acción
- Tabla de datos
- Modales de crear/editar/ver
- JavaScript específico del módulo (si aplica)

---

## 12. Seguridad

### Autenticación
- Sesiones PHP nativas
- Contraseñas hasheadas con `password_hash()` (bcrypt)
- Verificación con `password_verify()`
- Redirección automática al login si no está autenticado

### Control de acceso por rol
```php
// Solo admin puede acceder a ciertos módulos
if ($user['rol'] !== 'admin') {
    Security::redirect('index.php?module=dashboard');
}
```

### Roles disponibles
| Rol | Acceso |
|---|---|
| `admin` | Acceso completo a todos los módulos |
| `medico` | Dashboard, Pacientes, Consultas, Medicamentos, Mensajes, Perfil |
| `recepcionista` | Dashboard, Pacientes, Consultas, Mensajes, Perfil |

### CSRF Token
- Se genera en `Security::generateCSRFToken()` y se almacena en sesión
- Se incluye en todos los formularios de crear/editar
- Los métodos `delete` y `toggle` **no requieren** validación CSRF (eliminada para compatibilidad)

### Sanitización
```php
Security::sanitize($input); // htmlspecialchars + trim
```

---

## 13. JavaScript y AJAX

### Archivo principal: `CSS/script.js`

#### Función `apiRequest(url, data)`
Función central para todas las peticiones AJAX:

```javascript
async function apiRequest(url, data = {}) {
    const formData = new FormData();
    // Agrega CSRF_TOKEN automáticamente si no está en data
    if (!data.csrf_token && CSRF_TOKEN) {
        data.csrf_token = CSRF_TOKEN;
    }
    for (const key in data) {
        formData.append(key, data[key]);
    }
    const response = await fetch(url, { method: 'POST', body: formData });
    return JSON.parse(await response.text());
}
```

#### Funciones de eliminación
```javascript
confirmDeletePaciente(id, name)    // Eliminar paciente
confirmDeleteMedicamento(id)       // Eliminar medicamento
confirmDeleteConsulta(id)          // Eliminar consulta
confirmDeleteEmpleado(id, name)    // Eliminar empleado
confirmDeleteUsuario(id, name)     // Eliminar usuario
```

#### Funciones de estado
```javascript
toggleUsuarioEstado(id, accion)    // Activar/desactivar usuario
```

#### Funciones de modales
```javascript
openModal(id)       // Abrir modal por ID
closeModal(id)      // Cerrar modal por ID
closeAllModals()    // Cerrar todos los modales
```

#### Notificaciones Toast
```javascript
showToast(message, type)  // type: 'success' | 'error' | 'warning'
```

#### Modo oscuro
```javascript
initDarkMode()  // Lee localStorage y aplica el tema guardado
```

---

## 14. Estilos CSS

| Archivo | Descripción |
|---|---|
| `styleS.css` | Estilos principales del dashboard (sidebar, navbar, tablas, modales) |
| `style.css` | Estilos adicionales |
| `login.css` | Estilos de la pantalla de login |
| `pagination.css` | Estilos de paginación, botones de exportar, badges |
| `Normalize.css` | Reset CSS base |

### Variables CSS principales
```css
:root {
    --blue: #3C91E6;
    --light-blue: #CFE8FF;
    --grey: #eee;
    --dark-grey: #AAAAAA;
    --dark: #342E37;
    --red: #DB504A;
    --yellow: #FFCE26;
    --light-yellow: #FFF2C6;
    --orange: #FD7238;
    --light-orange: #FFE0D3;
}
```

---

## 15. Roles y Permisos

### Menú visible por rol

| Elemento del menú | admin | medico | recepcionista |
|---|---|---|---|
| Dashboard | ✅ | ✅ | ✅ |
| Pacientes | ✅ | ✅ | ✅ |
| Consultas | ✅ | ✅ | ✅ |
| Medicamentos | ✅ | ✅ | ✅ |
| Movimientos | ✅ | ✅ | ✅ |
| Mensajes | ✅ | ✅ | ✅ |
| Nómina | ✅ | ❌ | ❌ |
| Usuarios | ✅ | ❌ | ❌ |
| Perfil | ✅ | ✅ | ✅ |

---

## 16. Exportación CSV

Los módulos con exportación generan archivos CSV con codificación **UTF-8 con BOM** para compatibilidad con Excel.

### Módulos con exportación

| Módulo | Botón | Archivo generado |
|---|---|---|
| Pacientes | Exportar | `pacientes_YYYY-MM-DD.csv` |
| Nómina | Exportar | `nomina_YYYY-MM-DD.csv` |
| Consultas | Semanal / Mensual / Rango / Todo | `consultas_YYYY-MM-DD.csv` |
| Movimientos | Semanal / Mensual / Rango / Todo | `movimientos_YYYY-MM-DD.csv` |

### Implementación en controladores
```php
public function exportar() {
    Session::requireAuth();
    
    $datos = $this->model->getAll();
    $filename = "pacientes_" . date('Y-m-d') . ".csv";
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
    
    fputcsv($output, ['ID', 'Nombre', 'Apellido', ...]); // Encabezados
    
    foreach ($datos as $row) {
        fputcsv($output, [$row->id, $row->nombre, ...]); // Datos
    }
    
    fclose($output);
    exit;
}
```

---

## 17. Paginación

El sistema implementa paginación en los módulos: Pacientes, Medicamentos y Usuarios.

### Opciones de densidad
- 15 registros por página (por defecto)
- 30 registros por página
- 50 registros por página

### Parámetros URL
```
index.php?module=pacientes&page=2&per_page=30
```

### Componente de paginación
El archivo `Views/partials/pagination.php` recibe la variable `$pagination`:

```php
$pagination = [
    'current_page' => 1,
    'per_page'     => 15,
    'total'        => 150,
    'total_pages'  => 10
];
```

---

## Usuarios por defecto

| Usuario | Contraseña | Rol |
|---|---|---|
| admin | Admin123 | Administrador |
| medico | Admin123 | Médico |
| recepcion | Admin123 | Recepcionista |

> Si la contraseña no funciona, ejecutar: `http://localhost/SISGECLIN/diagnostico_password.php`

---

*Documentación generada para SISGECLIN v2.0 — Mayo 2026*
