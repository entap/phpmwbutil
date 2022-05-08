<?php

require_once __DIR__ . '/../vendor/autoload.php';

$document = new PHPMwbUtil\MwbDocument();
$document->loadFile('example01.mwb');

$migration = new \PHPMwbUtil\LaravelMigration\Migration();
$writer = new \PHPMwbUtil\LaravelMigration\PhpWriter();
foreach ($document->tables() as $table) {
    $migration->createTable($writer, $table);
}

echo $writer->contents();