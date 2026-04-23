<?php
namespace app\controller\api;

use app\model\Recipe as RecipeModel;
use app\model\Category as CategoryModel;
use app\model\LikeRecord as LikeModel;
use app\model\Favorite as FavoriteModel;
use think\facade\Db;

class RecipeController extends BaseApiController
{
    public function daily()
    {
        $recipes = Db::name('recipe')
            ->alias('r')
            ->join('category c', 'r.category_id = c.id')
            ->where('r.is_daily', 1)
            ->where('r.status', 1)
            ->field('r.id, r.title, r.image, r.description, r.like_count, r.favorite_count, r.view_count, c.name as category_name, c.color as category_color')
            ->order('r.create_time', 'desc')
            ->limit(5)
            ->select()
            ->toArray();
        
        if (empty($recipes)) {
            $recipes = Db::name('recipe')
                ->alias('r')
                ->join('category c', 'r.category_id = c.id')
                ->where('r.status', 1)
                ->field('r.id, r.title, r.image, r.description, r.like_count, r.favorite_count, r.view_count, c.name as category_name, c.color as category_color')
                ->order('r.view_count', 'desc')
                ->limit(5)
                ->select()
                ->toArray();
        }
        
        return $this->success($recipes);
    }
    
    public function list()
    {
        $page = (int)$this->request->param('page', 1);
        $pageSize = (int)$this->request->param('page_size', 10);
        $categoryId = $this->request->param('category_id', 0);
        $keyword = $this->request->param('keyword', '');
        $orderBy = $this->request->param('order_by', 'new');
        
        $query = Db::name('recipe')
            ->alias('r')
            ->join('category c', 'r.category_id = c.id')
            ->join('user u', 'r.user_id = u.id')
            ->where('r.status', 1);
        
        if ($categoryId > 0) {
            $query->where('r.category_id', $categoryId);
        }
        
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('r.title', 'like', "%{$keyword}%")
                  ->whereOr('r.description', 'like', "%{$keyword}%")
                  ->whereOr('r.suitable_people', 'like', "%{$keyword}%");
            });
        }
        
        switch ($orderBy) {
            case 'hot':
                $query->order('r.view_count', 'desc');
                break;
            case 'like':
                $query->order('r.like_count', 'desc');
                break;
            case 'favorite':
                $query->order('r.favorite_count', 'desc');
                break;
            default:
                $query->order('r.create_time', 'desc');
        }
        
        $recipes = $query
            ->field('r.id, r.title, r.image, r.description, r.like_count, r.favorite_count, r.view_count, r.difficulty, r.cooking_time, c.name as category_name, c.color as category_color, u.nickname as author_name, u.avatar as author_avatar')
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
    
    public function detail()
    {
        $id = (int)$this->request->param('id', 0);
        
        if (!$id) {
            return $this->error('缺少菜谱ID');
        }
        
        $recipe = Db::name('recipe')
            ->alias('r')
            ->join('category c', 'r.category_id = c.id')
            ->join('user u', 'r.user_id = u.id')
            ->where('r.id', $id)
            ->where('r.status', 1)
            ->field('r.*, c.name as category_name, c.color as category_color, u.nickname as author_name, u.avatar as author_avatar')
            ->find();
        
        if (!$recipe) {
            return $this->error('菜谱不存在');
        }
        
        Db::name('recipe')->where('id', $id)->inc('view_count')->update();
        $recipe['view_count'] = (int)$recipe['view_count'] + 1;
        
        $recipe['materials'] = json_decode($recipe['materials'], true) ?: [];
        $recipe['steps'] = json_decode($recipe['steps'], true) ?: [];
        $recipe['images'] = json_decode($recipe['images'], true) ?: [];
        
        $isLiked = false;
        $isFavorited = false;
        
        if ($this->userId) {
            $isLiked = LikeModel::where('user_id', $this->userId)
                ->where('recipe_id', $id)
                ->find() ? true : false;
            
            $isFavorited = FavoriteModel::where('user_id', $this->userId)
                ->where('recipe_id', $id)
                ->find() ? true : false;
        }
        
        $recipe['is_liked'] = $isLiked;
        $recipe['is_favorited'] = $isFavorited;
        $recipe['difficulty_text'] = $this->getDifficultyText($recipe['difficulty']);
        
        return $this->success($recipe);
    }
    
    public function create()
    {
        $loginResult = $this->needLogin();
        if ($loginResult) return $loginResult;
        
        $title = $this->request->param('title', '');
        $image = $this->request->param('image', '');
        $images = $this->request->param('images', []);
        $description = $this->request->param('description', '');
        $categoryId = (int)$this->request->param('category_id', 0);
        $materials = $this->request->param('materials', []);
        $steps = $this->request->param('steps', []);
        $tips = $this->request->param('tips', '');
        $suitablePeople = $this->request->param('suitable_people', '');
        $cookingTime = $this->request->param('cooking_time', '');
        $difficulty = (int)$this->request->param('difficulty', 1);
        
        if (empty($title)) {
            return $this->error('请输入菜谱名称');
        }
        if (empty($image)) {
            return $this->error('请上传菜谱图片');
        }
        if (empty($categoryId)) {
            return $this->error('请选择分类');
        }
        
        $recipe = RecipeModel::create([
            'user_id' => $this->userId,
            'category_id' => $categoryId,
            'title' => $title,
            'image' => $image,
            'images' => is_string($images) ? $images : json_encode($images, JSON_UNESCAPED_UNICODE),
            'description' => $description,
            'materials' => is_string($materials) ? $materials : json_encode($materials, JSON_UNESCAPED_UNICODE),
            'steps' => is_string($steps) ? $steps : json_encode($steps, JSON_UNESCAPED_UNICODE),
            'tips' => $tips,
            'suitable_people' => $suitablePeople,
            'cooking_time' => $cookingTime,
            'difficulty' => $difficulty,
            'status' => 1,
        ]);
        
        return $this->success(['id' => $recipe->id], '发布成功');
    }
    
    public function update()
    {
        $loginResult = $this->needLogin();
        if ($loginResult) return $loginResult;
        
        $id = (int)$this->request->param('id', 0);
        
        if (!$id) {
            return $this->error('缺少菜谱ID');
        }
        
        $recipe = RecipeModel::where('id', $id)
            ->where('user_id', $this->userId)
            ->find();
        
        if (!$recipe) {
            return $this->error('菜谱不存在或无权修改');
        }
        
        $updateData = [];
        
        $fields = ['title', 'image', 'description', 'category_id', 'tips', 'suitable_people', 'cooking_time', 'difficulty'];
        foreach ($fields as $field) {
            $value = $this->request->param($field, '');
            if ($value !== '') {
                $updateData[$field] = $value;
            }
        }
        
        $images = $this->request->param('images', '');
        if ($images) {
            $updateData['images'] = is_string($images) ? $images : json_encode($images, JSON_UNESCAPED_UNICODE);
        }
        
        $materials = $this->request->param('materials', '');
        if ($materials) {
            $updateData['materials'] = is_string($materials) ? $materials : json_encode($materials, JSON_UNESCAPED_UNICODE);
        }
        
        $steps = $this->request->param('steps', '');
        if ($steps) {
            $updateData['steps'] = is_string($steps) ? $steps : json_encode($steps, JSON_UNESCAPED_UNICODE);
        }
        
        if (!empty($updateData)) {
            $recipe->save($updateData);
        }
        
        return $this->success([], '更新成功');
    }
    
    public function delete()
    {
        $loginResult = $this->needLogin();
        if ($loginResult) return $loginResult;
        
        $id = (int)$this->request->param('id', 0);
        
        if (!$id) {
            return $this->error('缺少菜谱ID');
        }
        
        $recipe = RecipeModel::where('id', $id)
            ->where('user_id', $this->userId)
            ->find();
        
        if (!$recipe) {
            return $this->error('菜谱不存在或无权删除');
        }
        
        $recipe->status = 2;
        $recipe->save();
        
        return $this->success([], '删除成功');
    }
    
    public function toggleLike()
    {
        $loginResult = $this->needLogin();
        if ($loginResult) return $loginResult;
        
        $recipeId = (int)$this->request->param('recipe_id', 0);
        
        if (!$recipeId) {
            return $this->error('缺少菜谱ID');
        }
        
        $recipe = RecipeModel::where('id', $recipeId)->where('status', 1)->find();
        if (!$recipe) {
            return $this->error('菜谱不存在');
        }
        
        $like = LikeModel::where('user_id', $this->userId)
            ->where('recipe_id', $recipeId)
            ->find();
        
        if ($like) {
            $like->delete();
            $recipe->like_count = max(0, $recipe->like_count - 1);
            $recipe->save();
            return $this->success(['is_liked' => false, 'like_count' => $recipe->like_count], '取消点赞');
        } else {
            LikeModel::create([
                'user_id' => $this->userId,
                'recipe_id' => $recipeId,
            ]);
            $recipe->like_count = $recipe->like_count + 1;
            $recipe->save();
            return $this->success(['is_liked' => true, 'like_count' => $recipe->like_count], '点赞成功');
        }
    }
    
    public function toggleFavorite()
    {
        $loginResult = $this->needLogin();
        if ($loginResult) return $loginResult;
        
        $recipeId = (int)$this->request->param('recipe_id', 0);
        
        if (!$recipeId) {
            return $this->error('缺少菜谱ID');
        }
        
        $recipe = RecipeModel::where('id', $recipeId)->where('status', 1)->find();
        if (!$recipe) {
            return $this->error('菜谱不存在');
        }
        
        $favorite = FavoriteModel::where('user_id', $this->userId)
            ->where('recipe_id', $recipeId)
            ->find();
        
        if ($favorite) {
            $favorite->delete();
            $recipe->favorite_count = max(0, $recipe->favorite_count - 1);
            $recipe->save();
            return $this->success(['is_favorited' => false, 'favorite_count' => $recipe->favorite_count], '取消收藏');
        } else {
            FavoriteModel::create([
                'user_id' => $this->userId,
                'recipe_id' => $recipeId,
            ]);
            $recipe->favorite_count = $recipe->favorite_count + 1;
            $recipe->save();
            return $this->success(['is_favorited' => true, 'favorite_count' => $recipe->favorite_count], '收藏成功');
        }
    }
    
    private function getDifficultyText($difficulty)
    {
        $texts = [1 => '简单', 2 => '中等', 3 => '困难'];
        return $texts[$difficulty] ?? '未知';
    }
}
