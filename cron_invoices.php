<?php
/**
 * cron_invoices.php — Genera facturas mensuales para tenants activos.
 *
 * Programar mensualmente (día 1, 02:00):
 *   0 2 1 * * /usr/bin/php /var/www/kyroscounsel/cron_invoices.php
 *
 * En Windows (Task Scheduler):
 *   php.exe C:\xampp\htdocs\KyrosCounsel\cron_invoices.php
 */

declare(strict_types=1);
define('BASE_PATH', __DIR__);
require BASE_PATH . '/app/bootstrap.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Solo CLI.\n");
}

$start = microtime(true);
echo "[" . date('c') . "] Generando facturas mensuales...\n";

try {
    $count = admin_generate_monthly_invoices();
    $ms = round((microtime(true) - $start) * 1000);
    echo "  ✓ {$count} facturas creadas (en {$ms} ms)\n";
    audit_record('billing.invoices.generated', null, ['count' => $count, 'source' => 'cron']);
} catch (Throwable $e) {
    echo "  ✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
