<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInviteeTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'       => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'email'     => [
                'type'       => 'VARCHAR',
                'constraint' => '255'
            ],
            'source_id'     => [
                'type'       => 'BIGINT',
                'comment'    => "id of the project,document,images"
            ],
            'token'     => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true
            ],
            'user_id'     => [
                'type'       => 'BIGINT',
                'comment'    => "user id if user signed else will be null",
                'null'       => true
            ],
            'role'     => [
                'type'       => 'INT',
                'comment'    => "role on the project,document,images 1 for view, 2 for edit"
            ],
            'source_type'     => [
                'type'       => 'INT',
                'comment'    => "source type like project, document or image. 1 for project, 2 for document and 3 for images"
            ],
            'created_at datetime default current_timestamp',
            'updated_at datetime default current_timestamp on update current_timestamp',
            'deleted_at datetime default null'
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('source_id');
        $this->forge->createTable('tblinvitees');
    }

    public function down()
    {
        $this->forge->dropTable('tblinvitees');
    }
}
