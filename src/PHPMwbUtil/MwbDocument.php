<?php

namespace PHPMwbUtil;

class MwbDocument
{
    public $physicalModels;

    public function tables(): array
    {
        return $this->physicalModels[0]->catalog->schemata[0]->tables;
    }

    public function tableById(string $id)
    {
        foreach ($this->tables() as $table) {
            if ($table->id == $id) {
                return $table;
            }
        }
        return NULL;
    }
}