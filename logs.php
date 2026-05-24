<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Inicializar el historial de logs si no existe
if (!isset($_SESSION['system_logs'])) {
    $_SESSION['system_logs'] = [
        ['time' => date('H:i:s'), 'type' => 'info', 'text' => 'Sistema iniciado - Base de datos: SQLite']
    ];
}

// DECLARACIÓN SEGURA: Solo se crea la función si no existe previamente
if (!function_exists('registrar_log')) {
    function registrar_log($texto, $tipo = 'info') {
        if (!isset($_SESSION['system_logs'])) {
            $_SESSION['system_logs'] = [];
        }
        array_unshift($_SESSION['system_logs'], [
            'time' => date('H:i:s'),
            'type' => $tipo,
            'text' => $texto
        ]);
    }
}
?>
<div class="log-sidebar">
    <div class="log-header">AUTH_LOG / TRIGGER</div>
    <?php foreach ($_SESSION['system_logs'] as $log): ?>
        <div class="log-entry">
            <span class="log-time"><?php echo $log['time']; ?></span>
            <?php if ($log['type'] === 'success'): ?>
                <span class="log-success">✔ <?php echo htmlspecialchars($log['text']); ?></span>
            <?php elseif ($log['type'] === 'fail'): ?>
                <span class="log-fail">❌ <?php echo htmlspecialchars($log['text']); ?></span>
            <?php else: ?>
                <span class="log-info">i <?php echo htmlspecialchars($log['text']); ?></span>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>