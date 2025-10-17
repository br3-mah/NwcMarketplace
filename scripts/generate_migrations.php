<?php

declare(strict_types=1);

$baseDir = realpath(__DIR__ . '/..');

if ($baseDir === false) {
    fwrite(STDERR, "Unable to resolve project base directory.\n");
    exit(1);
}

$sqlPath = $baseDir . '';
$migrationDir = $baseDir . '/database/migrations';

if (!is_file($sqlPath)) {
    fwrite(STDERR, "SQL dump not found at {$sqlPath}.\n");
    exit(1);
}

if (!is_dir($migrationDir)) {
    fwrite(STDERR, "Migration directory not found at {$migrationDir}.\n");
    exit(1);
}

$lines = file($sqlPath, FILE_IGNORE_NEW_LINES);

if ($lines === false) {
    fwrite(STDERR, "Failed to read SQL dump.\n");
    exit(1);
}

$tables = [];
$order = [];
$lineCount = count($lines);

for ($i = 0; $i < $lineCount; $i++) {
    $line = ltrim($lines[$i]);

    if (!preg_match('/^CREATE TABLE `([^`]+)`/i', $line, $matches)) {
        continue;
    }

    $tableName = $matches[1];
    $order[] = $tableName;

    $block = [];
    $j = $i;

    while ($j < $lineCount) {
        $block[] = rtrim($lines[$j], "\r\n");

        if (str_contains($lines[$j], ';')) {
            break;
        }

        $j++;
    }

    $tables[$tableName] = [
        'create' => implode("\n", $block),
        'alters' => [],
    ];

    $i = $j;
}

for ($i = 0; $i < $lineCount; $i++) {
    $line = ltrim($lines[$i]);

    if (!preg_match('/^ALTER TABLE `([^`]+)`/i', $line, $matches)) {
        continue;
    }

    $tableName = $matches[1];

    $block = [];
    $j = $i;

    while ($j < $lineCount) {
        $block[] = rtrim($lines[$j], "\r\n");

        if (str_contains($lines[$j], ';')) {
            break;
        }

        $j++;
    }

    if (isset($tables[$tableName])) {
        $tables[$tableName]['alters'][] = implode("\n", $block);
    }

    $i = $j;
}

$skipTables = ['users'];
$timestampBase = new DateTimeImmutable('2024-03-14 00:00:00');
$counter = 0;

foreach ($order as $tableName) {
    if (in_array($tableName, $skipTables, true)) {
        continue;
    }

    if (!isset($tables[$tableName])) {
        continue;
    }

    $info = $tables[$tableName];
    $statements = array_values(array_filter(array_merge([$info['create']], $info['alters'])));

    if (empty($statements)) {
        continue;
    }

    $timestamp = $timestampBase->modify("+{$counter} seconds");
    $counter++;

    if ($timestamp === false) {
        fwrite(STDERR, "Unable to compute timestamp for migration {$tableName}.\n");
        exit(1);
    }

    $filename = sprintf(
        '%s_create_%s_table.php',
        $timestamp->format('Y_m_d_His'),
        $tableName
    );

    $path = $migrationDir . DIRECTORY_SEPARATOR . $filename;

    $statementBlocks = [];

    foreach ($statements as $statement) {
        $statementBlocks[] = "        DB::statement(<<<'SQL'\n{$statement}\nSQL);\n";
    }

    $statementsCode = implode("\n", $statementBlocks);

    $php = <<<PHP
<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Support\\Facades\\Schema;
use Illuminate\\Support\\Facades\\DB;

return new class extends Migration
{
    public function up(): void
    {
{$statementsCode}    }

    public function down(): void
    {
        Schema::dropIfExists('{$tableName}');
    }
};

PHP;

    file_put_contents($path, $php);
}

echo "Generated migrations for " . ($counter) . " tables.\n";
