<?php
namespace app\controller\admin;

use app\model\Recipe as RecipeModel;
use app\model\Category as CategoryModel;
use think\facade\Db;
use think\facade\View;

class RecipeController extends BaseAdminController
{
    public function index()
    {
        $page = (int)$this->request->param('page', 1);
        $pageSize = 15;
        $keyword = $this->request->param('keyword', '');
        $categoryId = (int)$this->request->param('category_id', 0);
        $status = $this->request->param('status', -1);
        $isDaily = $this->request->param('is_daily', -1);
        
        $query = Db::name('recipe')
            ->alias('r')
            ->join('user u', 'r.user_id = u.id')
            ->join('category c', 'r.category_id = c.id')
            ->order('r.create_time', 'desc');
        
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('r.title', 'like', "%{$keyword}%")
                  ->whereOr('r.description', 'like', "%{$keyword}%")
                  ->whereOr('u.nickname', 'like', "%{$keyword}%");
            });
        }
        
        if ($categoryId > 0) {
            $query->where('r.category_id', $categoryId);
        }
        
        if ($status >= 0) {
            $query->where('r.status', $status);
        }
        
        if ($isDaily >= 0) {
            $query->where('r.is_daily', $isDaily);
        }
        
        $recipes = $query
            ->field('r.*, u.nickname as author, c.name as category_name')
            ->paginate([
                'page' => $page,
                'list_rows' => $pageSize,
                'query' => $this->request->param()
            ]);
        
        $categories = CategoryModel::where('status', 1)->order('sort', 'asc')->select()->toArray();
        
        View::assign('recipes', $recipes);
        View::assign('categories', $categories);
        View::assign('keyword', $keyword);
        View::assign('category_id', $categoryId);
        View::assign('status', $status);
        View::assign('is_daily', $isDaily);
        
        return View::fetch();
    }
    
    public function detail()
    {
        $id = (int)$this->request->param('id', 0);
        
        if (!$id) {
            return $this->error('参数错误');
        }
        
        $recipe = Db::name('recipe')
            ->alias('r')
            ->join('user u', 'r.user_id = u.id')
            ->join('category c', 'r.category_id = c.id')
            ->where('r.id', $id)
            ->field('r.*, u.nickname as author, u.avatar as author_avatar, c.name as category_name')
            ->find();
        
        if (!$recipe) {
            return $this->error('菜谱不存在');
        }
        
        $recipe['materials'] = json_decode($recipe['materials'], true) ?: [];
        $recipe['steps'] = json_decode($recipe['steps'], true) ?: [];
        $recipe['images'] = json_decode($recipe['images'], true) ?: [];
        
        $recipe['difficulty_text'] = $this->getDifficultyText($recipe['difficulty']);
        
        View::assign('recipe', $recipe);
        
        return View::fetch();
    }
    
    public function status()
    {
        $id = (int)$this->request->param('id', 0);
        $status = (int)$this->request->param('status', 0);
        
        if (!$id) {
            return $this->error('参数错误');
        }
        
        $recipe = RecipeModel::find($id);
        if (!$recipe) {
            return $this->error('菜谱不存在');
        }
        
        $recipe->status = $status;
        $recipe->save();
        
        $statusText = [0 => '待审核', 1 => '已发布', 2 => '已下架'];
        return $this->success([], '状态已修改为：' . $statusText[$status]);
    }
    
    public function setDaily()
    {
        $id = (int)$this->request->param('id', 0);
        $isDaily = (int)$this->request->param('is_daily', 0);
        
        if (!$id) {
            return $this->error('参数错误');
        }
        
        $recipe = RecipeModel::find($id);
        if (!$recipe) {
            return $this->error('菜谱不存在');
        }
        
        $recipe->is_daily = $isDaily;
        $recipe->save();
        
        return $this->success([], $isDaily ? '已设置为每日推荐' : '已取消每日推荐');
    }
    
    public function delete()
    {
        $id = (int)$this->request->param('id', 0);
        
        if (!$id) {
            return $this->error('参数错误');
        }
        
        $recipe = RecipeModel::find($id);
        if (!$recipe) {
            return $this->error('菜谱不存在');
        }
        
        Db::startTrans();
        try {
            Db::name('favorite')->where('recipe_id', $id)->delete();
            Db::name('like_record')->where('recipe_id', $id)->delete();
            $recipe->delete();
            
            Db::commit();
            return $this->success([], '删除成功');
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error('删除失败：' . $e->getMessage());
        }
    }
    
    public function batchSetDaily()
    {
        $ids = $this->request->param('ids', []);
        $isDaily = (int)$this->request->param('is_daily', 0);
        
        if (empty($ids)) {
            return $this->error('请选择要操作的菜谱');
        }
        
        RecipeModel::whereIn('id', $ids)->update(['is_daily' => $isDaily]);
        
        return $this->success([], $isDaily ? '批量设置每日推荐成功' : '批量取消每日推荐成功');
    }
    
    private function getDifficultyText($difficulty)
    {
        $texts = [1 => '简单', 2 => '中等', 3 => '困难'];
        return $texts[$difficulty] ?? '未知';
    }
}
