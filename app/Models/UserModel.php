<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table         = 'users';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['nama', 'username', 'password', 'role', 'guru_id', 'profile_photo'];
    protected $useTimestamps = true;
}
