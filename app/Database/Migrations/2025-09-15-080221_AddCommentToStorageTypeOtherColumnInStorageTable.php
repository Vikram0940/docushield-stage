<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCommentToStorageTypeOtherColumnInStorageTable extends Migration
{
    public function up()
    {
        $fields = [
            'storage_type' => [
                'type'       => 'INT',
                'null'       => true,
                'default'    => null,
                'comment'    => '1 for document,2 for file and 3 for images',
            ]
        ];

        $this->forge->modifyColumn('tblstorage', $fields);
    }

    public function down()
    {
        $fields = [
            'storage_type' => [
                'type'       => 'INT',
                'comment'    => "1 for document,2 file",
                'null'       => true,
                'default'    => null
            ],
        ];

        $this->forge->modifyColumn('tblstorage', $fields);
    }
}
