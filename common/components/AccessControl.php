<?php
namespace common\components;

use Yii;
use yii\base\Action;
use yii\base\ActionFilter;
use yii\web\Controller;
use yii\web\User;
use yii\web\ForbiddenHttpException;

class AccessControl extends \yii\filters\AccessControl
{
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $user = $this->user;
        
        // Kiểm tra xem action hiện tại có phải là 'login' hoặc 'error' không
        $actionId = $action->id;
        $controllerId = $action->controller->id;
        
        // Nếu đây là action đăng nhập hoặc action xử lý lỗi, bỏ qua việc kiểm tra quyền
        if (($controllerId === 'site' && ($actionId === 'login' || $actionId === 'error'))) {
            return true;
        }
        
        if ($user->getIsGuest()) {
            // Nếu người dùng chưa đăng nhập, chuyển hướng đến trang đăng nhập
            $user->loginRequired();
            return false;
        }
        
        // THÊM CODE SAU ĐÂY: Kiểm tra nếu user có role 'admin' thì cho phép luôn
        $auth = Yii::$app->authManager;
        $roles = $auth->getRolesByUser($user->id);
        foreach ($roles as $role) {
            if ($role->name === 'admin') {
                return true; // Admin có thể truy cập mọi trang
            }
        }
        
        // Kiểm tra quyền truy cập bình thường
        return parent::beforeAction($action);
    }
}