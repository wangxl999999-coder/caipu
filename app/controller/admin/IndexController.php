<?php
namespace app\controller\admin;

use think\facade\Db;
use think\facade\View;

class IndexController extends BaseAdminController
{
    public function index()
    {
        $todayStart = strtotime(date('Y-m-d 00:00:00'));
        $todayEnd = strtotime(date('Y-m-d 23:59:59'));
        
        $statistics = [
            'user_total' => Db::name('user')->count(),
            'user_today' => Db::name('user')->whereBetween('create_time', [$todayStart, $todayEnd])->count(),
            'recipe_total' => Db::name('recipe')->count(),
            'recipe_today' => Db::name('recipe')->whereBetween('create_time', [$todayStart, $todayEnd])->count(),
            'recipe_pending' => Db::name('recipe')->where('status', 0)->count(),
            'category_total' => Db::name('category')->count(),
            'view_total' => Db::name('recipe')->sum('view_count'),
            'like_total' => Db::name('recipe')->sum('like_count'),
            'favorite_total' => Db::name('recipe')->sum('favorite_count'),
        ];
        
        $recentRecipes = Db::name('recipe')
            ->alias('r')
            ->join('user u', 'r.user_id = u.id')
            ->join('category c', 'r.category_id = c.id')
            ->field('r.id, r.title, r.image, r.status, r.create_time, r.view_count, r.like_count, u.nickname as author, c.name as category')
            ->order('r.create_time', 'desc')
            ->limit(10)
            ->select()
            ->toArray();
        
        $recentUsers = Db::name('user')
            ->field('id, nickname, avatar, phone, status, create_time')
            ->order('create_time', 'desc')
            ->limit(10)
            ->select()
            ->toArray();
        
        $categoryStats = Db::name('category')
            ->alias('c')
            ->leftJoin('recipe r', 'c.id = r.category_id')
            ->where('c.status', 1)
            ->field('c.id, c.name, c.color, COUNT(r.id) as recipe_count')
            ->group('c.id')
            ->order('recipe_count', 'desc')
            ->limit(10)
            ->select()
            ->toArray();
        
        View::assign('statistics', $statistics);
        View::assign('recentRecipes', $recentRecipes);
        View::assign('recentUsers', $recentUsers);
        View::assign('categoryStats', $categoryStats);
        
        return View::fetch();
    }
    
    public function welcome()
    {
        $todayStart = strtotime(date('Y-m-d 00:00:00'));
        
        $charts = [
            'recent_7_days' => $this->getRecent7DaysStats(),
            'category_distribution' => $this->getCategoryDistribution(),
        ];
        
        View::assign('charts', $charts);
        
        return View::fetch();
    }
    
    private function getRecent7DaysStats()
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} day"));
            $start = strtotime($date . ' 00:00:00');
            $end = strtotime($date . ' 23:59:59');
            
            $data[] = [
                'date' => $date,
                'users' => Db::name('user')->whereBetween('create_time', [$start, $end])->count(),
                'recipes' => Db::name('recipe')->whereBetween('create_time', [$start, $end])->count(),
                'views' => Db::name('recipe')->whereBetween('create_time', [$start, $end])->sum('view_count'),
            ];
        }
        return $data;
    }
    
    private function getCategoryDistribution()
    {
        return Db::name('category')
            ->alias('c')
            ->leftJoin('recipe r', 'c.id = r.category_id')
            ->where('c.status', 1)
            ->field('c.name, c.color, COUNT(r.id) as value')
            ->group('c.id')
            ->order('value', 'desc')
            ->select()
            ->toArray();
    }
}
