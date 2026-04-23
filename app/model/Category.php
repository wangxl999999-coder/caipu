<?php
namespace app\model;

use think\Model;

class Category extends Model
{
    protected $name = 'category';
    
    protected $pk = 'id';
    
    protected $autoWriteTimestamp = true;
    
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    protected $type = [
        'create_time' => 'timestamp',
        'update_time' => 'timestamp',
    ];
    
    public function recipes()
    {
        return $this->hasMany(Recipe::class, 'category_id', 'id');
    }
}
