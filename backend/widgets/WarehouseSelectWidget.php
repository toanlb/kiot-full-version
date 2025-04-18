<?php
namespace backend\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use common\models\Warehouse;
use common\models\UserWarehouse;

class WarehouseSelectWidget extends Widget
{
    public $model;
    public $attribute = 'warehouse_id';
    public $options = [];
    public $prompt = 'Chọn kho hàng...';
    
    public function run()
    {
        // Lấy danh sách kho hàng người dùng được phân quyền
        $userId = Yii::$app->user->id;
        $warehouses = $this->getUserWarehouses($userId);
        
        // Thiết lập options
        $options = array_merge([
            'class' => 'form-control',
            'prompt' => $this->prompt,
        ], $this->options);
        
        // Nếu chỉ có 1 kho, tự động chọn
        if (count($warehouses) === 1) {
            $warehouseIds = array_keys($warehouses);
            if ($this->model->isNewRecord) {
                $this->model->{$this->attribute} = $warehouseIds[0];
            }
        }
        
        return Html::activeDropDownList($this->model, $this->attribute, $warehouses, $options);
    }
    
    protected function getUserWarehouses($userId)
    {
        // Nếu là admin, lấy tất cả kho
        if (Yii::$app->authManager->checkAccess($userId, 'admin')) {
            return Warehouse::getList();
        }
        
        // Lấy kho hàng được phân quyền
        $userWarehouses = UserWarehouse::find()
            ->select('warehouse_id')
            ->where(['user_id' => $userId])
            ->column();
        
        // Nếu không có kho nào được phân quyền, trả về kho mặc định
        if (empty($userWarehouses)) {
            $defaultWarehouse = Warehouse::find()
                ->where(['is_default' => 1, 'is_active' => 1])
                ->one();
            
            if ($defaultWarehouse) {
                return [$defaultWarehouse->id => $defaultWarehouse->name];
            }
            
            return [];
        }
        
        // Lấy danh sách kho từ ID
        return Warehouse::find()
            ->where(['id' => $userWarehouses, 'is_active' => 1])
            ->orderBy(['name' => SORT_ASC])
            ->indexBy('id')
            ->select('name')
            ->column();
    }
}