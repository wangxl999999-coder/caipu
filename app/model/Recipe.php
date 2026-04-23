<?php
namespace app\model;

use think\Model;

class Recipe extends Model
{
    protected $name = 'recipe';
    
    protected $pk = 'id';
    
    protected $autoWriteTimestamp = true;
    
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    protected $type = [
        'create_time' => 'timestamp',
        'update_time' => 'timestamp',
        'materials' => 'json',
        'steps' => 'json',
        'images' => 'json',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
    
    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'recipe_id', 'id');
    }
    
    public function likes()
    {
        return $this->hasMany(LikeRecord::class, 'recipe_id', 'id');
    }
    
    public function getDifficultyTextAttr($value, $data)
    {
        $difficulty = [1 => '简单', 2 => '中等', 3 => '困难'];
        return $difficulty[$data['difficulty']] ?? '未知';
    }
}
