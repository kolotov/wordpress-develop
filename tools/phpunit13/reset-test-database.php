<?php

declare(strict_types=1);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($argc !== 3 || $argv[1] !== '--reset-prefix') {
    fwrite(STDERR, "Usage: php tools/phpunit13/reset-test-database.php --reset-prefix <table-prefix>\n");
    exit(2);
}

$prefix = $argv[2];
if ($prefix === '' || preg_match('/^[0-9A-Za-z_]+$/', $prefix) !== 1) {
    fwrite(STDERR, "Invalid test table prefix.\n");
    exit(2);
}

$database = getenv('WP_TESTS_DB_NAME') ?: 'wordpress_develop_tests';
$user = getenv('WP_TESTS_DB_USER') ?: 'root';
$password = getenv('WP_TESTS_DB_PASSWORD') ?: 'password';
$hostConfiguration = getenv('WP_TESTS_DB_HOST') ?: 'mysql';
$host = $hostConfiguration;
$port = 3306;

if (preg_match('/^(.+):(\d+)$/', $hostConfiguration, $matches) === 1) {
    $host = $matches[1];
    $port = (int) $matches[2];
}

$mysqli = new mysqli($host, $user, $password, $database, $port);
$mysqli->set_charset('utf8mb4');

$escapedPrefix = strtr($prefix, [
    '\\' => '\\\\',
    '%' => '\\%',
    '_' => '\\_',
]);
$like = $escapedPrefix . '%';
$statement = $mysqli->prepare(
    "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME LIKE ? ESCAPE '\\\\'",
);
$statement->bind_param('ss', $database, $like);
$statement->execute();
$result = $statement->get_result();
$tables = [];
while ($row = $result->fetch_assoc()) {
    $tables[] = $row['TABLE_NAME'];
}
$statement->close();

$mysqli->query('SET FOREIGN_KEY_CHECKS = 0');
foreach ($tables as $table) {
    $identifier = '`' . str_replace('`', '``', $table) . '`';
    $mysqli->query("DROP TABLE IF EXISTS {$identifier}");
}
$mysqli->query('SET FOREIGN_KEY_CHECKS = 1');
$mysqli->close();
