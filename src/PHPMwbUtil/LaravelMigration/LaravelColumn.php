<?php

namespace PHPMwbUtil\LaravelMigration;

class LaravelColumn
{
    // Laravelのカラム型
    private $type;

    // Laravelのカラム型の引数
    private $typeArgs;

    // メソッドチェイン
    private $methodArgs = [];

    // メソッドチェインで呼び出す順番
    static private $methodOrders = [
        'autoIncrement',
        'from',
        'nullable',
        'default',
        'primary',
        'index',
        'unique',
        'fulltext',
        'spatialIndex',
        'charset',
        'collation',
        'comment',
        'change',
    ];

    /**
     * Laravelの型と生成引数を設定
     *
     * @param $type string Laravelの型
     * @param $args array 生成引数
     */
    public function __construct($type, $args)
    {
        $this->type = $type;
        $this->typeArgs = $args;
    }

    /**
     * 自動増分として設定
     */
    public function autoIncrement()
    {
        $this->registerMethodChain('autoIncrement');
    }

    /**
     * 自動増分フィールドの開始値を設定
     */
    public function from(int $from)
    {
        $this->registerMethodChain('from', [$from]);
    }

    /**
     * NULL許容を設定
     */
    public function nullable(bool $nullable)
    {
        $this->registerMethodChain('nullable', $nullable ? [] : [false]);
    }

    /**
     * デフォルト値を設定
     */
    public function default(string $default)
    {
        $this->registerMethodChain('default', [$default]);
    }

    /**
     * カラムの文字セットを設定
     */
    public function charset(string $charset)
    {
        $this->registerMethodChain('charset', [$charset]);
    }

    /**
     * カラムのcollationを設定
     */
    public function collation(string $collation)
    {
        $this->registerMethodChain('collation', [$collation]);
    }

    /**
     * カラムのコメントを設定
     */
    public function comment(string $comment)
    {
        $this->registerMethodChain('comment', [$comment]);
    }

    /**
     * カラムの変更
     */
    public function change()
    {
        $this->registerMethodChain('change');
    }

    /**
     * インデクスを設定
     */
    public function index($indexType, $indexName = NULL)
    {
        $args = $indexName == NULL ? [] : [$indexName];
        $this->registerMethodChain(LaravelIndex::toLaravelMethod($indexType), $args);
    }

    /**
     * メソッドチェインの呼び出しを登録する
     *
     * @param $method string メソッド名
     * @param $args 引数
     */
    public function registerMethodChain($method, $args = [])
    {
        $this->methodArgs[$method] = $args;
    }

    /**
     * 生成されたコードを出力する
     *
     * @param PhpWriter $writer
     * @return void
     */
    public function write(PhpWriter $writer)
    {
        $writer->nextLine();
        $writer->write('$table');
        $writer->writeCall($this->type, $this->typeArgs);
        $writer->increaseIndent();
        foreach (LaravelColumn::$methodOrders as $method) {
            if (array_key_exists($method, $this->methodArgs)) {
                $writer->nextLine();
                $writer->writeCall($method, $this->methodArgs[$method]);
            }
        }
        $writer->write(';');
        $writer->decreaseIndent();
    }
}