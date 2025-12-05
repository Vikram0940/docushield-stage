<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVersionColumnToRevisionsTable extends Migration
{
    public function up()
    {
        $fields = [
            'version' => [
                'type'       => 'INT',
                'after'      => 'content',
                'default'    => 1
            ]
        ];
        $this->forge->addColumn('tblrevisions', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('tblrevisions', ['version']);
    }
}
