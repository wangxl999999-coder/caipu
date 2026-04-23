<?php
namespace app\model;

use think\Model;

class User extends Model
{
    protected $name = 'user';
    
    protected $pk = 'id';
    
    protected $autoWriteTimestamp = true;
    
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    protected $type = [
        'create_time' => 'timestamp',
        'update_time' => 'timestamp',
    ];
    
    protected $hidden = ['password', 'session_key'];
    
    public function recipes()
    {
        return $this->hasMany(Recipe::class, 'user_id', 'id');
    }
    
    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'user_id', 'id');
    }
    
    public function likes()
    {
        return $this->hasMany(LikeRecord::class, 'user_id', 'id');
    }
}
