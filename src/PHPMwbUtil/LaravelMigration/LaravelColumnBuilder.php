<?php

namespace PHPMwbUtil\LaravelMigration;

use PHPMwbUtil\MwbColumn;

class LaravelColumnBuilder
{
    // 整数値型のサイズトークン
    private static $integerSizes = [
        'tinyint' => 'Tiny',
        'smallint' => 'Small',
        'mediumint' => 'Medium',
        'int' => '',
        'bigint' => 'Big',
    ];

    // MySQL型からLaravel型への変換テーブル
    private static $laravelTypes = [
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

    /**
     * 指定されたMySQL型が整数型のカラムを判定する
     *
     * @param $mysqlType string MySQL型の名前
     * @return bool 整数型か?
     */
    private function isIntegerType($mysqlType)
    {
        return in_array($mysqlType, ['tinyint', 'smallint', 'mediumint', 'int', 'bigint']);
    }

    /**
     * 整数カラムを生成する
     *
     * @param $mysqlType string tinyint/smallint/mediumint/bigintのいずれか
     * @param $column string カラム名
     * @param $autoIncrement bool 自動増分か？
     * @param $unsigned bool 符号なしか？
     */
    private function createInteger(string $mysqlType, string $column, bool $autoIncrement, bool $unsigned)
    {
        $size = LaravelColumnBuilder::$integerSizes[$mysqlType];
        if ($unsigned) {
            if ($autoIncrement) {
                $type = lcfirst($size . 'Increments');
            } else {
                $type = 'unsigned' . $size . 'Integer';
            }
        } else {
            $type = lcfirst($size . 'Integer');
        }
        $args = [$column];
        if ($type == 'bigIncrements') {
            $type = 'id';
            if ($column == 'id') {
                $args = [];
            }
        } else if ($type == 'unsignedBigInteger' && substr($column, -3) == '_id') {
            $type = 'foreignId';
        }
        $laravelColumn = new LaravelColumn($type, $args);
        if ($autoIncrement && !$unsigned) {
            $laravelColumn->autoIncrement();
        }
        return $laravelColumn;
    }

    /**
     * 指定されたMySQL型が小数型のカラムを判定する
     *
     * @param $mysqlType string MySQL型の名前
     * @return 小数型か?
     */
    private function isDecimalType($mysqlType)
    {
        return in_array($mysqlType, ['float', 'double', 'decimal']);
    }

    /**
     * 小数カラムを生成する
     *
     * @param $mysqlType string float/double/real/decimalのいずれか
     * @param $column string カラム名
     * @param $total integer 桁数
     * @param $places integer 桁位置
     * @param $unsigned bool 符号なしか？
     */
    private function createDecimal(string $mysqlType, string $column, int $total, int $places, bool $unsigned)
    {
        if ($mysqlType == 'real') {
            $mysqlType = 'double';
        }
        if ($unsigned) {
            $type = 'unsigned' . ucfirst($mysqlType);
        } else {
            $type = $mysqlType;
        }
        if ($total == 8 && $places == 2) {
            $args = [strval($column)];
        } else if ($places == 2) {
            $args = [strval($column), intval($total)];
        } else {
            $args = [strval($column), intval($total), intval($places)];
        }
        return new LaravelColumn($type, $args);
    }

    /**
     * 指定されたMySQL型が文字列型のカラムを判定する
     *
     * @param $mysqlType string MySQL型の名前
     * @return 文字列型か?
     */
    private function isStringType($mysqlType)
    {
        return in_array($mysqlType, ['char', 'nchar', 'varchar', 'nvarchar']);
    }

    /**
     * 文字列カラムを生成する
     *
     * @param $mysqlType string char/varcharのいずれか
     * @param $column string カラム名
     * @param $length integer 長さ
     */
    private function createString(string $mysqlType, string $column, int $length)
    {
        if ($mysqlType == 'char' && $length == 36) {
            $type = 'uuid';
            $args = [strval($column)];
        } else {
            $type = LaravelColumnBuilder::$laravelTypes[$mysqlType];
            if ($length == 255) {
                $args = [strval($column)];
            } else {
                $args = [strval($column), intval($length)];
            }
        }
        return new LaravelColumn($type, $args);
    }

    /**
     * 指定されたMySQL型が時間型のカラムを判定する
     *
     * @param $mysqlType string MySQL型の名前
     * @return 時間型か?
     */
    private function isTimeType($mysqlType)
    {
        return in_array($mysqlType, ['datetime', 'datetime_f', 'time', 'time_f', 'timestamp', 'timestamp_f']);
    }

    /**
     * 時間型のカラムを生成する
     *
     * @param $mysqlType string time/datetime/timestampのいずれか
     * @param $column string カラム名
     * @param $precision integer 精度
     */
    private function createTime(string $mysqlType, string $column, int $precision)
    {
        $type = LaravelColumnBuilder::$laravelTypes[$mysqlType];
        if ($precision == 0) {
            $args = [strval($column)];
        } else {
            $args = [strval($column), intval($precision)];
        }
        return new LaravelColumn($type, $args);
    }

    /**
     * 指定されたMySQL型が列挙型のカラムを判定する
     *
     * @param $mysqlType string MySQL型の名前
     * @return 列挙型か?
     */
    private function isEnumType($mysqlType)
    {
        return in_array($mysqlType, ['enum', 'set']);
    }

    /**
     * 列挙型のカラムを生成する
     *
     * @param $mysqlType string enum/setのいずれか
     * @param $column string カラム名
     * @param $enums array 選択肢
     */
    private function createEnum(string $mysqlType, string $column, array $enums)
    {
        $type = LaravelColumnBuilder::$laravelTypes[$mysqlType];
        $args = [strval($column), $enums];
        return new LaravelColumn($type, $args);
    }

    /**
     * 指定されたMySQL型がサポートされているかを返す
     *
     * @param $mysqlType string MySQL型の名前
     * @return サポートされているか?
     */
    private function isSupportedType($mysqlType)
    {
        return array_key_exists($mysqlType, LaravelColumnBuilder::$laravelTypes);
    }

    /**
     * MySQL型を指定してカラムを生成する
     *
     * @param $mysqlType string 型の名前
     * @param $column string カラム名
     */
    private function createWithNoArgs(string $mysqlType, string $column)
    {
        $type = LaravelColumnBuilder::$laravelTypes[$mysqlType];
        $args = [strval($column)];
        return new LaravelColumn($type, $args);
    }

    /**
     * MMySQLWorkbenchのカラム型からLaravelのカラム型を生成する
     *
     * @param MwbColumn $mwbColumn MySQLWorkbenchのカラム型
     * @return LaravelColumn
     */
    public function create(MwbColumn $mwbColumn)
    {
        $mysqlType = $mwbColumn->mysqlType();
        if ($this->isIntegerType($mysqlType)) {
            return $this->createInteger(
                $mysqlType, $mwbColumn->name, $mwbColumn->autoIncrement, $mwbColumn->unsigned()
            );
        } else if ($this->isDecimalType($mysqlType)) {
            return $this->createDecimal(
                $mysqlType, $mwbColumn->name, $mwbColumn->precision, $mwbColumn->scale, $mwbColumn->unsigned()
            );
        } else if ($this->isStringType($mysqlType)) {
            return $this->createString($mysqlType, $mwbColumn->name, $mwbColumn->length);
        } else if ($this->isTimeType($mysqlType)) {
            return $this->createTime($mysqlType, $mwbColumn->name, $mwbColumn->length);
        } else if ($this->isEnumType($mysqlType)) {
            return $this->createEnum($mysqlType, $mwbColumn->name, $mwbColumn->enums());
        } else if ($this->isSupportedType($mysqlType)) {
            return $this->createWithNoArgs($mysqlType, $mwbColumn->name);
        } else {
            throw new \Error('Unsupported type: ' . $mysqlType);
        }
    }
}