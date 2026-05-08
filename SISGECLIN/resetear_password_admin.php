<?php
/**
 * Script para Resetear Contraseñas de Usuarios
 * Genera hashes correctos y actualiza la base de datos
 */

// Configuración de la base de datos
$host = 'localhost';
$dbname = 'sisgeclin';
$username = 'root';
$password = '';

// Nueva contraseña para todos los usuarios
$nueva_password = 'Admin123';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resetear Contraseñas - SISGECLIN</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        .header p {
            opacity: 0.9;
            font-size: 14px;
        }
        .content {
            padding: 30px;
        }
        .info-box {
            background: #e7f3ff;
            border: 2px solid #2196F3;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .info-box h3 {
            color: #1976D2;
            margin-bottom: 10px;
            font-size: 18px;
        }
        .info-box p {
            color: #555;
            line-height: 1.6;
            margin-bottom: 10px;
        }
        .info-box code {
            background: #fff;
            padding: 2px 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            color: #d63384;
            border: 1px solid #ddd;
        }
        .success {
            background: #d4edda;
            border-color: #28a745;
        }
        .success h3 {
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border-color: #dc3545;
        }
        .error h3 {
            color: #721c24;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: transform 0.2s;
            border: none;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        .credentials {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }
        .credentials table {
            width: 100%;
            border-collapse: collapse;
        }
        .credentials th,
        .credentials td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        .credentials th {
            background: #e9ecef;
            font-weight: 600;
            color: #495057;
        }
        .credentials td {
            color: #212529;
        }
        .credentials code {
            background: #fff;
            padding: 4px 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            color: #d63384;
            border: 1px solid #ddd;
        }
        .hash-info {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            font-size: 13px;
            color: #856404;
        }
        .hash-info strong {
            display: block;
            margin-bottom: 5px;
            color: #533f03;
        }
        .hash-info code {
            display: block;
            background: #fff;
            padding: 8px;
            border-radius: 4px;
            margin-top: 5px;
            word-break: break-all;
            font-size: 11px;
            border: 1px solid #ffc107;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Resetear Contraseñas</h1>
            <p>Actualizar contraseñas de usuarios del sistema</p>
        </div>
        
        <div class="content">
            <?php
            if (isset($_POST['resetear'])) {
                try {
                    // Conectar a la base de datos
                    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
                    echo '<div class="info-box success">';
                    echo '<h3>✓ Conexión exitosa</h3>';
                    echo '<p>Conectado a la base de datos <code>' . htmlspecialchars($dbname) . '</code></p>';
                    echo '</div>';
                    
                    // Generar hash de la contraseña
                    $hash = password_hash($nueva_password, PASSWORD_BCRYPT);
                    
                    echo '<div class="hash-info">';
                    echo '<strong>Hash generado:</strong>';
                    echo '<code>' . htmlspecialchars($hash) . '</code>';
                    echo '</div>';
                    
                    // Actualizar contraseñas de todos los usuarios
                    $stmt = $pdo->prepare("UPDATE usuarios SET password = :password");
                    $stmt->execute(['password' => $hash]);
                    
                    $affected = $stmt->rowCount();
                    
                    echo '<div class="info-box success">';
                    echo '<h3>✓ Contraseñas actualizadas</h3>';
                    echo '<p>Se actualizaron <strong>' . $affected . '</strong> usuarios correctamente.</p>';
                    echo '</div>';
                    
                    // Mostrar usuarios actualizados
                    $stmt = $pdo->query("SELECT id, username, email, nombre_completo, rol FROM usuarios ORDER BY id");
                    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo '<div class="info-box">';
                    echo '<h3>👥 Usuarios Actualizados</h3>';
                    echo '<div class="credentials">';
                    echo '<table>';
                    echo '<thead><tr><th>Usuario</th><th>Email</th><th>Rol</th><th>Contraseña</th></tr></thead>';
                    echo '<tbody>';
                    foreach ($usuarios as $u) {
                        echo '<tr>';
                        echo '<td><code>' . htmlspecialchars($u['username']) . '</code></td>';
                        echo '<td>' . htmlspecialchars($u['email']) . '</td>';
                        echo '<td>' . htmlspecialchars($u['rol']) . '</td>';
                        echo '<td><code>' . htmlspecialchars($nueva_password) . '</code></td>';
                        echo '</tr>';
                    }
                    echo '</tbody>';
                    echo '</table>';
                    echo '</div>';
                    echo '</div>';
                    
                    echo '<div class="info-box success">';
                    echo '<h3>🎉 ¡Listo!</h3>';
                    echo '<p>Ahora puedes iniciar sesión con cualquiera de estos usuarios usando la contraseña: <code>' . htmlspecialchars($nueva_password) . '</code></p>';
                    echo '</div>';
                    
                    echo '<a href="index.php" class="btn">Ir al Sistema</a>';
                    
                } catch (PDOException $e) {
                    echo '<div class="info-box error">';
                    echo '<h3>✗ Error</h3>';
                    echo '<p><strong>Mensaje:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
                    echo '<p><strong>Código:</strong> ' . $e->getCode() . '</p>';
                    echo '</div>';
                    
                    echo '<div class="info-box">';
                    echo '<h3>Posibles soluciones:</h3>';
                    echo '<ul style="margin-left: 20px; color: #666;">';
                    echo '<li>Verifica que la base de datos <code>' . htmlspecialchars($dbname) . '</code> exista</li>';
                    echo '<li>Verifica las credenciales de conexión</li>';
                    echo '<li>Asegúrate de que MySQL esté ejecutándose</li>';
                    echo '</ul>';
                    echo '</div>';
                    
                    echo '<button onclick="location.reload()" class="btn">Reintentar</button>';
                }
            } else {
                ?>
                <div class="info-box">
                    <h3>📋 ¿Qué hace este script?</h3>
                    <p>Este script realizará las siguientes acciones:</p>
                    <ul style="margin-left: 20px; color: #666; line-height: 1.8;">
                        <li>Generará un hash seguro de la contraseña <code><?php echo htmlspecialchars($nueva_password); ?></code></li>
                        <li>Actualizará la contraseña de <strong>todos los usuarios</strong> en la base de datos</li>
                        <li>Te mostrará las credenciales actualizadas</li>
                    </ul>
                </div>
                
                <div class="info-box">
                    <h3>🔐 Nueva Contraseña</h3>
                    <p>Todos los usuarios tendrán la contraseña: <code><?php echo htmlspecialchars($nueva_password); ?></code></p>
                </div>
                
                <div class="info-box">
                    <h3>👥 Usuarios que se Actualizarán</h3>
                    <div class="credentials">
                        <table>
                            <thead>
                                <tr>
                                    <th>Usuario</th>
                                    <th>Rol</th>
                                    <th>Nueva Contraseña</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>admin</code></td>
                                    <td>Administrador</td>
                                    <td><code><?php echo htmlspecialchars($nueva_password); ?></code></td>
                                </tr>
                                <tr>
                                    <td><code>medico</code></td>
                                    <td>Médico</td>
                                    <td><code><?php echo htmlspecialchars($nueva_password); ?></code></td>
                                </tr>
                                <tr>
                                    <td><code>recepcion</code></td>
                                    <td>Recepcionista</td>
                                    <td><code><?php echo htmlspecialchars($nueva_password); ?></code></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="hash-info">
                    <strong>ℹ️ Información Técnica:</strong>
                    <p>El script usa <code>password_hash()</code> con <code>PASSWORD_BCRYPT</code> para generar un hash seguro compatible con <code>password_verify()</code>.</p>
                </div>
                
                <form method="POST">
                    <button type="submit" name="resetear" class="btn">🚀 Resetear Contraseñas</button>
                </form>
                <?php
            }
            ?>
        </div>
    </div>
</body>
</html>
