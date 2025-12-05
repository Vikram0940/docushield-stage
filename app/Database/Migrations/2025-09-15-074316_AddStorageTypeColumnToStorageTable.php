<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStorageTypeColumnToStorageTable extends Migration
{
    public function up()
    {
        $fields = [
            'storage_type' => [
                'type'       => 'INT',
                'after'      => 'chat_group_id',
                'comment'    => "1 for document,2 file",
                'null'       => true,
                'default'    => null
            ],
            'storage_path' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'after'      => 'storage_type',
                'null'       => true,
                'default'    => null
            ]
        ];
        $this->forge->addColumn('tblstorage', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('tblstorage', ['storage_type', 'storage_path']);
    }
}
