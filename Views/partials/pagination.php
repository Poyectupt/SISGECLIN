<?php
/**
 * Componente de Paginación Elegante
 * 
 * Variables requeridas:
 * - $pagination: array con current_page, per_page, total, total_pages
 * - $module: string con el nombre del módulo actual
 * - $search: string con el término de búsqueda (opcional)
 */

if (!isset($pagination) || $pagination['total'] == 0) {
    return;
}

$currentPage = $pagination['current_page'];
$perPage = $pagination['per_page'];
$total = $pagination['total'];
$totalPages = $pagination['total_pages'];
$search = $search ?? '';

// Calcular rango de registros mostrados
$from = (($currentPage - 1) * $perPage) + 1;
$to = min($currentPage * $perPage, $total);

// Construir URL base
$baseUrl = "index.php?module={$module}";
if (!empty($search)) {
    $baseUrl .= "&search=" . urlencode($search);
}

// Función para generar URL de página
function pageUrl($base, $page, $perPage) {
    return "{$base}&page={$page}&per_page={$perPage}";
}

// Calcular rango de páginas a mostrar
$range = 2; // Páginas a mostrar a cada lado de la actual
$startPage = max(1, $currentPage - $range);
$endPage = min($totalPages, $currentPage + $range);
?>

<div class="pagination-wrapper">
    <!-- Información de registros -->
    <div class="pagination-info">
        <i class='bx bx-info-circle'></i>
        <span>
            Mostrando <strong><?php echo $from; ?></strong> a <strong><?php echo $to; ?></strong> 
            de <strong><?php echo $total; ?></strong> registros
        </span>
    </div>
    
    <!-- Selector de densidad -->
    <div class="pagination-density">
        <label for="per-page-select">
            <i class='bx bx-list-ul'></i> Mostrar:
        </label>
        <select id="per-page-select" onchange="changePerPage(this.value)">
            <option value="15" <?php echo $perPage == 15 ? 'selected' : ''; ?>>15</option>
            <option value="30" <?php echo $perPage == 30 ? 'selected' : ''; ?>>30</option>
            <option value="50" <?php echo $perPage == 50 ? 'selected' : ''; ?>>50</option>
        </select>
    </div>
    
    <!-- Controles de navegación -->
    <div class="pagination-controls">
        <!-- Botón Anterior -->
        <a href="<?php echo $currentPage > 1 ? pageUrl($baseUrl, $currentPage - 1, $perPage) : '#'; ?>" 
           class="pagination-btn nav-btn <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>"
           <?php echo $currentPage <= 1 ? 'onclick="return false;"' : ''; ?>>
            <i class='bx bx-chevron-left'></i>
            <span>Anterior</span>
        </a>
        
        <!-- Primera página -->
        <?php if ($startPage > 1): ?>
            <a href="<?php echo pageUrl($baseUrl, 1, $perPage); ?>" class="pagination-btn">1</a>
            <?php if ($startPage > 2): ?>
                <span class="pagination-ellipsis">...</span>
            <?php endif; ?>
        <?php endif; ?>
        
        <!-- Páginas del rango -->
        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
            <a href="<?php echo pageUrl($baseUrl, $i, $perPage); ?>" 
               class="pagination-btn <?php echo $i == $currentPage ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
        
        <!-- Última página -->
        <?php if ($endPage < $totalPages): ?>
            <?php if ($endPage < $totalPages - 1): ?>
                <span class="pagination-ellipsis">...</span>
            <?php endif; ?>
            <a href="<?php echo pageUrl($baseUrl, $totalPages, $perPage); ?>" class="pagination-btn">
                <?php echo $totalPages; ?>
            </a>
        <?php endif; ?>
        
        <!-- Botón Siguiente -->
        <a href="<?php echo $currentPage < $totalPages ? pageUrl($baseUrl, $currentPage + 1, $perPage) : '#'; ?>" 
           class="pagination-btn nav-btn <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>"
           <?php echo $currentPage >= $totalPages ? 'onclick="return false;"' : ''; ?>>
            <span>Siguiente</span>
            <i class='bx bx-chevron-right'></i>
        </a>
    </div>
</div>

<script>
function changePerPage(perPage) {
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('per_page', perPage);
    currentUrl.searchParams.set('page', '1'); // Resetear a página 1
    window.location.href = currentUrl.toString();
}
</script>
