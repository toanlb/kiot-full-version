<?php
namespace backend\services;

use Yii;
use common\models\StockIn;
use common\models\StockInDetail;
use common\models\StockMovement;
use common\models\ProductBatch;
use backend\services\StockService;

class StockInService
{
    private $stockService;
    
    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }
    
    public function generateStockInCode()
    {
        $prefix = 'NK' . date('ymd');
        $lastCode = StockIn::find()
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
    
    public function saveStockInDetails($stock_in_id, $details)
    {
        $totalAmount = 0;
        $discountAmount = 0;
        $taxAmount = 0;
        
        foreach ($details as $detail) {
            $stockInDetail = new StockInDetail();
            $stockInDetail->stock_in_id = $stock_in_id;
            $stockInDetail->product_id = $detail['product_id'];
            $stockInDetail->batch_number = $detail['batch_number'] ?? null;
            $stockInDetail->expiry_date = $detail['expiry_date'] ?? null;
            $stockInDetail->quantity = $detail['quantity'];
            $stockInDetail->unit_id = $detail['unit_id'];
            $stockInDetail->unit_price = $detail['unit_price'];
            $stockInDetail->discount_percent = $detail['discount_percent'] ?? 0;
            $stockInDetail->discount_amount = $detail['discount_amount'] ?? 0;
            $stockInDetail->tax_percent = $detail['tax_percent'] ?? 0;
            $stockInDetail->tax_amount = $detail['tax_amount'] ?? 0;
            
            // Tính toán tổng tiền
            $subtotal = $stockInDetail->quantity * $stockInDetail->unit_price;
            $itemDiscountAmount = $stockInDetail->discount_amount;
            $itemTaxAmount = $stockInDetail->tax_amount;
            $stockInDetail->total_price = $subtotal - $itemDiscountAmount + $itemTaxAmount;
            
            if (!$stockInDetail->save()) {
                throw new \Exception('Không thể lưu chi tiết nhập kho: ' . json_encode($stockInDetail->errors));
            }
            
            $totalAmount += $subtotal;
            $discountAmount += $itemDiscountAmount;
            $taxAmount += $itemTaxAmount;
        }
        
        // Cập nhật tổng tiền cho phiếu nhập kho
        $stockIn = StockIn::findOne($stock_in_id);
        $stockIn->total_amount = $totalAmount;
        $stockIn->discount_amount = $discountAmount;
        $stockIn->tax_amount = $taxAmount;
        $stockIn->final_amount = $totalAmount - $discountAmount + $taxAmount;
        
        if (!$stockIn->save()) {
            throw new \Exception('Không thể cập nhật tổng tiền phiếu nhập kho: ' . json_encode($stockIn->errors));
        }
        
        return true;
    }
    
    public function approveStockIn($id, $user_id)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $stockIn = StockIn::findOne($id);
            if (!$stockIn) {
                throw new \Exception('Phiếu nhập kho không tồn tại.');
            }
            
            if ($stockIn->status != StockIn::STATUS_DRAFT) {
                throw new \Exception('Chỉ có thể xác nhận phiếu nhập kho ở trạng thái nháp.');
            }
            
            $stockIn->status = StockIn::STATUS_CONFIRMED;
            $stockIn->approved_by = $user_id;
            $stockIn->approved_at = date('Y-m-d H:i:s');
            $stockIn->updated_at = date('Y-m-d H:i:s');
            
            if (!$stockIn->save()) {
                throw new \Exception('Không thể xác nhận phiếu nhập kho: ' . json_encode($stockIn->errors));
            }
            
            $transaction->commit();
            return [
                'success' => true,
                'message' => 'Phiếu nhập kho đã được xác nhận thành công.'
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    public function completeStockIn($id)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $stockIn = StockIn::findOne($id);
            if (!$stockIn) {
                throw new \Exception('Phiếu nhập kho không tồn tại.');
            }
            
            if ($stockIn->status != StockIn::STATUS_CONFIRMED) {
                throw new \Exception('Chỉ có thể hoàn thành phiếu nhập kho đã xác nhận.');
            }
            
            // Cập nhật tồn kho
            foreach ($stockIn->stockInDetails as $detail) {
                $result = $this->stockService->updateStock(
                    $detail->product_id,
                    $stockIn->warehouse_id,
                    $detail->quantity,
                    StockMovement::TYPE_IN,
                    $stockIn->id,
                    'stock_in',
                    $detail->unit_id,
                    null,
                    $stockIn->warehouse_id,
                    $detail->batch_number,
                    'Nhập kho: ' . $stockIn->code
                );
                
                if (!$result['success']) {
                    throw new \Exception($result['message']);
                }
                
                // Tạo lô hàng mới nếu có batch_number và expiry_date
                if ($detail->batch_number && $detail->expiry_date) {
                    $batch = ProductBatch::findOne([
                        'product_id' => $detail->product_id,
                        'warehouse_id' => $stockIn->warehouse_id,
                        'batch_number' => $detail->batch_number
                    ]);
                    
                    if (!$batch) {
                        $batch = new ProductBatch();
                        $batch->product_id = $detail->product_id;
                        $batch->warehouse_id = $stockIn->warehouse_id;
                        $batch->batch_number = $detail->batch_number;
                        $batch->quantity = $detail->quantity;
                        $batch->expiry_date = $detail->expiry_date;
                        $batch->cost_price = $detail->unit_price;
                        $batch->stock_in_id = $stockIn->id;
                        $batch->created_at = date('Y-m-d H:i:s');
                        $batch->updated_at = date('Y-m-d H:i:s');
                        
                        if (!$batch->save()) {
                            throw new \Exception('Không thể tạo lô hàng: ' . json_encode($batch->errors));
                        }
                    } else {
                        $batch->quantity += $detail->quantity;
                        $batch->updated_at = date('Y-m-d H:i:s');
                        
                        if (!$batch->save()) {
                            throw new \Exception('Không thể cập nhật lô hàng: ' . json_encode($batch->errors));
                        }
                    }
                }
                
                // Cập nhật giá nhập cho sản phẩm
                $product = $detail->product;
                if ($product->cost_price != $detail->unit_price) {
                    $product->cost_price = $detail->unit_price;
                    $product->updated_at = date('Y-m-d H:i:s');
                    
                    if (!$product->save()) {
                        throw new \Exception('Không thể cập nhật giá nhập sản phẩm: ' . json_encode($product->errors));
                    }
                    
                    // Lưu lịch sử giá
                    $priceHistory = new \common\models\ProductPriceHistory();
                    $priceHistory->product_id = $product->id;
                    $priceHistory->cost_price = $product->cost_price;
                    $priceHistory->selling_price = $product->selling_price;
                    $priceHistory->effective_date = date('Y-m-d H:i:s');
                    $priceHistory->created_at = date('Y-m-d H:i:s');
                    $priceHistory->created_by = Yii::$app->user->id;
                    $priceHistory->note = 'Cập nhật từ phiếu nhập kho ' . $stockIn->code;
                    
                    if (!$priceHistory->save()) {
                        throw new \Exception('Không thể lưu lịch sử giá: ' . json_encode($priceHistory->errors));
                    }
                }
            }
            
            // Cập nhật trạng thái phiếu nhập kho
            $stockIn->status = StockIn::STATUS_COMPLETED;
            $stockIn->updated_at = date('Y-m-d H:i:s');
            
            if (!$stockIn->save()) {
                throw new \Exception('Không thể hoàn thành phiếu nhập kho: ' . json_encode($stockIn->errors));
            }
            
            $transaction->commit();
            return [
                'success' => true,
                'message' => 'Phiếu nhập kho đã được hoàn thành thành công.'
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    public function cancelStockIn($id)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $stockIn = StockIn::findOne($id);
            if (!$stockIn) {
                throw new \Exception('Phiếu nhập kho không tồn tại.');
            }
            
            if ($stockIn->status == StockIn::STATUS_COMPLETED) {
                throw new \Exception('Không thể hủy phiếu nhập kho đã hoàn thành.');
            }
            
            $stockIn->status = StockIn::STATUS_CANCELED;
            $stockIn->updated_at = date('Y-m-d H:i:s');
            
            if (!$stockIn->save()) {
                throw new \Exception('Không thể hủy phiếu nhập kho: ' . json_encode($stockIn->errors));
            }
            
            $transaction->commit();
            return [
                'success' => true,
                'message' => 'Phiếu nhập kho đã được hủy thành công.'
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