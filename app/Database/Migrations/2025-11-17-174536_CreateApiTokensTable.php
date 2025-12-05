<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateApiTokensTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'       => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id'     => [
                'type'    => 'BIGINT',
                'comment' => "user id of user"
            ],
            'token'     => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'unique'     => true   // ✅ UNIQUE COLUMN
            ],
            'expires_at datetime default null',
            'created_at datetime default current_timestamp',
            'updated_at datetime default current_timestamp on update current_timestamp',
            'deleted_at datetime default null'
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->createTable('api_tokens');
    }

    public function down()
    {
        $this->forge->dropTable('api_tokens');
    }
}
