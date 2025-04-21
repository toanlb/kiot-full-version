<?php

namespace common\components;

use Yii;
use yii\base\ActionFilter;
use yii\web\ForbiddenHttpException;
use common\models\UserWarehouse;

/**
 * WarehouseFilter giới hạn truy cập dựa vào kho hàng được phân quyền
 */
class WarehouseFilter extends ActionFilter
{
    /**
     * @var string Tham số ID kho hàng trong request
     */
    public $warehouseParam = 'warehouse_id';
    
    /**
     * @var string Thông báo lỗi khi không có quyền truy cập kho
     */
    public $errorMessage = 'Bạn không có quyền truy cập kho hàng này.';
    
    /**
     * @var array Chỉ định các action cần kiểm tra quyền kho
     */
    public $only = [];
    
    /**
     * @var array Chỉ định các action không cần kiểm tra quyền kho
     */
    public $except = [];
    
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $actionId = $action->id;
        
        // Kiểm tra xem action có cần kiểm tra quyền kho không
        if (!empty($this->only) && !in_array($actionId, $this->only)) {
            return parent::beforeAction($action);
        }
        
        if (!empty($this->except) && in_array($actionId, $this->except)) {
            return parent::beforeAction($action);
        }
        
        // Lấy warehouse_id từ request
        $warehouseId = Yii::$app->request->get($this->warehouseParam);
        if (!$warehouseId) {
            $warehouseId = Yii::$app->request->post($this->warehouseParam);
        }
        
        // Nếu không có warehouse_id trong request, bỏ qua kiểm tra
        if (!$warehouseId) {
            return parent::beforeAction($action);
        }
        
        // Admin có quyền truy cập tất cả các kho
        if (Yii::$app->user->can('admin')) {
            return parent::beforeAction($action);
        }
        
        // Quản lý cửa hàng cũng có quyền truy cập tất cả các kho
        if (Yii::$app->user->can('storeManager')) {
            return parent::beforeAction($action);
        }
        
        // Kiểm tra quyền truy cập kho của người dùng
        $userId = Yii::$app->user->id;
        $hasAccess = UserWarehouse::find()
            ->where(['user_id' => $userId, 'warehouse_id' => $warehouseId])
            ->exists();
        
        if (!$hasAccess) {
            throw new ForbiddenHttpException($this->errorMessage);
        }
        
        return parent::beforeAction($action);
    }
}