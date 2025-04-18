<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "stock_check_detail".
 *
 * @property int $id
 * @property int $stock_check_id
 * @property int $product_id
 * @property string|null $batch_number
 * @property int $system_quantity
 * @property int $actual_quantity
 * @property int $difference
 * @property int $unit_id
 * @property string|null $note
 * @property int $adjustment_approved
 *
 * @property Product $product
 * @property StockCheck $stockCheck
 * @property ProductUnit $unit
 */
class StockCheckDetail extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'stock_check_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['stock_check_id', 'product_id', 'system_quantity', 'actual_quantity', 'unit_id'], 'required'],
            [['stock_check_id', 'product_id', 'system_quantity', 'actual_quantity', 'difference', 'unit_id', 'adjustment_approved'], 'integer'],
            [['note'], 'string'],
            [['batch_number'], 'string', 'max' => 100],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['stock_check_id'], 'exist', 'skipOnError' => true, 'targetClass' => StockCheck::class, 'targetAttribute' => ['stock_check_id' => 'id']],
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
            'stock_check_id' => 'Phiếu kiểm kho',
            'product_id' => 'Sản phẩm',
            'batch_number' => 'Số lô',
            'system_quantity' => 'Số lượng hệ thống',
            'actual_quantity' => 'Số lượng thực tế',
            'difference' => 'Chênh lệch',
            'unit_id' => 'Đơn vị',
            'note' => 'Ghi chú',
            'adjustment_approved' => 'Điều chỉnh được duyệt',
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
     * Gets query for [[StockCheck]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStockCheck()
    {
        return $this->hasOne(StockCheck::class, ['id' => 'stock_check_id']);
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
     * Before save
     * 
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Calculate difference
            $this->difference = $this->actual_quantity - $this->system_quantity;
            return true;
        }
        
        return false;
    }

    /**
     * Get available batches for selection
     * 
     * @return array
     */
    public function getAvailableBatches()
    {
        if (!$this->product_id || !$this->stockCheck || !$this->stockCheck->warehouse_id) {
            return [];
        }
        
        $batches = ProductBatch::find()
            ->where([
                'product_id' => $this->product_id,
                'warehouse_id' => $this->stockCheck->warehouse_id,
            ])
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

    /**
     * Get difference status
     * 
     * @return string
     */
    public function getDifferenceStatus()
    {
        if ($this->difference == 0) {
            return 'Khớp';
        } elseif ($this->difference > 0) {
            return 'Thừa';
        } else {
            return 'Thiếu';
        }
    }

    /**
     * Get adjustment status
     * 
     * @return string
     */
    public function getAdjustmentStatus()
    {
        if ($this->difference == 0) {
            return 'Không cần điều chỉnh';
        }
        
        if ($this->adjustment_approved) {
            return 'Đã duyệt điều chỉnh';
        }
        
        return 'Chưa duyệt điều chỉnh';
    }
}