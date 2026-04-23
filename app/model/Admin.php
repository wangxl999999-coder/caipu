<?php
namespace app\model;

use think\Model;

class Admin extends Model
{
    protected $name = 'admin';
    
    protected $pk = 'id';
    
    protected $autoWriteTimestamp = true;
    
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    protected $type = [
        'create_time' => 'timestamp',
        'update_time' => 'timestamp',
        'last_login_time' => 'timestamp',
    ];
}
