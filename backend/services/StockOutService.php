<?php
namespace backend\services;

use Yii;
use common\models\StockOut;
use common\models\StockOutDetail;
use common\models\StockMovement;
use common\models\ProductBatch;
use backend\services\StockService;

class StockOutService
{
    private $stockService;
    
    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }
    
    public function generateStockOutCode()
    {
        $prefix = 'XK' . date('ymd');
        $lastCode = StockOut::find()
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
    
    public function saveStockOutDetails($stock_out_id, $details)
    {
        $totalAmount = 0;
        
        foreach ($details as $detail) {
            $stockOutDetail = new StockOutDetail();
            $stockOutDetail->stock_out_id = $stock_out_id;
            $stockOutDetail->product_id = $detail['product_id'];
            $stockOutDetail->batch_number = $detail['batch_number'] ?? null;
            $stockOutDetail->quantity = $detail['quantity'];
            $stockOutDetail->unit_id = $detail['unit_id'];
            $stockOutDetail->unit_price = $detail['unit_price'];
            $stockOutDetail->total_price = $detail['quantity'] * $detail['unit_price'];
            $stockOutDetail->note = $detail['note'] ?? null;
            
            if (!$stockOutDetail->save()) {
                throw new \Exception('Không thể lưu chi tiết xuất kho: ' . json_encode($stockOutDetail->errors));
            }
            
            $totalAmount += $stockOutDetail->total_price;
        }
        
        // Cập nhật tổng tiền cho phiếu xuất kho
        $stockOut = StockOut::findOne($stock_out_id);
        $stockOut->total_amount = $totalAmount;
        
        if (!$stockOut->save()) {
            throw new \Exception('Không thể cập nhật tổng tiền phiếu xuất kho: ' . json_encode($stockOut->errors));
        }
        
        return true;
    }
    
    public function approveStockOut($id, $user_id)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $stockOut = StockOut::findOne($id);
            if (!$stockOut) {
                throw new \Exception('Phiếu xuất kho không tồn tại.');
            }
            
            if ($stockOut->status != StockOut::STATUS_DRAFT) {
                throw new \Exception('Chỉ có thể xác nhận phiếu xuất kho ở trạng thái nháp.');
            }
            
            // Kiểm tra tồn kho
            foreach ($stockOut->stockOutDetails as $detail) {
                $stock = \common\models\Stock::findOne([
                    'product_id' => $detail->product_id,
                    'warehouse_id' => $stockOut->warehouse_id
                ]);
                
                if (!$stock || $stock->quantity < $detail->quantity) {
                    throw new \Exception('Sản phẩm ' . $detail->product->name . ' không đủ số lượng trong kho.');
                }
                
                // Kiểm tra lô nếu có
                if ($detail->batch_number) {
                    $batch = ProductBatch::findOne([
                        'product_id' => $detail->product_id,
                        'warehouse_id' => $stockOut->warehouse_id,
                        'batch_number' => $detail->batch_number
                    ]);
                    
                    if (!$batch || $batch->quantity < $detail->quantity) {
                        throw new \Exception('Lô ' . $detail->batch_number . ' của sản phẩm ' . $detail->product->name . ' không đủ số lượng.');
                    }
                }
            }
            
            $stockOut->status = StockOut::STATUS_CONFIRMED;
            $stockOut->approved_by = $user_id;
            $stockOut->approved_at = date('Y-m-d H:i:s');
            $stockOut->updated_at = date('Y-m-d H:i:s');
            
            if (!$stockOut->save()) {
                throw new \Exception('Không thể xác nhận phiếu xuất kho: ' . json_encode($stockOut->errors));
            }
            
            $transaction->commit();
            return [
                'success' => true,
                'message' => 'Phiếu xuất kho đã được xác nhận thành công.'
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    public function completeStockOut($id)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $stockOut = StockOut::findOne($id);
            if (!$stockOut) {
                throw new \Exception('Phiếu xuất kho không tồn tại.');
            }
            
            if ($stockOut->status != StockOut::STATUS_CONFIRMED) {
                throw new \Exception('Chỉ có thể hoàn thành phiếu xuất kho đã xác nhận.');
            }
            
            // Cập nhật tồn kho
            foreach ($stockOut->stockOutDetails as $detail) {
                $result = $this->stockService->updateStock(
                    $detail->product_id,
                    $stockOut->warehouse_id,
                    $detail->quantity,
                    StockMovement::TYPE_OUT,
                    $stockOut->id,
                    'stock_out',
                    $detail->unit_id,
                    $stockOut->warehouse_id,
                    null,
                    $detail->batch_number,
                    'Xuất kho: ' . $stockOut->code
                );
                
                if (!$result['success']) {
                    throw new \Exception($result['message']);
                }
            }
            
            // Cập nhật trạng thái phiếu xuất kho
            $stockOut->status = StockOut::STATUS_COMPLETED;
            $stockOut->updated_at = date('Y-m-d H:i:s');
            
            if (!$stockOut->save()) {
                throw new \Exception('Không thể hoàn thành phiếu xuất kho: ' . json_encode($stockOut->errors));
            }
            
            $transaction->commit();
            return [
                'success' => true,
                'message' => 'Phiếu xuất kho đã được hoàn thành thành công.'
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    public function cancelStockOut($id)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $stockOut = StockOut::findOne($id);
            if (!$stockOut) {
                throw new \Exception('Phiếu xuất kho không tồn tại.');
            }
            
            if ($stockOut->status == StockOut::STATUS_COMPLETED) {
                throw new \Exception('Không thể hủy phiếu xuất kho đã hoàn thành.');
            }
            
            $stockOut->status = StockOut::STATUS_CANCELED;
            $stockOut->updated_at = date('Y-m-d H:i:s');
            
            if (!$stockOut->save()) {
                throw new \Exception('Không thể hủy phiếu xuất kho: ' . json_encode($stockOut->errors));
            }
            
            $transaction->commit();
            return [
                'success' => true,
                'message' => 'Phiếu xuất kho đã được hủy thành công.'
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