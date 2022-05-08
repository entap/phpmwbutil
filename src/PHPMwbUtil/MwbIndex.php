<?php

namespace PHPMwbUtil;

class MwbIndex
{
    public $id;
    public $name;
    public $comment;
    public $columns;
    public $indexType;
    public $isPrimary;
    public $unique;

    public function isSingle()
    {
        return count($this->columns) == 1;
    }
}