<?php

namespace PHPMwbUtil;

class MwbData
{
    public $tables;

    /**
     * SQLite3データベースをバイナリデータで渡して、全データを読み込む
     *
     * @param string $bin
     * @return void
     */
    public function load(string $bin)
    {
        $db = $this->sqliteOpen($bin);
        $tableNames = $this->sqliteTables($db);
        $this->tables = [];
        foreach ($tableNames as $tableName) {
            $table = new MwbDataTable();
            $table->load($db, $tableName);
            $this->tables[] = $table;
        }
    }

    /**
     * SQLite3データベースをバイナリデータを開く
     *
     * @param $bin
     * @return \SQLite3
     */
    private function sqliteOpen(string $bin)
    {
        $fp = tmpfile();
        fwrite($fp, $bin);
        fflush($fp);
        return new \SQLite3(stream_get_meta_data($fp)['uri']);
    }

    /**
     * SQLite3データベースのテーブル名を取得する
     *
     * @param string $db
     * @return void
     */
    private function sqliteTables(\SQLite3 $db): array
    {
        $tables = [];
        $result = $db->query("select name from sqlite_master where type='table'");
        while ($row = $result->fetchArray()) {
            if ($row['name'] != 'XP_PROC') {
                $tables[] = $row['name'];
            }
        }
        $result->finalize();
        return $tables;
    }
}