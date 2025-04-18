<?php
namespace backend\services;

use Yii;
use common\models\Stock;
use common\models\StockMovement;
use common\models\ProductBatch;

class StockService
{
    public function getProductBatches($product_id, $warehouse_id)
    {
        return ProductBatch::find()
            ->where(['product_id' => $product_id, 'warehouse_id' => $warehouse_id])
            ->andWhere(['>', 'quantity', 0])
            ->orderBy(['expiry_date' => SORT_ASC])
            ->all();
    }
    
    public function getStockMovements($product_id, $warehouse_id)
    {
        return StockMovement::find()
            ->where(['product_id' => $product_id])
            ->andWhere(['OR', 
                ['source_warehouse_id' => $warehouse_id],
                ['destination_warehouse_id' => $warehouse_id]
            ])
            ->orderBy(['movement_date' => SORT_DESC])
            ->limit(50)
            ->all();
    }
    
    public function getLowStockItems()
    {
        return Yii::$app->db->createCommand('
            SELECT s.*, p.name AS product_name, p.code AS product_code, 
                   w.name AS warehouse_name, u.name AS unit_name
            FROM stock s
            JOIN product p ON s.product_id = p.id
            JOIN warehouse w ON s.warehouse_id = w.id
            JOIN product_unit u ON p.unit_id = u.id
            WHERE s.quantity <= COALESCE(s.min_stock, p.min_stock)
            AND p.status = 1
            AND w.is_active = 1
            ORDER BY w.name, p.name
        ')->queryAll();
    }
    
    public function updateStock($product_id, $warehouse_id, $quantity, $movement_type, $reference_id, $reference_type, $unit_id, $source_warehouse_id = null, $destination_warehouse_id = null, $batch_number = null, $note = null)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Tìm hoặc tạo mới bản ghi stock
            $stock = Stock::findOne(['product_id' => $product_id, 'warehouse_id' => $warehouse_id]);
            if (!$stock) {
                $stock = new Stock();
                $stock->product_id = $product_id;
                $stock->warehouse_id = $warehouse_id;
                $stock->quantity = 0;
            }
            
            // Cập nhật số lượng tồn kho
            $old_quantity = $stock->quantity;
            if ($movement_type == StockMovement::TYPE_IN || $movement_type == StockMovement::TYPE_CHECK) {
                $stock->quantity += $quantity;
            } elseif ($movement_type == StockMovement::TYPE_OUT) {
                $stock->quantity -= $quantity;
            }
            
            // Cập nhật thời gian
            $stock->updated_at = date('Y-m-d H:i:s');
            
            if (!$stock->save()) {
                throw new \Exception('Không thể cập nhật số lượng tồn kho: ' . json_encode($stock->errors));
            }
            
            // Tạo bản ghi chuyển động kho
            $movement = new StockMovement();
            $movement->product_id = $product_id;
            $movement->source_warehouse_id = $source_warehouse_id;
            $movement->destination_warehouse_id = $destination_warehouse_id;
            $movement->reference_id = $reference_id;
            $movement->reference_type = $reference_type;
            $movement->quantity = $quantity;
            $movement->balance = $stock->quantity;
            $movement->unit_id = $unit_id;
            $movement->movement_type = $movement_type;
            $movement->movement_date = date('Y-m-d H:i:s');
            $movement->note = $note;
            $movement->created_at = date('Y-m-d H:i:s');
            $movement->created_by = Yii::$app->user->id;
            
            if (!$movement->save()) {
                throw new \Exception('Không thể tạo bản ghi chuyển động kho: ' . json_encode($movement->errors));
            }
            
            // Cập nhật hoặc tạo mới batch (nếu có)
            if ($batch_number && ($movement_type == StockMovement::TYPE_IN || $movement_type == StockMovement::TYPE_CHECK)) {
                $batch = ProductBatch::findOne([
                    'product_id' => $product_id,
                    'warehouse_id' => $warehouse_id,
                    'batch_number' => $batch_number
                ]);
                
                if (!$batch) {
                    $batch = new ProductBatch();
                    $batch->product_id = $product_id;
                    $batch->warehouse_id = $warehouse_id;
                    $batch->batch_number = $batch_number;
                    $batch->quantity = 0;
                    $batch->created_at = date('Y-m-d H:i:s');
                }
                
                $batch->quantity += $quantity;
                $batch->updated_at = date('Y-m-d H:i:s');
                
                if (!$batch->save()) {
                    throw new \Exception('Không thể cập nhật lô hàng: ' . json_encode($batch->errors));
                }
            } elseif ($batch_number && $movement_type == StockMovement::TYPE_OUT) {
                $batch = ProductBatch::findOne([
                    'product_id' => $product_id,
                    'warehouse_id' => $warehouse_id,
                    'batch_number' => $batch_number
                ]);
                
                if ($batch) {
                    $batch->quantity -= $quantity;
                    $batch->updated_at = date('Y-m-d H:i:s');
                    
                    if (!$batch->save()) {
                        throw new \Exception('Không thể cập nhật lô hàng: ' . json_encode($batch->errors));
                    }
                }
            }
            
            $transaction->commit();
            return [
                'success' => true,
                'old_quantity' => $old_quantity,
                'new_quantity' => $stock->quantity,
                'movement_id' => $movement->id
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