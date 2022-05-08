<?php

namespace PHPMwbUtil\LaravelMigration;

class LaravelColumn
{
    // Laravelのカラム型
    private $type;

    // Laravelのカラム型の引数
    private $typeOptions;

    // メソッドチェイン
    private $methodChains = [];

    // 整数値型のサイズトークン
    static private $integerSizes = [
        'tinyint' => 'Tiny',
        'smallint' => 'Small',
        'mediumint' => 'Medium',
        'int' => '',
        'bigint' => 'Big',
    ];

    // MySQL型からLaravel型への変換テーブル
    static private $laravelTypes = [
        'tinyint' => 'tinyInteger',
        'smallint' => 'smallInteger',
        'mediumint' => 'mediumInteger',
        'int' => 'integer',
        'bigint' => 'bigInteger',
        'float' => 'float',
        'real' => 'double',
        'double' => 'double',
        'decimal' => 'decimal',
        'char' => 'char',
        'nchar' => 'char',
        'varchar' => 'string',
        'nvarchar' => 'string',
        'binary' => 'blob',
        'varbinary' => 'blob',
        'tinytext' => 'tinyText',
        'text' => 'text',
        'mediumtext' => 'mediumText',
        'longtext' => 'longText',
        'tinyblob' => 'blob',
        'blob' => 'blob',
        'mediumblob' => 'blob',
        'longblob' => 'blob',
        'json' => 'json',
        'datetime' => 'datetime',
        'datetime_f' => 'datetime',
        'date' => 'date',
        'time' => 'time',
        'time_f' => 'time',
        'year' => 'year',
        'timestamp' => 'timestamp',
        'timestamp_f' => 'timestamp',
        'geometry' => 'geometry',
        'point' => 'point',
        'linestring' => 'lineString',
        'polygon' => 'polygon',
        'geometrycollection' => 'geometryCollection',
        'multipoint' => 'multiPoint',
        'multilinestring' => 'multiLineString',
        'multipolygon' => 'multiPolygon',
        //'bit'=>'bit',//未対応
        'boolean' => 'boolean',
        'enum' => 'enum',
        'set' => 'set',
    ];

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
        'generated',
        'charset',
        'collation',
        'comment',
        'change',
    ];

    /**
     * 指定されたMySQL型が整数型かを返す
     *
     * @param $mysqlType string MySQL型の名前
     * @return bool 整数型か?
     */
    public static function isIntegerType($mysqlType)
    {
        return in_array($mysqlType, ['tinyint', 'smallint', 'mediumint', 'int', 'bigint']);
    }

    /**
     * 指定されたMySQL型が小数型かを返す
     *
     * @param $mysqlType string MySQL型の名前
     * @return 小数型か?
     */
    public static function isDecimalType($mysqlType)
    {
        return in_array($mysqlType, ['float', 'double', 'decimal']);
    }

    /**
     * 指定されたMySQL型が文字列型かを返す
     *
     * @param $mysqlType string MySQL型の名前
     * @return 文字列型か?
     */
    public static function isStringType($mysqlType)
    {
        return in_array($mysqlType, ['char', 'nchar', 'varchar', 'nvarchar']);
    }

    /**
     * 指定されたMySQL型が時間型かを返す
     *
     * @param $mysqlType string MySQL型の名前
     * @return 時間型か?
     */
    public static function isTimeType($mysqlType)
    {
        return in_array($mysqlType, ['datetime', 'datetime_f', 'time', 'time_f', 'timestamp', 'timestamp_f']);
    }

    /**
     * 指定されたMySQL型が列挙型かを返す
     *
     * @param $mysqlType string MySQL型の名前
     * @return 列挙型か?
     */
    public static function isEnumType($mysqlType)
    {
        return in_array($mysqlType, ['enum', 'set']);
    }

    /**
     * 指定されたMySQL型がサポートされているかを返す
     *
     * @param $mysqlType string MySQL型の名前
     * @return サポートされているか?
     */
    public static function isSupportedType($mysqlType)
    {
        return array_key_exists($mysqlType, LaravelColumn::$laravelTypes);
    }

    /**
     * カラムの型を整数に設定する
     *
     * @param $mysqlType string tinyint/smallint/mediumint/bigintのいずれか
     * @param $column string カラム名
     * @param $autoIncrement bool 自動増分か？
     * @param $unsigned bool 符号なしか？
     */
    public function integer(string $mysqlType, string $column, bool $autoIncrement, bool $unsigned)
    {
        assert(LaravelColumn::isIntegerType($mysqlType));
        $size = LaravelColumn::$integerSizes[$mysqlType];
        if ($unsigned) {
            if ($autoIncrement) {
                $this->type = lcfirst($size . 'Increments');
            } else {
                $this->type = 'unsigned' . $size . 'Integer';
            }
        } else {
            $this->type = lcfirst($size . 'Integer');
            if ($autoIncrement) {
                $this->autoIncrement();
            }
        }
        $this->typeOptions = [$column];
        if ($this->type == 'bigIncrements') {
            $this->type = 'id';
            if ($column == 'id') {
                $this->typeOptions = []; // idメソッドのデフォルトカラム名はid
            }
        }
    }

    /**
     * カラムの型を小数に設定する
     *
     * @param $mysqlType string float/double/real/decimalのいずれか
     * @param $column string カラム名
     * @param $total integer 桁数
     * @param $places integer 桁位置
     * @param $unsigned bool 符号なしか？
     */
    public function decimal(string $mysqlType, string $column, int $total, int $places, bool $unsigned)
    {
        assert(LaravelColumn::isDecimalType($mysqlType));
        if ($mysqlType == 'real') {
            $mysqlType = 'double';
        }
        if ($unsigned) {
            $this->type = 'unsigned' . ucfirst($mysqlType);
        } else {
            $this->type = $mysqlType;
        }
        if ($total == 8 && $places == 2) {
            $this->typeOptions = [strval($column)];
        } else if ($places == 2) {
            $this->typeOptions = [strval($column), intval($total)];
        } else {
            $this->typeOptions = [strval($column), intval($total), intval($places)];
        }
    }

    /**
     * カラムの型を文字列に設定する
     *
     * @param $mysqlType string char/varcharのいずれか
     * @param $column string カラム名
     * @param $length integer 長さ
     */
    public function string(string $mysqlType, string $column, int $length)
    {
        assert(LaravelColumn::isStringType($mysqlType));
        if ($mysqlType == 'char' && $length == 36) {
            $this->type = 'uuid';
            $this->typeOptions = [strval($column)];
        } else {
            $this->type = LaravelColumn::$laravelTypes[$mysqlType];
            if ($length == 255) {
                $this->typeOptions = [strval($column)];
            } else {
                $this->typeOptions = [strval($column), intval($length)];
            }
        }
    }

    /**
     * カラムの型を時間型に設定する
     *
     * @param $mysqlType string time/datetime/timestampのいずれか
     * @param $column string カラム名
     * @param $precision integer 精度
     */
    public function time(string $mysqlType, string $column, int $precision)
    {
        assert(LaravelColumn::isTimeType($mysqlType));
        $this->type = LaravelColumn::$laravelTypes[$mysqlType];
        if ($precision == 0) {
            $this->typeOptions = [strval($column)];
        } else {
            $this->typeOptions = [strval($column), intval($precision)];
        }
    }

    /**
     * カラムの型を列挙型に設定する
     *
     * @param $mysqlType string enum/setのいずれか
     * @param $column string カラム名
     * @param $enums array 選択肢
     */
    public function enum(string $mysqlType, string $column, array $enums)
    {
        assert(LaravelColumn::isEnumType($mysqlType));
        $this->type = LaravelColumn::$laravelTypes[$mysqlType];
        $this->typeOptions = [strval($column), $enums];
    }

    /**
     * カラムの型を設定する
     *
     * @param $mysqlType string 型の名前
     * @param $column string カラム名
     */
    public function type(string $mysqlType, string $column)
    {
        if (!LaravelColumn::isSupportedType($mysqlType)) {
            throw new \Error('Invalid type: ' . $mysqlType);
        }
        $this->type = LaravelColumn::$laravelTypes[$mysqlType];
        $this->typeOptions = [strval($column)];
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
     * 保存されない生成カラム
     */
    public function virtualAs(string $expression)
    {
        $this->registerMethodChain('virtualAs', [$expression]);
    }

    /**
     * 保存される生成カラム
     */
    public function storeAs(string $expression)
    {
        $this->registerMethodChain('storeAs', [$expression]);
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
        $args = [];//$indexName == NULL ? [] : [$indexName];
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
        $this->methodChains[$method] = $args;
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
        $writer->writeCall($this->type, $this->typeOptions);
        $writer->increaseIndent();
        foreach (LaravelColumn::$methodOrders as $method) {
            if (array_key_exists($method, $this->methodChains)) {
                $writer->nextLine();
                $this->writeCall($writer, $method, $this->methodChains[$method]);
            }
        }
        $writer->write(';');
        $writer->decreaseIndent();
    }

    private function writeCall($writer, $method, $args)
    {
        if ($this->isExpressionRequired($method)) {
            $exprArg = 'new Expression(' . PhpWriter::literal($args[0]) . ')';
            return $writer->writeCallRaw($method, [$exprArg]);
        } else if ($method == 'default') {
            return $writer->writeCallRaw($method, $args);
        } else {
            return $writer->writeCall($method, $args);
        }
    }

    private function isExpressionRequired($method)
    {
        return $method == 'virtualAs' || $method == 'storeAs';
    }
}