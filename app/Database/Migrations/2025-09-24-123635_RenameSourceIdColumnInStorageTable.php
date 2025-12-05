<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameSourceIdColumnInStorageTable extends Migration
{
    public function up()
    {
        $fields = [
            'source_id' => [
                'name' => 'parent_id',   // new column name
                'type' => 'BIGINT',
                'null' => true
            ],
        ];

        $this->forge->modifyColumn('tblstorage', $fields);
    }

    public function down()
    {
        $fields = [
            'source_id' => [
                'type' => 'BIGINT',
                'null' => true
            ],
        ];

        $this->forge->modifyColumn('tblstorage', $fields);
    }
}
