<?php

namespace PHPMwbUtil;

class MwbForeignKey
{
    public $id;
    public $name;
    public $comment;
    public $referencedTable;
    public $referencedColumns;
    public $columns;
    public $index;

    public function isSingle()
    {
        return count($this->columns) == 1 && count($this->referencedColumns) == 1;
    }
}