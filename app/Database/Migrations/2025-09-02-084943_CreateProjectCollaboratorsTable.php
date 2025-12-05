<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProjectCollaboratorsTable extends Migration
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
            'project_id'     => [
                'type'       => 'BIGINT',
                'comment'    => "id of the project"
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
                'comment'    => "role on the project 1 for view, 2 for edit"
            ],
            'created_at datetime default current_timestamp',
            'updated_at datetime default current_timestamp on update current_timestamp',
            'deleted_at datetime default null'
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('project_id');
        // Foreign key (project_id → tblprojects.id)
        //$this->forge->addForeignKey('project_id', 'tblprojects', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tblprojectcollaborators');
    }

    public function down()
    {
        $this->forge->dropTable('tblprojectcollaborators');
    }
}
