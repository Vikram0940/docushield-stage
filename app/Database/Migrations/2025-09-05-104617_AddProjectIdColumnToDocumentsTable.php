<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProjectIdColumnToDocumentsTable extends Migration
{
    public function up()
    {
        $fields = [
            'project_id' => [
                'type'       => 'BIGINT',
                'after'      => 'bs_data',
                'null'       => true,
                'default'    => null
            ]
        ];
        $this->forge->addColumn('tbldocuments', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('tbldocuments', ['project_id']);
    }
}
