<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCommentToStorageTypeColumnInStorageTable extends Migration
{
    public function up()
    {
        $fields = [
            'source_type' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
                'comment'    => 'storage type 1 for document, 2 folder',
            ]
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
