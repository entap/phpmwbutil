<?php

namespace PHPMwbUtil;

class MwbDocument
{
    public $physicalModels;
    public $objectById = [];

    public function tables(): array
    {
        return $this->physicalModels[0]->catalog->schemata[0]->tables;
    }

    public function find(string $id)
    {
        return array_key_exists($id, $this->objectById) ? $this->objectById[$id] : NULL;
    }
}