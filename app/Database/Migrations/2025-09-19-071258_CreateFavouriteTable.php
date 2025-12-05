<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFavouriteTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'       => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'source_id'     => [
                'type'       => 'BIGINT',
                'comment'    => "id of the project,document,images"
            ],
            'source_type'     => [
                'type'       => 'INT',
                'comment'    => "source type like project, document or image. 1 for project, 2 for document and 3 for images"
            ],
            'user_id'     => [
                'type'       => 'BIGINT',
                'comment'    => "user id if user signed else will be null",
                'null'       => true
            ],
            'created_at datetime default current_timestamp',
            'updated_at datetime default current_timestamp on update current_timestamp',
            'deleted_at datetime default null'
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('source_id');
        $this->forge->createTable('tblfavorites');
    }

    public function down()
    {
        $this->forge->dropTable('tblfavorites');
    }
}
