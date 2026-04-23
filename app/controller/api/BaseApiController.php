<?php
namespace app\controller\api;

use app\BaseController;
use think\facade\Db;
use think\response\Json;

class BaseApiController extends BaseController
{
    protected $user = null;
    protected $userId = 0;
    
    protected function initialize()
    {
        parent::initialize();
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, token');
        
        if ($this->request->method() == 'OPTIONS') {
            exit;
        }
        
        $token = $this->request->header('token', $this->request->param('token', ''));
        if ($token) {
            $this->checkToken($token);
        }
    }
    
    protected function checkToken($token)
    {
        $user = Db::name('user')->where('openid', $token)->find();
        if ($user) {
            $this->user = $user;
            $this->userId = $user['id'];
        }
    }
    
    protected function needLogin()
    {
        if (!$this->userId) {
            return json(['code' => 401, 'msg' => '请先登录', 'data' => []]);
        }
        return null;
    }
    
    protected function success($data = [], $msg = 'success'): Json
    {
        return json(['code' => 200, 'msg' => $msg, 'data' => $data]);
    }
    
    protected function error($msg = 'error', $code = 400, $data = []): Json
    {
        return json(['code' => $code, 'msg' => $msg, 'data' => $data]);
    }
}
