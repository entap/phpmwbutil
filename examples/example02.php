<?php

require_once __DIR__ . '/../vendor/autoload.php';

$loader = new \PHPMwbUtil\MwbLoader();
$loader->load('example01.mwb');
//var_dump($loader->document->physicalModels);
//var_dump($loader->data);

$writer = new \PHPMwbUtil\LaravelMigration\PhpWriter();
$migration = new \PHPMwbUtil\LaravelMigration\Migration();
foreach ($loader->document->tables() as $table) {
    $migration->createTable($writer, $table);
}

echo $writer->contents();