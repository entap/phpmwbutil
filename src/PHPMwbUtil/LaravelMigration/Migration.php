<?php

namespace PHPMwbUtil\LaravelMigration;

use PHPMwbUtil\MwbColumn;
use PHPMwbUtil\MwbIndex;
use PHPMwbUtil\MwbTable;

class Migration
{
    /**
     * テーブル作成のPHPコードを出力する
     *
     * @param PhpWriter $writer ソースコードを取得する先
     * @param MwbTable $mwbTable 追加するテーブル
     */
    public function createTable(PhpWriter $writer, MwbTable $mwbTable)
    {
        $writer->nextLine();
        $writer->write("Schema::create('" . $mwbTable->name . "', function (Blueprint \$table) {");
        $writer->increaseIndent();
        foreach ($mwbTable->columns as $column) {
            $this->addColumn($writer, $mwbTable, $column);
        }
        foreach ($mwbTable->indices as $index) {
            if (!$index->isSingleColumn()) {
                $this->addIndex($writer, $mwbTable, $index);
            }
        }
        $writer->decreaseIndent();
        $writer->nextLine();
        $writer->write("});");
    }

    /**
     * カラム追加のPHPコードを出力する
     *
     * @param PhpWriter $writer ソースコードを取得する先
     * @param MwbTable $mwbTable 対象のテーブル
     * @param MwbColumn $mwbColumn 追加するカラム
     */
    public function addColumn(PhpWriter $writer, MwbTable $mwbTable, MwbColumn $mwbColumn)
    {
        $laravelColumn = new LaravelColumn();
        $this->setupType($laravelColumn, $mwbColumn);
        if ($mwbColumn->autoIncrement) {
            if ($mwbTable->nextAutoInc !== '') {
                $laravelColumn->autoIncrement($mwbTable->nextAutoInc);
            }
        }
        if (!$mwbColumn->isNotNull) {
            $laravelColumn->nullable(true);
        }
        if ($mwbColumn->defaultValue !== '' && !$mwbColumn->defaultValueIsNull) {
            $laravelColumn->default($mwbColumn->defaultValue);
        }
        if ($mwbColumn->characterSetName !== $mwbTable->defaultCharacterSetName) {
            $laravelColumn->charset($mwbColumn->characterSetName);
        }
        if ($mwbColumn->collationName !== $mwbTable->defaultCollationName) {
            $laravelColumn->collation($mwbColumn->collationName);
        }
        if ($mwbColumn->comment !== '') {
            $laravelColumn->comment($mwbColumn->comment);
        }
        foreach ($mwbTable->getSingleIndices($mwbColumn) as $index) {
            $laravelColumn->index($index->indexType, $index->name);
        }
        $laravelColumn->write($writer);
    }

    /**
     * インデックス追加のPHPコードを出力する
     *
     * @param PhpWriter $writer ソースコードを取得する先
     * @param MwbTable $mwbTable テーブル
     * @param MwbIndex $mwbIndex 追加するインデクス
     */
    public function addIndex(PhpWriter $writer, MwbTable $mwbTable, MwbIndex $mwbIndex)
    {
        $laravelIndex = new LaravelIndex();
        $laravelIndex->setIndexType($mwbIndex->indexType);
        foreach ($mwbIndex->columns as $indexColumn) {
            $column = $mwbTable->getColumnById($indexColumn->referencedColumn);
            if ($column) {
                $laravelIndex->addColumn($column->name);
            }
        }
        $laravelIndex->write($writer);
    }

    /**
     * MMySQLWorkbenchのカラム型から、Laravelのカラム型を設定する
     *
     * @param LaravelColumn $laravelColumn Laravelのカラム型
     * @param MwbColumn $mwbColumn MySQLWorkbenchのカラム型
     * @return LaravelColumn
     */
    public function setupType(LaravelColumn $laravelColumn, MwbColumn $mwbColumn)
    {
        $mysqlType = $mwbColumn->getMysqlType();
        if (LaravelColumn::isIntegerType($mysqlType)) {
            $laravelColumn->integer($mysqlType, $mwbColumn->name, $mwbColumn->autoIncrement, $mwbColumn->isUnsigned());
        } else if (LaravelColumn::isDecimalType($mysqlType)) {
            $laravelColumn->decimal($mysqlType, $mwbColumn->name, $mwbColumn->precision, $mwbColumn->scale, $mwbColumn->isUnsigned());
        } else if (LaravelColumn::isStringType($mysqlType)) {
            $laravelColumn->string($mysqlType, $mwbColumn->name, $mwbColumn->length);
        } else if (LaravelColumn::isTimeType($mysqlType)) {
            $laravelColumn->time($mysqlType, $mwbColumn->name, $mwbColumn->length);
        } else if (LaravelColumn::isEnumType($mysqlType)) {
            $laravelColumn->enum($mysqlType, $mwbColumn->name, $mwbColumn->getEnums());
        } else if (LaravelColumn::isSupportedType($mysqlType)) {
            $laravelColumn->type($mysqlType, $mwbColumn->name);
        }
        return $laravelColumn;
    }
}
