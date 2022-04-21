<?php

require_once __DIR__ . '/../vendor/autoload.php';

$document = new PHPMwbUtil\MwbDocument();
$document->loadFile('example01.mwb');
var_dump($document->physicalModels);
