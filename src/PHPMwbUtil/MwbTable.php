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

    /**
     * IDを指定してカラムを取得する
     */
    public function getColumnById($id)
    {
        foreach ($this->columns as $column) {
            if ($column->id == $id) {
                return $column;
            }
        }
        return NULL;
    }

    /**
     * 指定したカラムに紐付いた、単一インデックスを取得する
     *
     * @param MwbColumn $mwbColumn
     * @return array
     */
    public function getSingleIndices(MwbColumn $mwbColumn)
    {
        $singleIndices = [];
        foreach ($this->indices as $index) {
            if ($index->isSingleColumn()) {
                if ($index->columns[0]->referencedColumn == $mwbColumn->id) {
                    $singleIndices[] = $index;
                }
            }
        }
        return $singleIndices;
    }
}