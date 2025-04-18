<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "stock_transfer_detail".
 *
 * @property int $id
 * @property int $stock_transfer_id
 * @property int $product_id
 * @property string|null $batch_number
 * @property int $quantity
 * @property int|null $received_quantity
 * @property int $unit_id
 * @property string|null $note
 *
 * @property Product $product
 * @property StockTransfer $stockTransfer
 * @property ProductUnit $unit
 */
class StockTransferDetail extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'stock_transfer_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['stock_transfer_id', 'product_id', 'quantity', 'unit_id'], 'required'],
            [['stock_transfer_id', 'product_id', 'quantity', 'received_quantity', 'unit_id'], 'integer'],
            [['note'], 'string'],
            [['batch_number'], 'string', 'max' => 100],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['stock_transfer_id'], 'exist', 'skipOnError' => true, 'targetClass' => StockTransfer::class, 'targetAttribute' => ['stock_transfer_id' => 'id']],
            [['unit_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductUnit::class, 'targetAttribute' => ['unit_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'stock_transfer_id' => 'Phiếu chuyển kho',
            'product_id' => 'Sản phẩm',
            'batch_number' => 'Số lô',
            'quantity' => 'Số lượng',
            'received_quantity' => 'Số lượng đã nhận',
            'unit_id' => 'Đơn vị',
            'note' => 'Ghi chú',
        ];
    }

    /**
     * Gets query for [[Product]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    /**
     * Gets query for [[StockTransfer]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStockTransfer()
    {
        return $this->hasOne(StockTransfer::class, ['id' => 'stock_transfer_id']);
    }

    /**
     * Gets query for [[Unit]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUnit()
    {
        return $this->hasOne(ProductUnit::class, ['id' => 'unit_id']);
    }

    /**
     * Get received status
     * 
     * @return string
     */
    public function getReceivedStatus()
    {
        if (is_null($this->received_quantity)) {
            return 'Chưa nhận';
        }
        
        if ($this->received_quantity == 0) {
            return 'Không nhận';
        }
        
        if ($this->received_quantity < $this->quantity) {
            return 'Nhận một phần';
        }
        
        return 'Đã nhận đủ';
    }

    /**
     * Get available batches for selection
     * 
     * @return array
     */
    public function getAvailableBatches()
    {
        if (!$this->product_id || !$this->stockTransfer || !$this->stockTransfer->source_warehouse_id) {
            return [];
        }
        
        $batches = ProductBatch::find()
            ->where([
                'product_id' => $this->product_id,
                'warehouse_id' => $this->stockTransfer->source_warehouse_id,
            ])
            ->andWhere(['>', 'quantity', 0])
            ->orderBy(['expiry_date' => SORT_ASC, 'id' => SORT_ASC])
            ->all();
            
        $result = [];
        foreach ($batches as $batch) {
            $expiryInfo = '';
            if ($batch->expiry_date) {
                $expiryInfo = ' (HSD: ' . Yii::$app->formatter->asDate($batch->expiry_date) . ')';
            }
            
            $result[$batch->batch_number] = $batch->batch_number . ' - SL: ' . $batch->quantity . $expiryInfo;
        }
        
        return $result;
    }
}