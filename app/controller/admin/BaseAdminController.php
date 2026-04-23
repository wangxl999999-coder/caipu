<?php
namespace app\controller\admin;

use app\BaseController;
use think\facade\Session;
use think\facade\View;
use think\response\Json;

class BaseAdminController extends BaseController
{
    protected $admin = null;
    protected $adminId = 0;
    
    protected $noNeedLogin = [];
    
    protected function initialize()
    {
        parent::initialize();
        
        $controller = strtolower($this->request->controller());
        $action = strtolower($this->request->action());
        
        $noNeedLogin = array_map('strtolower', $this->noNeedLogin);
        if (in_array("{$controller}/{$action}", $noNeedLogin) || in_array($action, $noNeedLogin)) {
            return;
        }
        
        $this->checkLogin();
    }
    
    protected function checkLogin()
    {
        $adminId = Session::get('admin_id');
        if (!$adminId) {
            if ($this->request->isAjax()) {
                return json(['code' => 401, 'msg' => '请先登录', 'data' => []])->send();
            }
            return redirect(url('/admin/login'))->send();
        }
        
        $admin = \app\model\Admin::find($adminId);
        if (!$admin || $admin->status != 1) {
            Session::delete('admin_id');
            Session::delete('admin_info');
            if ($this->request->isAjax()) {
                return json(['code' => 401, 'msg' => '登录已过期', 'data' => []])->send();
            }
            return redirect(url('/admin/login'))->send();
        }
        
        $this->admin = $admin;
        $this->adminId = $adminId;
        
        View::assign('admin', $admin);
    }
    
    protected function success($data = [], $msg = '操作成功'): Json
    {
        return json(['code' => 200, 'msg' => $msg, 'data' => $data]);
    }
    
    protected function error($msg = '操作失败', $code = 400, $data = []): Json
    {
        return json(['code' => $code, 'msg' => $msg, 'data' => $data]);
    }
    
    protected function assign($name, $value = null)
    {
        View::assign($name, $value);
    }
    
    protected function fetch($template = '', $vars = [])
    {
        return View::fetch($template, $vars);
    }
}
