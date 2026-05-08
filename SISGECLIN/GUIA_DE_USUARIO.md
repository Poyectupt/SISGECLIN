# SISGECLIN v2.0 — Guía de Usuario

**Sistema de Gestión Clínica**  
Versión: 2.0 | Mayo 2026

---

## Tabla de Contenidos

1. [Acceso al Sistema](#1-acceso-al-sistema)
2. [Panel de Control (Dashboard)](#2-panel-de-control-dashboard)
3. [Módulo de Pacientes](#3-módulo-de-pacientes)
4. [Módulo de Consultas](#4-módulo-de-consultas)
5. [Módulo de Medicamentos](#5-módulo-de-medicamentos)
6. [Módulo de Movimientos de Inventario](#6-módulo-de-movimientos-de-inventario)
7. [Módulo de Mensajes](#7-módulo-de-mensajes)
8. [Módulo de Nómina](#8-módulo-de-nómina)
9. [Módulo de Usuarios](#9-módulo-de-usuarios)
10. [Mi Perfil](#10-mi-perfil)
11. [Exportación de Datos](#11-exportación-de-datos)
12. [Funciones Generales](#12-funciones-generales)
13. [Preguntas Frecuentes](#13-preguntas-frecuentes)

---

## 1. Acceso al Sistema

### Iniciar sesión

1. Abrir el navegador y escribir: `http://localhost/SISGECLIN/`
2. Ingresar usuario y contraseña
3. Hacer click en **Iniciar Sesión**

**Credenciales por defecto:**

| Usuario | Contraseña | Rol |
|---|---|---|
| admin | Admin123 | Administrador |
| medico | Admin123 | Médico |
| recepcion | Admin123 | Recepcionista |

> **Nota:** La contraseña tiene A mayúscula: `Admin123`

### Cerrar sesión

- Hacer click en el ícono de perfil (esquina superior derecha)
- Seleccionar **Cerrar Sesión**
- O hacer click en **Cerrar Sesión** en la parte inferior del menú lateral

---

## 2. Panel de Control (Dashboard)

El dashboard es la pantalla principal que se muestra al iniciar sesión. Contiene:

### Tarjetas de estadísticas
- **Total de Pacientes** — Número total de pacientes registrados
- **Consultas Hoy** — Consultas registradas en el día actual
- **Medicamentos** — Total de medicamentos en inventario
- **Alertas de Stock** — Medicamentos con cantidad igual o menor al stock mínimo

### Alertas de stock bajo
Si hay medicamentos con stock bajo, aparece una sección de alerta en color rojo/naranja con el listado de medicamentos que necesitan reposición.

### Consultas recientes
Tabla con las 4 consultas más recientes del sistema, ordenadas de la más nueva a la más antigua.

### Modo oscuro
- Hacer click en el ícono de luna/sol en la barra superior para cambiar entre modo claro y oscuro
- El sistema recuerda la preferencia en el navegador

---

## 3. Módulo de Pacientes

**Acceso:** Menú lateral → **Pacientes**  
**Disponible para:** Todos los roles

### Ver listado de pacientes

Al entrar al módulo se muestra la tabla con todos los pacientes registrados. La tabla incluye:
- ID, Nombre completo, Cédula, Teléfono, Email, Género, Tipo de sangre
- Botones de acción: Ver, Editar, Eliminar

### Buscar pacientes

1. Usar la barra de búsqueda en la parte superior de la página
2. Escribir nombre, apellido o cédula
3. Presionar Enter o hacer click en el ícono de búsqueda
4. Los resultados se muestran con paginación

### Registrar nuevo paciente

1. Hacer click en el botón **+ Nuevo Paciente** (esquina superior derecha)
2. Completar el formulario:
   - **Nombre** *(obligatorio)*
   - **Apellido** *(obligatorio)*
   - **Cédula** *(obligatorio, debe ser única)*
   - Teléfono
   - Email
   - Fecha de Nacimiento
   - Género (Masculino / Femenino / Otro)
   - Tipo de Sangre (O+, O-, A+, A-, B+, B-, AB+, AB-)
   - Dirección
   - Alergias
3. Hacer click en **Guardar**

### Ver detalles de un paciente

1. Hacer click en el ícono de ojo 👁️ en la fila del paciente
2. Se abre un modal con todos los datos del paciente

### Editar un paciente

1. Hacer click en el ícono de lápiz ✏️ en la fila del paciente
2. Modificar los campos necesarios
3. Hacer click en **Guardar**

### Eliminar un paciente

1. Hacer click en el ícono de basura 🗑️ en la fila del paciente
2. Confirmar la eliminación en el modal que aparece
3. El paciente y sus consultas relacionadas serán eliminados

> ⚠️ **Advertencia:** La eliminación es permanente y también elimina las consultas del paciente.

### Exportar pacientes

1. Hacer click en el botón **Exportar** (esquina superior derecha)
2. Se descarga automáticamente el archivo `pacientes_YYYY-MM-DD.csv` con todos los pacientes

### Paginación

- Usar los botones **Anterior** / **Siguiente** para navegar entre páginas
- Cambiar la cantidad de registros por página con el selector: **15 / 30 / 50**

---

## 4. Módulo de Consultas

**Acceso:** Menú lateral → **Consultas**  
**Disponible para:** Todos los roles

### Ver listado de consultas

La tabla muestra: ID, Paciente, Médico, Fecha/Hora, Motivo, Estado, Acciones.

### Estados de una consulta

| Estado | Color | Descripción |
|---|---|---|
| Pendiente | Amarillo | Consulta programada, aún no atendida |
| En Proceso | Azul | Consulta en curso |
| Completada | Verde | Consulta finalizada |
| Cancelada | Rojo | Consulta cancelada |

### Registrar nueva consulta

1. Hacer click en **+ Nueva Consulta**
2. Completar el formulario:
   - **Paciente** *(obligatorio)* — Seleccionar de la lista
   - **Médico** *(obligatorio)* — Seleccionar de la lista
   - **Fecha y Hora** *(obligatorio)*
   - **Estado** — Por defecto: Pendiente
   - **Motivo** *(obligatorio)*
   - Diagnóstico *(se puede completar después)*
   - Tratamiento *(se puede completar después)*
3. Hacer click en **Guardar**

### Editar una consulta

1. Hacer click en el ícono de lápiz ✏️
2. Actualizar los campos (diagnóstico, tratamiento, estado)
3. Hacer click en **Guardar**

### Exportar consultas

Los botones de exportación están en la parte superior derecha:

| Botón | Exporta |
|---|---|
| **Exportar con Fechas** | Abre un selector para elegir rango de fechas |
| **Última Semana** | Consultas de los últimos 7 días |
| **Último Mes** | Consultas de los últimos 30 días |
| **Exportar Todo** | Todas las consultas |

**Para exportar con fechas personalizadas:**
1. Hacer click en **Exportar con Fechas**
2. Seleccionar la **Fecha Inicio**
3. Seleccionar la **Fecha Fin**
4. Hacer click en **Exportar**
5. Se descarga el archivo CSV con las consultas del período seleccionado

---

## 5. Módulo de Medicamentos

**Acceso:** Menú lateral → **Medicamentos**  
**Disponible para:** Todos los roles

### Ver listado de medicamentos

La tabla muestra: ID, Nombre, Cantidad, Unidad, Precio, Vencimiento, Proveedor, Stock Mínimo, Acciones.

Los medicamentos con **stock bajo** (cantidad ≤ stock mínimo) se resaltan en rojo.

### Registrar nuevo medicamento

1. Hacer click en **+ Nuevo Medicamento**
2. Completar el formulario:
   - **Nombre del Medicamento** *(obligatorio)*
   - Descripción
   - **Cantidad** — Stock inicial
   - Unidad (unidades, cajas, frascos, etc.)
   - Precio
   - Fecha de Vencimiento
   - Proveedor
   - **Stock Mínimo** — Cantidad mínima antes de generar alerta (por defecto: 10)
3. Hacer click en **Guardar**

### Editar un medicamento

1. Hacer click en el ícono de lápiz ✏️
2. Modificar los campos necesarios
3. Hacer click en **Guardar**

> **Nota:** Para actualizar el stock, usar el módulo de **Movimientos de Inventario** en lugar de editar directamente la cantidad.

### Eliminar un medicamento

1. Hacer click en el ícono de basura 🗑️
2. Confirmar la eliminación
3. El medicamento y sus movimientos de inventario serán eliminados

### Buscar medicamentos

Usar la barra de búsqueda para buscar por nombre o proveedor.

---

## 6. Módulo de Movimientos de Inventario

**Acceso:** Menú lateral → **Movimientos**  
**Disponible para:** Todos los roles

Este módulo registra todas las entradas y salidas de medicamentos del inventario.

### Ver movimientos

La tabla muestra: ID, Fecha/Hora, Medicamento, Tipo (Entrada/Salida), Cantidad, Stock Anterior, Stock Nuevo, Usuario, Motivo.

### Tarjetas de estadísticas

En la parte superior se muestran:
- **Total Entradas** del período seleccionado
- **Total Salidas** del período seleccionado
- **Movimientos Recientes** (cantidad visible en pantalla)

### Filtros

- **Período:** Última Semana / Último Mes
- **Tipo:** Todos / Solo Entradas / Solo Salidas

### Registrar entrada de medicamento

1. Hacer click en **+ Registrar Entrada**
2. Seleccionar el medicamento
3. Ingresar la cantidad que ingresa al inventario
4. Escribir el motivo (compra, donación, etc.)
5. Hacer click en **Registrar Entrada**
6. El stock del medicamento se actualiza automáticamente

### Registrar salida de medicamento

1. Hacer click en **- Registrar Salida**
2. Seleccionar el medicamento
3. Ingresar la cantidad que sale del inventario
4. Escribir el motivo (dispensación, vencimiento, etc.)
5. Hacer click en **Registrar Salida**
6. El stock del medicamento se actualiza automáticamente

> ⚠️ El sistema no permite registrar una salida si la cantidad supera el stock disponible.

### Exportar movimientos

| Botón | Exporta |
|---|---|
| **Exportar con Fechas** | Selector de rango de fechas + filtro por tipo |
| **Última Semana** | Movimientos de los últimos 7 días |
| **Último Mes** | Movimientos de los últimos 30 días |
| **Exportar Todo** | Todos los movimientos |

---

## 7. Módulo de Mensajes

**Acceso:** Menú lateral → **Mensajes**  
**Disponible para:** Todos los roles

### Ver mensajes

Se muestran los mensajes recibidos y enviados. Los mensajes no leídos aparecen resaltados.

### Enviar un mensaje

1. Hacer click en **+ Nuevo Mensaje**
2. Seleccionar el **Destinatario** de la lista de usuarios
3. Escribir el **Asunto**
4. Escribir el **Contenido** del mensaje
5. Hacer click en **Enviar**

### Leer un mensaje

1. Hacer click en el ícono de ojo 👁️ en el mensaje
2. El mensaje se marca automáticamente como leído
3. El contador de notificaciones en la barra superior se actualiza

### Eliminar un mensaje

1. Hacer click en el ícono de basura 🗑️
2. Confirmar la eliminación

### Notificaciones

El ícono de campana 🔔 en la barra superior muestra el número de mensajes no leídos.

---

## 8. Módulo de Nómina

**Acceso:** Menú lateral → **Nómina**  
**Disponible para:** Solo Administradores

### Ver listado de empleados

La tabla muestra: ID, Nombre, Cédula, Cargo, Departamento, Salario, Estado, Acciones.

Los empleados inactivos aparecen con fondo gris.

### Registrar nuevo empleado

1. Hacer click en **+ Nuevo Empleado**
2. Completar el formulario:
   - **Nombre** *(obligatorio)*
   - **Apellido** *(obligatorio)*
   - **Cédula** *(obligatorio, debe ser única)*
   - Teléfono
   - **Cargo** *(obligatorio)* — Seleccionar del listado de cargos hospitalarios
   - **Departamento** *(obligatorio)* — Seleccionar del listado
   - Salario
   - **Fecha de Ingreso** *(obligatorio)*
   - Email
   - Estado (Activo / Inactivo)
3. Hacer click en **Guardar**

### Cargos hospitalarios disponibles

El selector de cargos incluye más de 70 opciones organizadas en categorías:

| Categoría | Ejemplos |
|---|---|
| Dirección y Administración | Director General, Director Médico, Administrador |
| Médicos Especialistas | Médico General, Pediatra, Cardiólogo, Cirujano, Neurólogo... |
| Enfermería | Jefe de Enfermería, Enfermera Licenciada, Enfermera Auxiliar... |
| Servicios de Apoyo Clínico | Técnico de Laboratorio, Farmacéutico, Fisioterapeuta... |
| Servicios Generales | Recepcionista, Secretaria, Contador, Recursos Humanos... |
| Servicios de Apoyo | Camillero, Personal de Limpieza, Seguridad, Conductor... |
| Tecnología | Jefe de Sistemas, Técnico de Sistemas, Ingeniero Biomédico |

### Editar un empleado

1. Hacer click en el ícono de lápiz ✏️
2. Modificar los campos necesarios
3. Hacer click en **Guardar**

### Eliminar un empleado

1. Hacer click en el ícono de basura 🗑️
2. Confirmar la eliminación

### Exportar nómina

1. Hacer click en el botón **Exportar**
2. Se descarga automáticamente el archivo `nomina_YYYY-MM-DD.csv` con todos los empleados

---

## 9. Módulo de Usuarios

**Acceso:** Menú lateral → **Usuarios**  
**Disponible para:** Solo Administradores

Este módulo gestiona las cuentas de acceso al sistema.

### Ver listado de usuarios

La tabla muestra: ID, Usuario, Nombre Completo, Email, Rol, Estado, Acciones.

### Crear nuevo usuario

1. Hacer click en **+ Nuevo Usuario**
2. Completar el formulario:
   - **Usuario** *(obligatorio, único)*
   - **Email** *(obligatorio, único)*
   - **Contraseña** *(obligatorio)*
   - **Nombre Completo** *(obligatorio)*
   - **Rol:** admin / medico / recepcionista
   - Estado: Activo / Inactivo
3. Hacer click en **Guardar**

### Editar un usuario

1. Hacer click en el ícono de lápiz ✏️
2. Modificar los campos necesarios
3. Si se deja la contraseña vacía, no se cambia
4. Hacer click en **Guardar**

### Deshabilitar / Activar un usuario

1. Hacer click en el ícono de usuario con X (deshabilitar) o con check (activar)
2. Confirmar la acción en el modal
3. El usuario deshabilitado no podrá iniciar sesión

> **Nota:** Un administrador no puede deshabilitarse a sí mismo.

### Eliminar un usuario

1. Hacer click en el ícono de basura 🗑️
2. Confirmar la eliminación

> **Nota:** Un administrador no puede eliminarse a sí mismo.

---

## 10. Mi Perfil

**Acceso:** Menú lateral → **Mi Perfil** (parte inferior) o ícono de perfil en la barra superior

### Ver y editar perfil

1. Ir a **Mi Perfil**
2. Modificar los campos:
   - Nombre Completo
   - Email
3. Hacer click en **Guardar Cambios**

### Cambiar contraseña

1. Ir a **Mi Perfil**
2. En la sección **Cambiar Contraseña**:
   - Ingresar la contraseña actual
   - Ingresar la nueva contraseña
   - Confirmar la nueva contraseña
3. Hacer click en **Cambiar Contraseña**

---

## 11. Exportación de Datos

### Resumen de exportaciones disponibles

| Módulo | Tipo de exportación | Archivo |
|---|---|---|
| Pacientes | General (todos) | `pacientes_YYYY-MM-DD.csv` |
| Nómina | General (todos) | `nomina_YYYY-MM-DD.csv` |
| Consultas | Semanal, Mensual, Rango de fechas, Todo | `consultas_*.csv` |
| Movimientos | Semanal, Mensual, Rango de fechas, Todo | `movimientos_*.csv` |

### Cómo abrir un CSV en Excel correctamente

Para que los caracteres especiales (ñ, á, é, ó, ú) se vean bien:

1. Abrir **Excel**
2. Ir a la pestaña **Datos**
3. Hacer click en **Desde texto/CSV**
4. Seleccionar el archivo descargado
5. En **Origen del archivo**, seleccionar: `65001: Unicode (UTF-8)`
6. Hacer click en **Cargar**

---

## 12. Funciones Generales

### Barra de búsqueda

- Disponible en la barra superior de navegación
- Busca dentro del módulo activo
- Presionar Enter o hacer click en el ícono de lupa

### Paginación

- Disponible en módulos con muchos registros (Pacientes, Medicamentos, Usuarios)
- Selector de densidad: **15 / 30 / 50** registros por página
- Botones **Anterior** y **Siguiente** para navegar

### Modo oscuro

- Hacer click en el ícono de luna 🌙 / sol ☀️ en la barra superior
- El sistema recuerda la preferencia

### Notificaciones Toast

Las notificaciones aparecen en la esquina inferior derecha:
- 🟢 **Verde** — Operación exitosa
- 🔴 **Rojo** — Error
- 🟡 **Amarillo** — Advertencia

### Modales

- Hacer click en **X** o en el botón **Cancelar** para cerrar
- También se puede presionar la tecla **Escape**
- O hacer click fuera del modal (en el fondo oscuro)

---

## 13. Preguntas Frecuentes

**¿Por qué no puedo iniciar sesión?**  
Verificar que la contraseña sea `Admin123` (con A mayúscula). Si sigue sin funcionar, abrir `http://localhost/SISGECLIN/diagnostico_password.php` y hacer click en "Actualizar Todas las Contraseñas".

**¿Por qué no veo el módulo de Nómina o Usuarios?**  
Esos módulos solo son visibles para usuarios con rol **Administrador**. Si tu usuario tiene rol médico o recepcionista, no tendrás acceso.

**¿Cómo actualizo el stock de un medicamento?**  
No se edita directamente. Ir a **Movimientos de Inventario** y registrar una **Entrada** (para aumentar stock) o una **Salida** (para reducir stock). Esto mantiene el historial de movimientos.

**¿Qué pasa si elimino un paciente?**  
Se eliminan también todas sus consultas. Esta acción es permanente e irreversible.

**¿Cómo sé qué medicamentos tienen stock bajo?**  
El **Dashboard** muestra una alerta con los medicamentos cuya cantidad es igual o menor al stock mínimo configurado. También en el módulo de Medicamentos, esos registros aparecen resaltados en rojo.

**¿Puedo exportar solo los empleados de un departamento específico?**  
Actualmente la exportación de Nómina exporta todos los empleados. Para filtrar, abrir el CSV en Excel y usar los filtros de columna.

**¿El sistema funciona en celular?**  
Sí, el diseño es responsive. En pantallas pequeñas el menú lateral se oculta y se puede abrir con el ícono de menú ☰ en la barra superior.

**¿Cómo cambio el tema a modo oscuro?**  
Hacer click en el ícono de luna 🌙 en la barra superior. Para volver al modo claro, hacer click en el ícono de sol ☀️.

**¿Qué significa cada estado de una consulta?**  
- **Pendiente:** Consulta programada, aún no atendida
- **En Proceso:** El médico está atendiendo al paciente
- **Completada:** La consulta finalizó con diagnóstico y tratamiento
- **Cancelada:** La consulta fue cancelada

---

## Atajos de Teclado

| Tecla | Acción |
|---|---|
| `Escape` | Cerrar modal abierto |
| `Enter` (en buscador) | Ejecutar búsqueda |

---

## Información del Sistema

| Dato | Valor |
|---|---|
| Nombre | SISGECLIN |
| Versión | 2.0 |
| URL local | http://localhost/SISGECLIN/ |
| Base de datos | sisgeclin |
| Servidor | localhost |
| Usuario BD | root |
| Contraseña BD | (vacía) |

---

*Guía de Usuario — SISGECLIN v2.0 — Mayo 2026*
