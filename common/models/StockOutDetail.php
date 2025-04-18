<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "stock_out_detail".
 *
 * @property int $id
 * @property int $stock_out_id
 * @property int $product_id
 * @property string|null $batch_number
 * @property int $quantity
 * @property int $unit_id
 * @property float $unit_price
 * @property float $total_price
 * @property string|null $note
 *
 * @property Product $product
 * @property StockOut $stockOut
 * @property ProductUnit $unit
 */
class StockOutDetail extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'stock_out_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['stock_out_id', 'product_id', 'quantity', 'unit_id'], 'required'],
            [['stock_out_id', 'product_id', 'quantity', 'unit_id'], 'integer'],
            [['unit_price', 'total_price'], 'number'],
            [['note'], 'string'],
            [['batch_number'], 'string', 'max' => 100],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['stock_out_id'], 'exist', 'skipOnError' => true, 'targetClass' => StockOut::class, 'targetAttribute' => ['stock_out_id' => 'id']],
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
            'stock_out_id' => 'Phiếu xuất kho',
            'product_id' => 'Sản phẩm',
            'batch_number' => 'Số lô',
            'quantity' => 'Số lượng',
            'unit_id' => 'Đơn vị',
            'unit_price' => 'Đơn giá',
            'total_price' => 'Thành tiền',
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
     * Gets query for [[StockOut]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStockOut()
    {
        return $this->hasOne(StockOut::class, ['id' => 'stock_out_id']);
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
     * Calculate total price
     */
    public function calculateTotalPrice()
    {
        $this->total_price = $this->quantity * $this->unit_price;
    }

    /**
     * Before save
     * 
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Calculate total price
            $this->calculateTotalPrice();
            return true;
        }
        
        return false;
    }

    /**
     * After save
     * 
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        
        // Update stock out totals
        $this->updateStockOutTotals();
    }

    /**
     * After delete
     */
    public function afterDelete()
    {
        parent::afterDelete();
        
        // Update stock out totals
        $this->updateStockOutTotals();
    }

    /**
     * Update stock out totals
     */
    protected function updateStockOutTotals()
    {
        $stockOut = $this->stockOut;
        
        if ($stockOut) {
            $totalAmount = self::find()
                ->where(['stock_out_id' => $stockOut->id])
                ->sum('total_price');
            
            $stockOut->total_amount = $totalAmount ?: 0;
            $stockOut->save(false);
        }
    }

    /**
     * Get available batches for selection
     * 
     * @return array
     */
    public function getAvailableBatches()
    {
        if (!$this->product_id || !$this->stockOut || !$this->stockOut->warehouse_id) {
            return [];
        }
        
        $batches = ProductBatch::find()
            ->where([
                'product_id' => $this->product_id,
                'warehouse_id' => $this->stockOut->warehouse_id,
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