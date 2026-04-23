<?php
namespace app\controller\admin;

use app\model\User as UserModel;
use think\facade\Db;
use think\facade\View;

class UserController extends BaseAdminController
{
    public function index()
    {
        $page = (int)$this->request->param('page', 1);
        $pageSize = 15;
        $keyword = $this->request->param('keyword', '');
        $status = $this->request->param('status', -1);
        
        $query = UserModel::order('create_time', 'desc');
        
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('nickname', 'like', "%{$keyword}%")
                  ->whereOr('phone', 'like', "%{$keyword}%")
                  ->whereOr('openid', 'like', "%{$keyword}%");
            });
        }
        
        if ($status >= 0) {
            $query->where('status', $status);
        }
        
        $users = $query->paginate([
            'page' => $page,
            'list_rows' => $pageSize,
            'query' => $this->request->param()
        ]);
        
        View::assign('users', $users);
        View::assign('keyword', $keyword);
        View::assign('status', $status);
        
        return View::fetch();
    }
    
    public function detail()
    {
        $id = (int)$this->request->param('id', 0);
        
        if (!$id) {
            return $this->error('参数错误');
        }
        
        $user = UserModel::find($id);
        if (!$user) {
            return $this->error('用户不存在');
        }
        
        $statistics = [
            'recipe_count' => Db::name('recipe')->where('user_id', $id)->count(),
            'favorite_count' => Db::name('favorite')->where('user_id', $id)->count(),
            'like_count' => Db::name('like_record')->where('user_id', $id)->count(),
        ];
        
        $recentRecipes = Db::name('recipe')
            ->alias('r')
            ->join('category c', 'r.category_id = c.id')
            ->where('r.user_id', $id)
            ->field('r.id, r.title, r.image, r.status, r.create_time, r.view_count, c.name as category')
            ->order('r.create_time', 'desc')
            ->limit(10)
            ->select()
            ->toArray();
        
        View::assign('user', $user);
        View::assign('statistics', $statistics);
        View::assign('recentRecipes', $recentRecipes);
        
        return View::fetch();
    }
    
    public function status()
    {
        $id = (int)$this->request->param('id', 0);
        $status = (int)$this->request->param('status', 0);
        
        if (!$id) {
            return $this->error('参数错误');
        }
        
        $user = UserModel::find($id);
        if (!$user) {
            return $this->error('用户不存在');
        }
        
        $user->status = $status;
        $user->save();
        
        return $this->success([], $status == 1 ? '用户已启用' : '用户已禁用');
    }
    
    public function delete()
    {
        $id = (int)$this->request->param('id', 0);
        
        if (!$id) {
            return $this->error('参数错误');
        }
        
        $user = UserModel::find($id);
        if (!$user) {
            return $this->error('用户不存在');
        }
        
        Db::startTrans();
        try {
            Db::name('recipe')->where('user_id', $id)->update(['status' => 2]);
            Db::name('favorite')->where('user_id', $id)->delete();
            Db::name('like_record')->where('user_id', $id)->delete();
            $user->delete();
            
            Db::commit();
            return $this->success([], '删除成功');
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error('删除失败：' . $e->getMessage());
        }
    }
}
