<?php
namespace backend\services;

use Yii;
use common\models\StockCheck;
use common\models\StockCheckDetail;
use common\models\StockMovement;
use common\models\Stock;
use common\models\Product;
use backend\services\StockService;

class StockCheckService
{
    private $stockService;
    
    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }
    
    public function generateStockCheckCode()
    {
        $prefix = 'KK' . date('ymd');
        $lastCode = StockCheck::find()
            ->where(['like', 'code', $prefix . '%', false])
            ->orderBy(['id' => SORT_DESC])
            ->one();
        
        if ($lastCode) {
            $lastNumber = (int)substr($lastCode->code, strlen($prefix));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
    
    public function getWarehouseProducts($warehouse_id)
    {
        return Yii::$app->db->createCommand('
            SELECT s.product_id, s.quantity AS system_quantity, p.name AS product_name, p.code AS product_code, u.id AS unit_id, u.name AS unit_name
            FROM stock s
            JOIN product p ON s.product_id = p.id
            JOIN product_unit u ON p.unit_id = u.id
            WHERE s.warehouse_id = :warehouse_id
            AND p.status = 1
            ORDER BY p.name
        ', [':warehouse_id' => $warehouse_id])->queryAll();
    }
    
    public function saveStockCheckDetails($stock_check_id, $details)
    {
        foreach ($details as $detail) {
            $checkDetail = new StockCheckDetail();
            $checkDetail->stock_check_id = $stock_check_id;
            $checkDetail->product_id = $detail['product_id'];
            $checkDetail->batch_number = $detail['batch_number'] ?? null;
            $checkDetail->system_quantity = $detail['system_quantity'];
            $checkDetail->actual_quantity = $detail['actual_quantity'];
            $checkDetail->difference = $detail['actual_quantity'] - $detail['system_quantity'];
            $checkDetail->unit_id = $detail['unit_id'];
            $checkDetail->note = $detail['note'] ?? null;
            $checkDetail->adjustment_approved = 0;
            
            if (!$checkDetail->save()) {
                throw new \Exception('Không thể lưu chi tiết kiểm kê: ' . json_encode($checkDetail->errors));
            }
        }
        
        return true;
    }
    
    public function approveStockCheck($id, $user_id)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $stockCheck = StockCheck::findOne($id);
            if (!$stockCheck) {
                throw new \Exception('Phiếu kiểm kê không tồn tại.');
            }
            
            if ($stockCheck->status != StockCheck::STATUS_DRAFT) {
                throw new \Exception('Chỉ có thể xác nhận phiếu kiểm kê ở trạng thái nháp.');
            }
            
            $stockCheck->status = StockCheck::STATUS_CONFIRMED;
            $stockCheck->approved_by = $user_id;
            $stockCheck->approved_at = date('Y-m-d H:i:s');
            $stockCheck->updated_at = date('Y-m-d H:i:s');
            
            if (!$stockCheck->save()) {
                throw new \Exception('Không thể xác nhận phiếu kiểm kê: ' . json_encode($stockCheck->errors));
            }
            
            $transaction->commit();
            return [
                'success' => true,
                'message' => 'Phiếu kiểm kê đã được xác nhận thành công.'
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    public function adjustStock($id, $adjustmentDetails)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $stockCheck = StockCheck::findOne($id);
            if (!$stockCheck) {
                throw new \Exception('Phiếu kiểm kê không tồn tại.');
            }
            
            if ($stockCheck->status != StockCheck::STATUS_CONFIRMED) {
                throw new \Exception('Chỉ có thể điều chỉnh tồn kho cho phiếu kiểm kê đã xác nhận.');
            }
            
            foreach ($adjustmentDetails as $detailId => $adjustment) {
                $detail = StockCheckDetail::findOne($detailId);
                if (!$detail || $detail->stock_check_id != $stockCheck->id) {
                    throw new \Exception('Chi tiết kiểm kê không hợp lệ.');
                }
                
                $approve = (bool)$adjustment['adjustment_approved'];
                $detail->adjustment_approved = $approve ? 1 : 0;
                
                if (!$detail->save()) {
                    throw new \Exception('Không thể cập nhật trạng thái điều chỉnh: ' . json_encode($detail->errors));
                }
                
                // Nếu được phê duyệt, thực hiện điều chỉnh tồn kho
                if ($approve && $detail->difference != 0) {
                    // Lấy thông tin tồn kho
                    $stock = Stock::findOne([
                        'product_id' => $detail->product_id,
                        'warehouse_id' => $stockCheck->warehouse_id
                    ]);
                    
                    if (!$stock) {
                        // Nếu chưa có bản ghi tồn kho, tạo mới nếu actual_quantity > 0
                        if ($detail->actual_quantity > 0) {
                            $stock = new Stock();
                            $stock->product_id = $detail->product_id;
                            $stock->warehouse_id = $stockCheck->warehouse_id;
                            $stock->quantity = 0;
                            $stock->updated_at = date('Y-m-d H:i:s');
                            
                            if (!$stock->save()) {
                                throw new \Exception('Không thể tạo bản ghi tồn kho mới: ' . json_encode($stock->errors));
                            }
                        } else {
                            continue; // Bỏ qua nếu không có tồn kho và số lượng thực tế = 0
                        }
                    }
                    
                    // Tạo bản ghi chuyển động kho
                    $movement = new StockMovement();
                    $movement->product_id = $detail->product_id;
                    $movement->source_warehouse_id = $detail->difference < 0 ? $stockCheck->warehouse_id : null;
                    $movement->destination_warehouse_id = $detail->difference > 0 ? $stockCheck->warehouse_id : null;
                    $movement->reference_id = $stockCheck->id;
                    $movement->reference_type = 'stock_check';
                    $movement->quantity = abs($detail->difference);
                    $movement->unit_id = $detail->unit_id;
                    $movement->movement_type = StockMovement::TYPE_CHECK;
                    $movement->movement_date = date('Y-m-d H:i:s');
                    $movement->balance = $detail->actual_quantity;
                    $movement->note = 'Điều chỉnh sau kiểm kê ' . $stockCheck->code;
                    $movement->created_at = date('Y-m-d H:i:s');
                    $movement->created_by = Yii::$app->user->id;
                    
                    if (!$movement->save()) {
                        throw new \Exception('Không thể tạo bản ghi chuyển động kho: ' . json_encode($movement->errors));
                    }
                    
                    // Cập nhật số lượng tồn kho
                    $stock->quantity = $detail->actual_quantity;
                    $stock->updated_at = date('Y-m-d H:i:s');
                    
                    if (!$stock->save()) {
                        throw new \Exception('Không thể cập nhật số lượng tồn kho: ' . json_encode($stock->errors));
                    }
                }
            }
            
            // Kiểm tra xem tất cả các chi tiết đã được xử lý chưa
            $allProcessed = StockCheckDetail::find()
                ->where(['stock_check_id' => $stockCheck->id])
                ->andWhere(['difference' => 0])
                ->orWhere(['adjustment_approved' => 1])
                ->count() == StockCheckDetail::find()
                ->where(['stock_check_id' => $stockCheck->id])
                ->count();
            
            if ($allProcessed) {
                $stockCheck->status = StockCheck::STATUS_ADJUSTED;
                $stockCheck->updated_at = date('Y-m-d H:i:s');
                
                if (!$stockCheck->save()) {
                    throw new \Exception('Không thể cập nhật trạng thái phiếu kiểm kê: ' . json_encode($stockCheck->errors));
                }
            }
            
            $transaction->commit();
            return [
                'success' => true,
                'message' => 'Điều chỉnh tồn kho thành công.',
                'completed' => $allProcessed
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    public function cancelStockCheck($id)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $stockCheck = StockCheck::findOne($id);
            if (!$stockCheck) {
                throw new \Exception('Phiếu kiểm kê không tồn tại.');
            }
            
            if ($stockCheck->status == StockCheck::STATUS_ADJUSTED) {
                throw new \Exception('Không thể hủy phiếu kiểm kê đã điều chỉnh.');
            }
            
            $stockCheck->status = StockCheck::STATUS_CANCELED;
            $stockCheck->updated_at = date('Y-m-d H:i:s');
            
            if (!$stockCheck->save()) {
                throw new \Exception('Không thể hủy phiếu kiểm kê: ' . json_encode($stockCheck->errors));
            }
            
            $transaction->commit();
            return [
                'success' => true,
                'message' => 'Phiếu kiểm kê đã được hủy thành công.'
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}