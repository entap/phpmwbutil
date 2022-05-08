<?php

require_once __DIR__ . '/../vendor/autoload.php';

$loader = new \PHPMwbUtil\MwbLoader();
$loader->load('example01.mwb');
$document = $loader->document;

$migration = new \PHPMwbUtil\LaravelMigration\Migration();
$writer = new \PHPMwbUtil\LaravelMigration\PhpWriter();
foreach ($document->tables() as $table) {
    $migration->createTable($writer, $table);
}

echo $writer->contents();