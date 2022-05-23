<?php

namespace PHPMwbUtil\LaravelMigration;

use PHPMwbUtil\MwbColumn;
use PHPMwbUtil\MwbDocument;
use PHPMwbUtil\MwbIndex;
use PHPMwbUtil\MwbTable;

class Migration
{
    /**
     * 新旧ドキュメントを比較して、新しく作成されたテーブルを取得する
     *
     * @param MwbDocument $newDocument
     * @param MwbDocument|NULL $prevDocument
     * @return array
     */
    public function getCreatedTables(MwbDocument $newDocument, MwbDocument $prevDocument = NULL)
    {
        $newTables = $newDocument->tables();
        if ($prevDocument === NULL) {
            return $newTables;
        }
        $createdTables = [];
        foreach ($newTables as $table) {
            if ($prevDocument->find($table->id) === NULL) {
                yield $table;
                $createdTables[] = $table;
            }
        }
        return $createdTables;
    }

    /**
     * 新旧ドキュメントを比較して、削除されたテーブルを取得する
     *
     * @param MwbDocument $newDocument
     * @param MwbDocument $prevDocument
     * @return array
     */
    public function getDroppedTables(MwbDocument $newDocument, MwbDocument $prevDocument)
    {
        if ($prevDocument == NULL) {
            return [];
        }
        return $this->getCreatedTables($prevDocument, $newDocument);
    }

    /**
     * 新旧ドキュメントを比較して、名前変更されたテーブルを取得する
     *
     * @param MwbDocument $newDocument
     * @param MwbDocument $prevDocument
     * @return array
     */
    public function getRenamedTables(MwbDocument $newDocument, MwbDocument $prevDocument)
    {
        if ($prevDocument === NULL) {
            return [];
        }
        $newTables = $newDocument->tables();
        $renamedTables = [];
        foreach ($newTables as $newTable) {
            $prevTable = $prevDocument->find($newTable->id);
            if ($prevTable) {
                if ($newTable->name != $prevTable->name) {
                    $renamedTables[] = $newTable;
                }
            }
        }
        return $renamedTables;
    }

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
            if (!$index->single()) {
                $this->addIndex($writer, $mwbTable, $index);
            }
        }
        $writer->decreaseIndent();
        $writer->nextLine();
        $writer->write("});");
    }

    /**
     * テーブル削除のPHPコードを出力する
     *
     * @param PhpWriter $writer ソースコードを取得する先
     * @param MwbTable $mwbTable 追加するテーブル
     */
    public function dropTable(PhpWriter $writer, MwbTable $mwbTable)
    {
        $writer->nextLine();
        $writer->write("Schema::drop('" . $mwbTable->name . "');");
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
        $builder = new LaravelColumnBuilder();
        $laravelColumn = $builder->create($mwbColumn);
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
        foreach ($mwbTable->indicesByColumn($mwbColumn) as $index) {
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
            $column = $mwbTable->columnById($indexColumn->referencedColumn);
            if ($column) {
                $laravelIndex->addColumn($column->name);
            }
        }
        $laravelIndex->write($writer);
    }
}
