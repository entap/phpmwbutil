<?php

namespace PHPMwbUtil\LaravelMigration;

class LaravelIndex
{
    // MySQLWorkbenchのインデックス区分からLaravelのメソッド名への変換テーブル
    static private $laravelIndices = [
        'PRIMARY' => 'primary',
        'INDEX' => 'index',
        'UNIQUE' => 'unique',
        'SPATIAL' => 'spatialIndex',
        'FULLTEXT' => 'fulltext',
    ];

    private $type;
    private $columns = [];
    private $name = NULL;

    /**
     * インデクスの種類を設定する
     *
     * @param $indexType string インデクスの種類
     */
    public function setIndexType(string $indexType)
    {
        $this->type = LaravelIndex::toLaravelMethod($indexType, true);
    }

    /**
     * インデクスの名前を設定する
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * インデクス対象のカラムを設定する
     */
    public function addColumn(string $column)
    {
        $this->columns[] = $column;
    }

    /**
     * 生成されたコードを出力する
     *
     * @param PhpWriter $writer
     */
    public function write(PhpWriter $writer)
    {
        $typeArgs = $this->name === NULL ? [$this->columns] : [$this->columns, $this->name];
        $writer->nextLine();
        $writer->write('$table');
        $writer->writeCall($this->type, $typeArgs);
    }

    /**
     * MySQLWorkbenchのインデクスから、Laravelのインデクスメソッド名に変換する
     *
     * @param $indexType string インデクス名
     * @param $forTable bool テーブル用のメソッドか？(LaravelのBUG対応。テーブル用はfulltextではなくてfullText)
     * @return string Laravelのインデクスメソッド名
     */
    static public function toLaravelMethod(string $indexType, $forTable = false)
    {
        if (array_key_exists($indexType, LaravelIndex::$laravelIndices)) {
            $laravelMethod = LaravelIndex::$laravelIndices[$indexType];
            return ($laravelMethod == 'fulltext' && $forTable) ? 'fullText' : $laravelMethod;
        } else {
            throw new \Error('Invalid index type: ' . $indexType);
        }
    }
}