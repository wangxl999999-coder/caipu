<?php
namespace app\controller\admin;

use app\model\Category as CategoryModel;
use think\facade\Db;
use think\facade\View;

class CategoryController extends BaseAdminController
{
    public function index()
    {
        $categories = CategoryModel::order('sort', 'asc')
            ->order('id', 'asc')
            ->select()
            ->toArray();
        
        foreach ($categories as &$category) {
            $category['recipe_count'] = Db::name('recipe')
                ->where('category_id', $category['id'])
                ->count();
        }
        
        View::assign('categories', $categories);
        
        return View::fetch();
    }
    
    public function add()
    {
        if ($this->request->isPost()) {
            $name = $this->request->param('name', '');
            $icon = $this->request->param('icon', '');
            $color = $this->request->param('color', '#FF6B6B');
            $sort = (int)$this->request->param('sort', 0);
            
            if (empty($name)) {
                return $this->error('请输入分类名称');
            }
            
            $exists = CategoryModel::where('name', $name)->find();
            if ($exists) {
                return $this->error('分类名称已存在');
            }
            
            CategoryModel::create([
                'name' => $name,
                'icon' => $icon,
                'color' => $color,
                'sort' => $sort,
                'status' => 1,
            ]);
            
            return $this->success(['redirect' => (string)url('/admin/category/index')], '添加成功');
        }
        
        return View::fetch();
    }
    
    public function edit()
    {
        $id = (int)$this->request->param('id', 0);
        
        if (!$id) {
            return $this->error('参数错误');
        }
        
        $category = CategoryModel::find($id);
        if (!$category) {
            return $this->error('分类不存在');
        }
        
        if ($this->request->isPost()) {
            $name = $this->request->param('name', '');
            $icon = $this->request->param('icon', '');
            $color = $this->request->param('color', '#FF6B6B');
            $sort = (int)$this->request->param('sort', 0);
            
            if (empty($name)) {
                return $this->error('请输入分类名称');
            }
            
            $exists = CategoryModel::where('name', $name)->where('id', '<>', $id)->find();
            if ($exists) {
                return $this->error('分类名称已存在');
            }
            
            $category->name = $name;
            $category->icon = $icon;
            $category->color = $color;
            $category->sort = $sort;
            $category->save();
            
            return $this->success([], '修改成功');
        }
        
        View::assign('category', $category);
        
        return View::fetch();
    }
    
    public function status()
    {
        $id = (int)$this->request->param('id', 0);
        $status = (int)$this->request->param('status', 0);
        
        if (!$id) {
            return $this->error('参数错误');
        }
        
        $category = CategoryModel::find($id);
        if (!$category) {
            return $this->error('分类不存在');
        }
        
        $category->status = $status;
        $category->save();
        
        return $this->success([], $status == 1 ? '分类已显示' : '分类已隐藏');
    }
    
    public function delete()
    {
        $id = (int)$this->request->param('id', 0);
        
        if (!$id) {
            return $this->error('参数错误');
        }
        
        $category = CategoryModel::find($id);
        if (!$category) {
            return $this->error('分类不存在');
        }
        
        $recipeCount = Db::name('recipe')->where('category_id', $id)->count();
        if ($recipeCount > 0) {
            return $this->error('该分类下还有菜谱，无法删除');
        }
        
        $category->delete();
        
        return $this->success([], '删除成功');
    }
    
    public function sort()
    {
        $id = (int)$this->request->param('id', 0);
        $sort = (int)$this->request->param('sort', 0);
        
        if (!$id) {
            return $this->error('参数错误');
        }
        
        $category = CategoryModel::find($id);
        if (!$category) {
            return $this->error('分类不存在');
        }
        
        $category->sort = $sort;
        $category->save();
        
        return $this->success([], '排序成功');
    }
}
