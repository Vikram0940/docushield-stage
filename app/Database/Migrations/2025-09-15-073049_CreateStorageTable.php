<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStorageTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'title'       => [
                'type'       => 'VARCHAR',
                'constraint' => '255'
            ],
            'content'     => [
                'type'       => 'LONGTEXT'
            ],
            'source_id'   => [
                'type'       => 'BIGINT',
                'null'       => true
            ],
            'source_type' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true
            ],
            'user_id'     => [
                'type'       => 'BIGINT',
                'comment'    => "user who created the file",
                'null'       => true
            ],
            'status'      => [
                'type'       => 'INT',
                'comment'    => "1 for published,2 for completed,3 for cancelled"
            ],
            'template' => [
                'type'       => 'LONGTEXT',
                'default'    => null,
                'null'       => true
            ],
            'bs_response' => [
                'type'       => 'LONGTEXT',
                'default'    => null,
                'null'       => true
            ],
            'bs_data'     => [
                'type'       => 'LONGTEXT',
                'default'    => null,
                'null'       => true
            ],
            'chat_group_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true
            ],
            'created_at datetime default current_timestamp',
            'updated_at datetime default current_timestamp on update current_timestamp',
            'deleted_at datetime default null'
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('source_id');
        $this->forge->createTable('tblstorage');
    }

    public function down()
    {
        $this->forge->dropTable('tblstorage');
    }
}
