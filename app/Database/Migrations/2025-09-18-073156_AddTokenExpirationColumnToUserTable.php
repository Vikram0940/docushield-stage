<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTokenExpirationColumnToUserTable extends Migration
{
    public function up()
    {
        $fields = [
            'token_expires_at' => [
                'type'       => 'DATETIME',
                'after'      => 'token',
                'null'       => true,
                'default'    => null
            ]
        ];
        $this->forge->addColumn('tblusers', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('tblusers', ['token_expires_at']);
    }
}
