<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTokenColumnToUserTable extends Migration
{
    public function up()
    {
        $fields = [
            'token' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'after'      => 'updated_date',
                'null'       => true,
                'default'    => null
            ]
        ];
        $this->forge->addColumn('tblusers', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('tblusers', ['token']);
    }
}
