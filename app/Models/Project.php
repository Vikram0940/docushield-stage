<?php

namespace App\Models;

use CodeIgniter\Model;

class Project extends Model
{
    protected $table            = 'tblprojects';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = ["name","description","case_id","user_id","status"];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Generate a unique 6-digit case id using recursion with safeguard
     *
     * @param int $attempt
     * @param int $maxAttempts
     * @return string
     */
    public function generateCaseId(int $attempt = 0, int $maxAttempts = 10): string
    {
        // Prevent infinite recursion
        if ($attempt >= $maxAttempts) {
            throw new \RuntimeException('Failed to generate a unique case_id after ' . $maxAttempts . ' attempts.');
        }

        // Generate a random 6-digit number (leading zeros preserved)
        $case_id = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        // Check if it already exists in DB
        $exists = $this->where('case_id', $case_id)->first();

        if ($exists) {
            // Retry recursively with attempt incremented
            return $this->generateCaseId($attempt + 1, $maxAttempts);
        }

        return $case_id;
    }

    /**
     * Getting count of documents, files and images
     *
     * @param int $project_id
     * @param int $file_type
     * @return int
     */
    public function getCount(int $project_id, int $file_type = 0): int
    {
        if ($file_type == 0) {
            return $this->db->table('tblstorage')
                        ->where('parent_id', $project_id)
                        ->where('deleted_at IS NULL')
                        ->countAllResults();
        }
        else {
            return $this->db->table('tblstorage')
                        ->where('parent_id', $project_id)
                        ->where('parent_type', 1)
                        ->where('storage_type', $file_type)
                        ->where('deleted_at IS NULL')
                        ->countAllResults();
        }
    }
}
