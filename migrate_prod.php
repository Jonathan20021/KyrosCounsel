<?php
/**
 * Migrador one-shot para aplicar el schema completo a la DB de produccion.
 *
 * Uso (CLI, desde la carpeta del proyecto):
 *   $env:KYROS_ENV='production'; & "C:/xampp/php/php.exe" -d extension=sodium migrate_prod.php
 *
 * Idempotente en lo posible: ALTER TABLE usa IF NOT EXISTS donde aplique.
 * Las tablas de install_v2..v9 se DROPean y recrean (estan en intake fresco).
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Solo CLI.\n");
}

define('BASE_PATH', __DIR__);
require BASE_PATH . '/config.php';

if (APP_ENV !== 'production') {
    fwrite(STDERR, "ERROR: APP_ENV=" . APP_ENV . ". Ejecuta con: \$env:KYROS_ENV='production' antes.\n");
    exit(1);
}

echo "Conectando a " . DB_HOST . " ... ";
$dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', DB_HOST, DB_PORT, DB_NAME, DB_CHARSET);
try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => true,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
    ]);
} catch (Throwable $e) {
    fwrite(STDERR, "FAIL: " . $e->getMessage() . "\n");
    exit(1);
}
echo "OK\n";
echo "Host: " . DB_HOST . "  DB: " . DB_NAME . "  User: " . DB_USER . "\n\n";

$files = [
    'install.sql',
    'install_v2.sql',
    'install_v3.sql',
    'install_v4.sql',
    'install_v5.sql',
    'install_v6.sql',
    'install_v7.sql',
    'install_v8.sql',
    'install_v9.sql',
];

foreach ($files as $f) {
    $path = BASE_PATH . '/' . $f;
    if (!is_file($path)) {
        echo "  [skip] {$f} (no existe)\n";
        continue;
    }
    echo "==> {$f}\n";
    $sql = file_get_contents($path);

    // Remover CREATE DATABASE y USE (la conexion ya esta posicionada).
    $sql = preg_replace('/^\s*CREATE\s+DATABASE[^;]+;/im', '', $sql);
    $sql = preg_replace('/^\s*USE\s+\w+\s*;/im', '', $sql);
    // MySQL 8 no soporta "ADD COLUMN IF NOT EXISTS" (sintaxis MariaDB).
    // Como la DB de produccion esta limpia, las columnas no existen, asi
    // que quitar el "IF NOT EXISTS" es seguro para esta migracion fresca.
    $sql = preg_replace('/\bADD\s+COLUMN\s+IF\s+NOT\s+EXISTS\b/i', 'ADD COLUMN', $sql);

    try {
        $pdo->exec($sql);
        echo "    OK\n";
    } catch (Throwable $e) {
        fwrite(STDERR, "  [ERROR en {$f}] " . $e->getMessage() . "\n");
        exit(2);
    }
}

echo "\n[OK] Schema aplicado.\n";

$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo "Tablas en DB: " . count($tables) . "\n";
foreach ($tables as $t) echo "  - {$t}\n";

