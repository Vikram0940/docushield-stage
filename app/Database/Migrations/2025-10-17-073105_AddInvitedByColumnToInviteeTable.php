<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddInvitedByColumnToInviteeTable extends Migration
{
    public function up()
    {
        $fields = [
            'invited_by' => [
                'type'       => 'BIGINT',
                'after'      => 'user_id',
                'null'       => true,
                'default'    => null
            ]
        ];
        $this->forge->addColumn('tblinvitees', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('tblinvitees', ['invited_by']);
    }
}
