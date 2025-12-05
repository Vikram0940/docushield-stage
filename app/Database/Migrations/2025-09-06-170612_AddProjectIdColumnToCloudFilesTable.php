<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProjectIdColumnToCloudFilesTable extends Migration
{
    public function up()
    {
        $fields = [
            'project_id' => [
                'type'       => 'BIGINT',
                'after'      => 'file_size',
                'null'       => true,
                'default'    => null
            ]
        ];
        $this->forge->addColumn('tblcloudfiles', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('tblcloudfiles', ['project_id']);
    }
}
