<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProfilePhotos extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('profile_photo', 'users')) {
            $this->db->query('ALTER TABLE users ADD profile_photo VARCHAR(255) NULL AFTER guru_id');
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('profile_photo', 'users')) {
            $this->db->query('ALTER TABLE users DROP COLUMN profile_photo');
        }
    }
}
