<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSearchHistoryTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'       => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'keyword'     => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true
            ],
            'user_id'     => [
                'type'       => 'BIGINT',
                'comment'    => "user id of user searchig",
                'null'       => true
            ],
            'created_at datetime default current_timestamp',
            'updated_at datetime default current_timestamp on update current_timestamp',
            'deleted_at datetime default null'
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->createTable('tblsearch_history');
    }

    public function down()
    {
        $this->forge->dropTable('tblsearch_history');
    }
}
