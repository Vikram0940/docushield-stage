<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBusinessTypeColumnToUserTable extends Migration
{
    public function up()
    {
        $fields = [
            'business_type' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'after'      => 'company_name',
                'null'       => true,
                'default'    => null
            ]
        ];
        $this->forge->addColumn('tblusers', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('tblusers', ['business_type']);
    }
}
