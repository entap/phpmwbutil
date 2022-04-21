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
}