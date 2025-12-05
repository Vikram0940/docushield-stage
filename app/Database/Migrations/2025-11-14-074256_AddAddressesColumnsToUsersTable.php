<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAddressesColumnsToUsersTable extends Migration
{
    public function up()
    {
        $fields = [
            'country' => [
                'type'       => 'INT',
                'after'      => 'address',
                'default'    => null,
                'null'       => true
            ],
            'state' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'after'      => 'country',
                'default'    => null,
                'null'       => true
            ],
            'city' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'after'      => 'state',
                'default'    => null,
                'null'       => true
            ],
            'postal_code' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'after'      => 'city',
                'default'    => null,
                'null'       => true
            ]
        ];
        $this->forge->addColumn('tblusers', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('tblusers', ['country', 'state', 'city', 'postal_code']);
    }
}
