<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCurrentSessionIdColumnToUsersTable extends Migration
{
    public function up()
    {
        $fields = [
            'current_session_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'after'      => 'token_expires_at',
                'default'    => null,
                'null'       => true
            ]
        ];
        $this->forge->addColumn('tblusers', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('tblusers', ['current_session_id']);
    }
}
