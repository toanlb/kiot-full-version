<?php
namespace console\controllers;

use Yii;
use yii\console\Controller;
use common\models\User;

class UserController extends Controller
{
    public function actionResetPassword($username, $password)
    {
        $user = User::findOne(['username' => $username]);
        
        if (!$user) {
            echo "Không tìm thấy user với username: $username\n";
            return 1;
        }
        
        $user->setPassword($password);
        if ($user->save(false)) {
            echo "Đã cập nhật mật khẩu cho user $username thành công!\n";
            return 0;
        } else {
            echo "Không thể cập nhật mật khẩu. Lỗi: " . print_r($user->errors, true) . "\n";
            return 1;
        }
    }
}