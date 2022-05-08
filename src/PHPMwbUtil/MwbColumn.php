<?php

namespace PHPMwbUtil;

class MwbColumn
{
    public $id;
    public $name;
    public $comment;
    public $simpleType;
    public $autoIncrement;
    public $characterSetName;
    public $collationName;
    public $datatypeExplicitParams;
    public $expression;
    public $generated;
    public $generatedStorage;
    public $defaultValue;
    public $defaultValueIsNull;
    public $flags;
    public $isNotNull;
    public $length;
    public $precision;
    public $scale;

    public function getMysqlType()
    {
        $tokens = explode('.', $this->simpleType);
        return array_pop($tokens);
    }

    public function isUnsigned()
    {
        return in_array('UNSIGNED', $this->flags);
    }

    public function getEnums()
    {
        if (preg_match_all('/\'([^\']*)\'/', $this->datatypeExplicitParams, $matches)) {
            return $matches[1];
        } else {
            return [];
        }
    }
}