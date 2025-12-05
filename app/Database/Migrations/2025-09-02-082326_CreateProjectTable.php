<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProjectTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'       => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name'     => [
                'type'       => 'VARCHAR',
                'constraint' => '255'
            ],
            'case_id'     => [
                'type'       => 'BIGINT',
                'comment'    => "unique id of the project"
            ],
            'description'     => [
                'type'       => 'LONGTEXT',
                'null'       => true,
                'default'    => null
            ],
            'user_id'     => [
                'type'       => 'BIGINT',
                'comment'    => "who created the project"
            ],
            'status'     => [
                'type'       => 'INT',
                'comment'    => "status of the project 1 for active, 0 for inactive"
            ],
            'created_at datetime default current_timestamp',
            'updated_at datetime default current_timestamp on update current_timestamp',
            'deleted_at datetime default null'
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('case_id');
        $this->forge->addKey('user_id');
        $this->forge->createTable('tblprojects');
    }

    public function down()
    {
        $this->forge->dropTable('tblprojects');
    }
}
