<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEmailLogs extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'to'            => ['type' => 'TEXT'],
            'cc'            => ['type' => 'TEXT', 'null' => true],
            'bcc'           => ['type' => 'TEXT', 'null' => true],
            'reply_to'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'subject'       => ['type' => 'VARCHAR', 'constraint' => 255],
            'template'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status'        => ['type' => 'VARCHAR', 'constraint' => 20], // success|failed
            'error_message' => ['type' => 'TEXT', 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('email_logs');
    }

    public function down()
    {
        $this->forge->dropTable('email_logs');
    }
}
