<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFileTypeAndSizeColumnsToStorageTable extends Migration
{
    public function up()
    {
        $fields = [
            'file_mime_type' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'after'      => 'storage_path',
                'null'       => true,
                'default'    => null
            ],
            'file_size' => [
                'type'       => 'BIGINT',
                'after'      => 'file_mime_type',
                'null'       => true,
                'default'    => null
            ]
        ];
        $this->forge->addColumn('tblstorage', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('tblstorage', ['file_mime_type','file_size']);
    }
}
