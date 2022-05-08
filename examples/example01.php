<?php

require_once __DIR__ . '/../vendor/autoload.php';

$loader = new \PHPMwbUtil\MwbLoader();
$loader->load('example01.mwb');
var_dump($loader->document->physicalModels);
var_dump($loader->data);