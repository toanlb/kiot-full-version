<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "return_detail".
 *
 * @property int $id
 * @property int $return_id
 * @property int|null $order_detail_id
 * @property int $product_id
 * @property int $quantity
 * @property int $unit_id
 * @property float $unit_price
 * @property float|null $tax_amount
 * @property float $total_amount
 * @property string|null $batch_number
 * @property string|null $reason
 * @property int|null $condition
 * @property int $restocking
 *
 * @property OrderDetail $orderDetail
 * @property Product $product
 * @property ReturnModel $return
 * @property ProductUnit $unit
 */
class ReturnDetail extends ActiveRecord
{
    const CONDITION_GOOD = 1;
    const CONDITION_DEFECTIVE = 2;
    const CONDITION_DAMAGED = 3;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'return_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['return_id', 'product_id', 'quantity', 'unit_id'], 'required'],
            [['return_id', 'order_detail_id', 'product_id', 'quantity', 'unit_id', 'condition', 'restocking'], 'integer'],
            [['unit_price', 'tax_amount', 'total_amount'], 'number'],
            [['reason'], 'string'],
            [['batch_number'], 'string', 'max' => 100],
            [['order_detail_id'], 'exist', 'skipOnError' => true, 'targetClass' => OrderDetail::class, 'targetAttribute' => ['order_detail_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['return_id'], 'exist', 'skipOnError' => true, 'targetClass' => ReturnModel::class, 'targetAttribute' => ['return_id' => 'id']],
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
            'return_id' => 'Trả hàng',
            'order_detail_id' => 'Chi tiết đơn hàng',
            'product_id' => 'Sản phẩm',
            'quantity' => 'Số lượng',
            'unit_id' => 'Đơn vị',
            'unit_price' => 'Đơn giá',
            'tax_amount' => 'Thuế',
            'total_amount' => 'Thành tiền',
            'batch_number' => 'Số lô',
            'reason' => 'Lý do',
            'condition' => 'Tình trạng',
            'restocking' => 'Nhập lại kho',
        ];
    }

    /**
     * Gets query for [[OrderDetail]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrderDetail()
    {
        return $this->hasOne(OrderDetail::class, ['id' => 'order_detail_id']);
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
     * Gets query for [[Return]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReturn()
    {
        return $this->hasOne(ReturnModel::class, ['id' => 'return_id']);
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
     * Get condition label
     * 
     * @return string
     */
    public function getConditionLabel()
    {
        $conditions = self::getConditions();
        return isset($conditions[$this->condition]) ? $conditions[$this->condition] : 'Không xác định';
    }

    /**
     * Get conditions
     * 
     * @return array
     */
    public static function getConditions()
    {
        return [
            self::CONDITION_GOOD => 'Tốt',
            self::CONDITION_DEFECTIVE => 'Lỗi',
            self::CONDITION_DAMAGED => 'Hư hỏng',
        ];
    }

    /**
     * Calculate total price
     */
    public function calculateTotalPrice()
    {
        $amount = $this->quantity * $this->unit_price;
        
        // Apply tax
        if ($this->tax_amount) {
            $amount += $this->tax_amount;
        }
        
        $this->total_amount = $amount;
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
            
            // Set default condition if not specified
            if (!$this->condition) {
                $this->condition = self::CONDITION_GOOD;
            }
            
            // Set default restocking based on condition
            if ($insert && $this->restocking === null) {
                $this->restocking = ($this->condition == self::CONDITION_GOOD) ? 1 : 0;
            }
            
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
        
        // Update return totals
        $this->updateReturnTotals();
    }

    /**
     * After delete
     */
    public function afterDelete()
    {
        parent::afterDelete();
        
        // Update return totals
        $this->updateReturnTotals();
    }

    /**
     * Update return totals
     */
    protected function updateReturnTotals()
    {
        $return = $this->return;
        
        if ($return) {
            $return->calculateTotals();
            $return->save(false);
        }
    }

    /**
     * Get available batches for selection
     * 
     * @return array
     */
    public function getAvailableBatches()
    {
        if (!$this->product_id || !$this->return || !$this->return->warehouse_id) {
            return [];
        }
        
        $batches = ProductBatch::find()
            ->where([
                'product_id' => $this->product_id,
                'warehouse_id' => $this->return->warehouse_id,
            ])
            ->orderBy(['expiry_date' => SORT_ASC, 'id' => SORT_ASC])
            ->all();
            
        $result = [];
        foreach ($batches as $batch) {
            $expiryInfo = '';
            if ($batch->expiry_date) {
                $expiryInfo = ' (HSD: ' . Yii::$app->formatter->asDate($batch->expiry_date) . ')';
            }
            
            $result[$batch->batch_number] = $batch->batch_number . $expiryInfo;
        }
        
        return $result;
    }
}