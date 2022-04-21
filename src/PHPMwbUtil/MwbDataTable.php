<?php

namespace PHPMwbUtil;

class MwbDataTable
{
    public $name;
    public $columns;
    public $rows;

    public function load(\SQLite3 $db, string $tableName)
    {
        $this->name = $tableName;
        $this->columns = [];
        $this->rows = [];
        $result = $db->query("select * from '" . $tableName . "'");
        for ($i = 0; $i < $result->numColumns(); $i++) {
            $this->columns[] = $result->columnName($i);
        }
        while ($row = $result->fetchArray(SQLITE3_NUM)) {
            $this->rows[] = $row;
        }
        $result->finalize();
    }
}