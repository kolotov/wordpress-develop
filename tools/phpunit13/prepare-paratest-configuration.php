<?php

declare(strict_types=1);

if ($argc !== 6) {
    fwrite(
        STDERR,
        "Usage: php tools/phpunit13/prepare-paratest-configuration.php <workspace> <source-config> <parallel-config> <isolated-config> <isolated-files-manifest>\n",
    );
    exit(2);
}

[, $workspace, $sourceConfig, $parallelConfig, $isolatedConfig, $manifest] = $argv;
$workspace = rtrim($workspace, DIRECTORY_SEPARATOR);
$sourcePath = $workspace . DIRECTORY_SEPARATOR . $sourceConfig;
$parallelPath = $workspace . DIRECTORY_SEPARATOR . $parallelConfig;
$isolatedPath = $workspace . DIRECTORY_SEPARATOR . $isolatedConfig;
$manifestPath = $workspace . DIRECTORY_SEPARATOR . $manifest;
$testsRoot = $workspace . DIRECTORY_SEPARATOR . 'tests/phpunit/tests';

if (! is_file($sourcePath)) {
    throw new RuntimeException("Missing PHPUnit configuration: {$sourcePath}");
}
if (! is_dir($testsRoot)) {
    throw new RuntimeException("Missing WordPress tests directory: {$testsRoot}");
}

$sourceDirectory = realpath(dirname($sourcePath));
$parallelDirectory = realpath(dirname($parallelPath));
$isolatedDirectory = realpath(dirname($isolatedPath));
if (
    $sourceDirectory === false
    || $parallelDirectory === false
    || $isolatedDirectory === false
    || $parallelDirectory !== $sourceDirectory
    || $isolatedDirectory !== $sourceDirectory
) {
    throw new RuntimeException(
        'Generated PHPUnit configurations must stay next to the source configuration so relative paths remain valid.',
    );
}

$serialOnlyFiles = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($testsRoot, FilesystemIterator::SKIP_DOTS),
);
foreach ($iterator as $file) {
    if (! $file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $contents = file_get_contents($file->getPathname());
    if ($contents === false) {
        throw new RuntimeException("Cannot read {$file->getPathname()}");
    }
    if (
        ! str_contains($contents, 'RunInSeparateProcess')
        && ! str_contains($contents, 'RunTestsInSeparateProcesses')
    ) {
        continue;
    }

    $serialOnlyFiles[] = str_replace('\\', '/', substr($file->getPathname(), strlen($workspace) + 1));
}

$serialOnlyFiles = array_values(array_unique($serialOnlyFiles));
sort($serialOnlyFiles, SORT_STRING);
if ($serialOnlyFiles === []) {
    throw new RuntimeException('No serial-only WordPress test files were discovered.');
}
if (file_put_contents($manifestPath, implode("\n", $serialOnlyFiles) . "\n") === false) {
    throw new RuntimeException("Cannot write serial-only manifest: {$manifestPath}");
}

$configDirectory = dirname($sourcePath);
$relativeFiles = array_map(
    static function (string $workspaceRelativePath) use ($workspace, $configDirectory): string {
        $absolutePath = $workspace . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $workspaceRelativePath);
        return str_replace('\\', '/', substr($absolutePath, strlen($configDirectory) + 1));
    },
    $serialOnlyFiles,
);

$load = static function (string $path): DOMDocument {
    $document = new DOMDocument();
    $document->preserveWhiteSpace = true;
    $document->formatOutput = false;
    if (! $document->load($path)) {
        throw new RuntimeException("Cannot parse PHPUnit configuration: {$path}");
    }
    return $document;
};

$parallel = $load($sourcePath);
$parallelSuites = $parallel->getElementsByTagName('testsuite');
if ($parallelSuites->length === 0) {
    throw new RuntimeException("No testsuite found in {$sourcePath}");
}
$defaultSuite = $parallelSuites->item(0);
foreach ($relativeFiles as $relativePath) {
    $exclude = $parallel->createElement('exclude');
    $exclude->appendChild($parallel->createTextNode($relativePath));
    $defaultSuite->appendChild($exclude);
}
if ($parallel->save($parallelPath) === false) {
    throw new RuntimeException("Cannot write ParaTest configuration: {$parallelPath}");
}

$isolated = $load($sourcePath);
$testsuitesNodes = $isolated->getElementsByTagName('testsuites');
if ($testsuitesNodes->length !== 1) {
    throw new RuntimeException("Expected exactly one testsuites node in {$sourcePath}");
}
$testsuites = $testsuitesNodes->item(0);
while ($testsuites->firstChild !== null) {
    $testsuites->removeChild($testsuites->firstChild);
}
$isolatedSuite = $isolated->createElement('testsuite');
$isolatedSuite->setAttribute('name', 'serial-only-files');
foreach ($relativeFiles as $relativePath) {
    $file = $isolated->createElement('file');
    $file->appendChild($isolated->createTextNode($relativePath));
    $isolatedSuite->appendChild($file);
}
$testsuites->appendChild($isolatedSuite);
if ($isolated->save($isolatedPath) === false) {
    throw new RuntimeException("Cannot write isolated PHPUnit configuration: {$isolatedPath}");
}

printf("Prepared ParaTest split with %d serial-only test files.\n", count($serialOnlyFiles));
