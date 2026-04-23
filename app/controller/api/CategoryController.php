<?php
namespace app\controller\api;

use app\model\Category as CategoryModel;
use think\facade\Db;

class CategoryController extends BaseApiController
{
    public function list()
    {
        $categories = CategoryModel::where('status', 1)
            ->order('sort', 'asc')
            ->order('id', 'asc')
            ->select()
            ->toArray();
        
        $allCategory = [
            'id' => 0,
            'name' => '全部',
            'icon' => '',
            'color' => '#FF6B6B',
            'sort' => 0,
            'recipe_count' => 0
        ];
        
        foreach ($categories as &$category) {
            $category['recipe_count'] = Db::name('recipe')
                ->where('category_id', $category['id'])
                ->where('status', 1)
                ->count();
        }
        
        $allCategory['recipe_count'] = Db::name('recipe')->where('status', 1)->count();
        
        array_unshift($categories, $allCategory);
        
        return $this->success($categories);
    }
    
    public function detail()
    {
        $id = (int)$this->request->param('id', 0);
        
        if (!$id) {
            return $this->error('缺少分类ID');
        }
        
        $category = CategoryModel::where('id', $id)->where('status', 1)->find();
        
        if (!$category) {
            return $this->error('分类不存在');
        }
        
        $category = $category->toArray();
        $category['recipe_count'] = Db::name('recipe')
            ->where('category_id', $id)
            ->where('status', 1)
            ->count();
        
        return $this->success($category);
    }
    
    public function hot()
    {
        $categories = Db::name('category')
            ->alias('c')
            ->join('recipe r', 'c.id = r.category_id')
            ->where('c.status', 1)
            ->where('r.status', 1)
            ->field('c.id, c.name, c.icon, c.color, COUNT(r.id) as recipe_count')
            ->group('c.id')
            ->order('recipe_count', 'desc')
            ->limit(6)
            ->select()
            ->toArray();
        
        return $this->success($categories);
    }
}
