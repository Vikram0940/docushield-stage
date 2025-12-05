<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUpdatedDateColumnToUsersTable extends Migration
{
    public function up()
    {
        $this->db->query("
            ALTER TABLE tblusers
            ADD COLUMN updated_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ");
    }

    public function down()
    {
        $this->forge->dropColumn('tblusers', ['updated_date']);
    }
}
