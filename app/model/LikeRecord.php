<?php
namespace app\model;

use think\Model;

class LikeRecord extends Model
{
    protected $name = 'like_record';
    
    protected $pk = 'id';
    
    protected $autoWriteTimestamp = true;
    
    protected $createTime = 'create_time';
    protected $updateTime = false;
    
    protected $type = [
        'create_time' => 'timestamp',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    public function recipe()
    {
        return $this->belongsTo(Recipe::class, 'recipe_id', 'id');
    }
}
