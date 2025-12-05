<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameSourceTypeColumnInStorageTable extends Migration
{
    public function up()
    {
        $fields = [
            'source_type' => [
                'name'       => 'parent_type',   // new column name
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true
            ],
        ];

        $this->forge->modifyColumn('tblstorage', $fields);
    }

    public function down()
    {
        $fields = [
            'source_type' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true
            ],
        ];

        $this->forge->modifyColumn('tblstorage', $fields);
    }
}
