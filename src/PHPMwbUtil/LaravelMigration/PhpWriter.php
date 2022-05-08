<?php

namespace PHPMwbUtil\LaravelMigration;

class PhpWriter
{
    private $depth;
    private $contents;

    public function __construct()
    {
        $this->clear();
    }

    /**
     * コードを初期化する
     */
    public function clear()
    {
        $this->depth = 0;
        $this->contents = '';
    }

    /**
     * 生成したコードを返す
     */
    public function contents()
    {
        return $this->contents;
    }

    /**
     * インデントを増やす
     */
    public function increaseIndent()
    {
        $this->depth++;
    }

    /**
     * インデントを減らす
     */
    public function decreaseIndent()
    {
        $this->depth--;
    }

    /**
     * 文字列を出力する
     */
    public function write($str)
    {
        $this->contents .= $str;
    }

    /**
     * 改行とインデント文字を出力する
     */
    public function nextLine()
    {
        $this->contents .= "\n" . $this->indentSpaces();
    }

    /**
     * PHPメソッドコールを出力する
     *
     * @param $method string メソッド名
     * @param $args array 引数
     * @return void
     */
    public function writeCall($method, $args)
    {
        $args = array_map(PhpWriter::class . '::literal', $args);
        $this->writeCallRaw($method, $args);
    }

    /**
     * 引数をエスケープせず、PHPメソッドコールを出力する
     *
     * @param $method string メソッド名
     * @param $args array 引数
     * @return void
     */
    public function writeCallRaw($method, $args)
    {
        $this->write('->' . $method . '(' . implode(', ', $args) . ')');
    }

    /**
     * 現在のインデント分の空白文字を返す
     */
    private function indentSpaces()
    {
        return str_repeat(' ', $this->depth * 4);
    }

    /**
     * 指定した値を表すPHP言語でのリテラル表現を返す
     *
     * @param $val string 値
     * @return string
     */
    static public function literal($val): string
    {
        if (is_string($val)) {
            return '"' . addcslashes($val, "\0..\37\$\{\}\'\"") . '"';
        } else if (is_int($val)) {
            return $val;
        } else if (is_array($val)) {
            return '[' . implode(', ', array_map(PhpWriter::class . '::literal', $val)) . ']';
        } else {
            throw new \Error('PhpWriter::literal called with not supported types');
        }
    }
}