<?php

namespace PHPMwbUtil;

class MwbTable
{
    public $id;
    public $name;
    public $comment;
    public $columns;
    public $primaryKey;
    public $indices;
    public $foreignKeys;
    public $nextAutoInc;
    public $defaultCharacterSetName;
    public $defaultCollationName;

    public function columnById(string $id)
    {
        foreach ($this->columns as $column) {
            if ($column->id == $id) {
                return $column;
            }
        }
        return NULL;
    }

    public function indicesByColumn(MwbColumn $mwbColumn)
    {
        foreach ($this->indices as $index) {
            if ($index->single()) {
                if ($index->columns[0]->referencedColumn == $mwbColumn->id) {
                    yield $index;
                }
            }
        }
    }
}