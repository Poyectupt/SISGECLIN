<?php
/**
 * Script de prueba para verificar que la exportación funciona correctamente
 * Acceder a: http://localhost/SISGECLIN/test_export.php
 */

require_once __DIR__ . '/vendor/autoload.php';

echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Test de Exportación - SISGECLIN</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo "h1 { color: #333; border-bottom: 3px solid #667eea; padding-bottom: 10px; }";
echo ".test-item { margin: 20px 0; padding: 15px; border-left: 4px solid #667eea; background: #f8f9fa; }";
echo ".test-item.success { border-left-color: #28a745; background: #d4edda; }";
echo ".test-item.error { border-left-color: #dc3545; background: #f8d7da; }";
echo ".test-item h3 { margin: 0 0 10px 0; }";
echo ".test-item p { margin: 0; font-size: 14px; }";
echo ".status { font-weight: bold; }";
echo ".status.ok { color: #28a745; }";
echo ".status.error { color: #dc3545; }";
echo ".info { background: #e7f3ff; border-left: 4px solid #2196F3; padding: 15px; margin: 20px 0; }";
echo "</style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";
echo "<h1>✅ Test de Exportación - SISGECLIN</h1>";

// Test 1: mPDF
echo "<div class='test-item success'>";
echo "<h3>📄 mPDF (v8.3.1)</h3>";
if (class_exists('Mpdf\Mpdf')) {
    echo "<p><span class='status ok'>✓ OK</span> - mPDF está correctamente instalado</p>";
    echo "<p>Ubicación: " . dirname((new ReflectionClass('Mpdf\Mpdf'))->getFileName()) . "</p>";
} else {
    echo "<p><span class='status error'>✗ ERROR</span> - mPDF no está instalado</p>";
}
echo "</div>";

// Test 2: PhpSpreadsheet
echo "<div class='test-item success'>";
echo "<h3>📊 PhpSpreadsheet (v5.7.0)</h3>";
if (class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
    echo "<p><span class='status ok'>✓ OK</span> - PhpSpreadsheet está correctamente instalado</p>";
    echo "<p>Ubicación: " . dirname((new ReflectionClass('PhpOffice\PhpSpreadsheet\Spreadsheet'))->getFileName()) . "</p>";
} else {
    echo "<p><span class='status error'>✗ ERROR</span> - PhpSpreadsheet no está instalado</p>";
}
echo "</div>";

// Test 3: Archivos necesarios
echo "<div class='test-item'>";
echo "<h3>📁 Archivos Necesarios</h3>";

$archivos = [
    'Controllers/ExportController.php' => 'Controlador de exportación',
    'vendor/autoload.php' => 'Autoloader de Composer',
    'CSS/script.js' => 'JavaScript de exportación',
];

foreach ($archivos as $archivo => $descripcion) {
    $ruta = __DIR__ . '/' . $archivo;
    if (file_exists($ruta)) {
        echo "<p><span class='status ok'>✓</span> $descripcion ($archivo)</p>";
    } else {
        echo "<p><span class='status error'>✗</span> $descripcion ($archivo) - NO ENCONTRADO</p>";
    }
}

echo "</div>";

// Test 4: Permisos
echo "<div class='test-item'>";
echo "<h3>🔐 Permisos de Carpetas</h3>";

$carpetas = [
    'vendor' => 'Vendor',
    'database' => 'Base de datos',
];

foreach ($carpetas as $carpeta => $nombre) {
    $ruta = __DIR__ . '/' . $carpeta;
    if (is_dir($ruta) && is_readable($ruta)) {
        echo "<p><span class='status ok'>✓</span> $nombre ($carpeta) - Lectura OK</p>";
    } else {
        echo "<p><span class='status error'>✗</span> $nombre ($carpeta) - Problema de permisos</p>";
    }
}

echo "</div>";

// Información
echo "<div class='info'>";
echo "<h3>ℹ️ Información del Sistema</h3>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Servidor:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p><strong>Directorio:</strong> " . __DIR__ . "</p>";
echo "</div>";

// Instrucciones
echo "<div class='info'>";
echo "<h3>📋 Próximos Pasos</h3>";
echo "<ol>";
echo "<li>Acceder al sistema: <a href='index.php' target='_blank'>http://localhost/SISGECLIN/</a></li>";
echo "<li>Iniciar sesión con: admin / Admin123</li>";
echo "<li>Ir a Consultas o Pacientes</li>";
echo "<li>Hacer clic en 'Exportar'</li>";
echo "<li>Seleccionar formato (PDF, Excel, CSV)</li>";
echo "<li>Descargar el archivo</li>";
echo "</ol>";
echo "</div>";

echo "</div>";
echo "</body>";
echo "</html>";
?>
