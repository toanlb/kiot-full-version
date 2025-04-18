<?php
namespace console\controllers;

use Yii;
use yii\console\Controller;

class RbacController extends Controller
{
    public function actionInit()
    {
        $auth = Yii::$app->authManager;
        $auth->removeAll();

        // Tạo các quyền cơ bản
        $viewDashboard = $auth->createPermission('viewDashboard');
        $viewDashboard->description = 'Xem trang tổng quan';
        $auth->add($viewDashboard);

        $manageProducts = $auth->createPermission('manageProducts');
        $manageProducts->description = 'Quản lý sản phẩm';
        $auth->add($manageProducts);

        $manageOrders = $auth->createPermission('manageOrders');
        $manageOrders->description = 'Quản lý đơn hàng';
        $auth->add($manageOrders);

        $manageCustomers = $auth->createPermission('manageCustomers');
        $manageCustomers->description = 'Quản lý khách hàng';
        $auth->add($manageCustomers);

        $manageSettings = $auth->createPermission('manageSettings');
        $manageSettings->description = 'Quản lý cài đặt hệ thống';
        $auth->add($manageSettings);

        // Tạo các vai trò
        $cashier = $auth->createRole('cashier');
        $cashier->description = 'Nhân viên bán hàng';
        $auth->add($cashier);
        $auth->addChild($cashier, $viewDashboard);

        $manager = $auth->createRole('manager');
        $manager->description = 'Quản lý cửa hàng';
        $auth->add($manager);
        $auth->addChild($manager, $cashier);
        $auth->addChild($manager, $manageProducts);
        $auth->addChild($manager, $manageOrders);
        $auth->addChild($manager, $manageCustomers);

        $admin = $auth->createRole('admin');
        $admin->description = 'Quản trị viên';
        $auth->add($admin);
        $auth->addChild($admin, $manager);
        $auth->addChild($admin, $manageSettings);

        // Gán vai trò admin cho user ID=1
        $auth->assign($admin, 1);

        echo "RBAC initialization completed.\n";
    }
}