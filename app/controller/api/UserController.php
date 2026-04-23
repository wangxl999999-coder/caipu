<?php
namespace app\controller\api;

use app\model\User as UserModel;
use think\facade\Db;

class UserController extends BaseApiController
{
    public function login()
    {
        $code = $this->request->param('code', '');
        $nickname = $this->request->param('nickname', '');
        $avatar = $this->request->param('avatar', '');
        $gender = $this->request->param('gender', 0);
        
        if (empty($code)) {
            return $this->error('缺少必要参数');
        }
        
        $openid = 'mock_openid_' . md5($code . time());
        
        $user = UserModel::where('openid', $openid)->find();
        
        if ($user) {
            $user->last_login_time = time();
            if ($nickname) $user->nickname = $nickname;
            if ($avatar) $user->avatar = $avatar;
            $user->save();
        } else {
            $user = UserModel::create([
                'openid' => $openid,
                'nickname' => $nickname ?: '美食爱好者',
                'avatar' => $avatar ?: '',
                'gender' => $gender,
                'status' => 1,
            ]);
        }
        
        $userData = $user->toArray();
        unset($userData['session_key']);
        
        return $this->success([
            'user' => $userData,
            'token' => $openid
        ], '登录成功');
    }
    
    public function info()
    {
        $loginResult = $this->needLogin();
        if ($loginResult) return $loginResult;
        
        $user = UserModel::find($this->userId);
        if (!$user) {
            return $this->error('用户不存在');
        }
        
        $userData = $user->toArray();
        unset($userData['session_key']);
        
        $favoriteCount = Db::name('favorite')->where('user_id', $this->userId)->count();
        $recipeCount = Db::name('recipe')->where('user_id', $this->userId)->count();
        $likeCount = Db::name('like_record')->where('user_id', $this->userId)->count();
        
        $userData['favorite_count'] = $favoriteCount;
        $userData['recipe_count'] = $recipeCount;
        $userData['like_count'] = $likeCount;
        
        return $this->success($userData);
    }
    
    public function update()
    {
        $loginResult = $this->needLogin();
        if ($loginResult) return $loginResult;
        
        $nickname = $this->request->param('nickname', '');
        $avatar = $this->request->param('avatar', '');
        $phone = $this->request->param('phone', '');
        $gender = $this->request->param('gender', 0);
        
        $user = UserModel::find($this->userId);
        if (!$user) {
            return $this->error('用户不存在');
        }
        
        if ($nickname) $user->nickname = $nickname;
        if ($avatar) $user->avatar = $avatar;
        if ($phone) $user->phone = $phone;
        if ($gender) $user->gender = $gender;
        
        $user->save();
        
        $userData = $user->toArray();
        unset($userData['session_key']);
        
        return $this->success($userData, '更新成功');
    }
    
    public function myFavorites()
    {
        $loginResult = $this->needLogin();
        if ($loginResult) return $loginResult;
        
        $page = (int)$this->request->param('page', 1);
        $pageSize = (int)$this->request->param('page_size', 10);
        
        $favorites = Db::name('favorite')
            ->alias('f')
            ->join('recipe r', 'f.recipe_id = r.id')
            ->join('category c', 'r.category_id = c.id')
            ->where('f.user_id', $this->userId)
            ->where('r.status', 1)
            ->field('r.*, c.name as category_name, c.color as category_color')
            ->order('f.create_time', 'desc')
            ->paginate([
                'page' => $page,
                'list_rows' => $pageSize
            ]);
        
        return $this->success([
            'list' => $favorites->items(),
            'total' => $favorites->total(),
            'page' => $page,
            'page_size' => $pageSize
        ]);
    }
    
    public function myRecipes()
    {
        $loginResult = $this->needLogin();
        if ($loginResult) return $loginResult;
        
        $page = (int)$this->request->param('page', 1);
        $pageSize = (int)$this->request->param('page_size', 10);
        $status = $this->request->param('status', '');
        
        $query = Db::name('recipe')
            ->alias('r')
            ->join('category c', 'r.category_id = c.id')
            ->where('r.user_id', $this->userId);
        
        if ($status !== '') {
            $query->where('r.status', (int)$status);
        }
        
        $recipes = $query
            ->field('r.*, c.name as category_name, c.color as category_color')
            ->order('r.create_time', 'desc')
            ->paginate([
                'page' => $page,
                'list_rows' => $pageSize
            ]);
        
        return $this->success([
            'list' => $recipes->items(),
            'total' => $recipes->total(),
            'page' => $page,
            'page_size' => $pageSize
        ]);
    }
}
