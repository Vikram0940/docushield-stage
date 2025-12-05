<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropForeignKeyConstraintFromProjectTable extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $table = 'tblprojectcollaborators';
        $fkName = 'tblprojectcollaborators_project_id_foreign';

        // Check if foreign key exists
        $query = $db->query("
            SELECT CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND CONSTRAINT_NAME = ?
        ", [$table, $fkName]);

        if ($query->getNumRows() > 0) {
            $this->forge->dropForeignKey($table, $fkName);
        }
    }

    public function down()
    {
        // Recreate the foreign key if rolled back
        $this->forge->addForeignKey('project_id', 'tblprojects', 'id', 'CASCADE', 'CASCADE');
        $this->forge->processIndexes('tblprojectcollaborators');
    }
}
