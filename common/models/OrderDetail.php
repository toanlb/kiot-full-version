<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "order_detail".
 *
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property int $quantity
 * @property int $unit_id
 * @property float $unit_price
 * @property float|null $discount_percent
 * @property float|null $discount_amount
 * @property float|null $tax_percent
 * @property float|null $tax_amount
 * @property float $total_amount
 * @property string|null $batch_number
 * @property string|null $note
 * @property float|null $cost_price
 *
 * @property Order $order
 * @property Product $product
 * @property ProductUnit $unit
 * @property ReturnDetail[] $returnDetails
 * @property Warranty[] $warranties
 */
class OrderDetail extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'product_id', 'quantity', 'unit_id'], 'required'],
            [['order_id', 'product_id', 'quantity', 'unit_id'], 'integer'],
            [['unit_price', 'discount_percent', 'discount_amount', 'tax_percent', 'tax_amount', 'total_amount', 'cost_price'], 'number'],
            [['note'], 'string'],
            [['batch_number'], 'string', 'max' => 100],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::class, 'targetAttribute' => ['order_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
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
            'order_id' => 'Đơn hàng',
            'product_id' => 'Sản phẩm',
            'quantity' => 'Số lượng',
            'unit_id' => 'Đơn vị',
            'unit_price' => 'Đơn giá',
            'discount_percent' => 'Chiết khấu (%)',
            'discount_amount' => 'Chiết khấu',
            'tax_percent' => 'Thuế (%)',
            'tax_amount' => 'Thuế',
            'total_amount' => 'Thành tiền',
            'batch_number' => 'Số lô',
            'note' => 'Ghi chú',
            'cost_price' => 'Giá vốn',
        ];
    }

    /**
     * Gets query for [[Order]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
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
     * Gets query for [[Unit]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUnit()
    {
        return $this->hasOne(ProductUnit::class, ['id' => 'unit_id']);
    }

    /**
     * Gets query for [[ReturnDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReturnDetails()
    {
        return $this->hasMany(ReturnDetail::class, ['order_detail_id' => 'id']);
    }

    /**
     * Gets query for [[Warranties]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWarranties()
    {
        return $this->hasMany(Warranty::class, ['order_detail_id' => 'id']);
    }

    /**
     * Calculate total price
     */
    public function calculateTotalPrice()
    {
        $amount = $this->quantity * $this->unit_price;
        
        // Apply discount
        if ($this->discount_percent > 0) {
            $this->discount_amount = $amount * ($this->discount_percent / 100);
        }
        
        if ($this->discount_amount > 0) {
            $amount -= $this->discount_amount;
        }
        
        // Apply tax
        if ($this->tax_percent > 0) {
            $this->tax_amount = $amount * ($this->tax_percent / 100);
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
            
            // Store cost price
            if ($insert && !$this->cost_price) {
                $product = $this->product;
                if ($product) {
                    $this->cost_price = $product->cost_price;
                }
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
        
        // Update order totals
        $this->updateOrderTotals();
    }

    /**
     * After delete
     */
    public function afterDelete()
    {
        parent::afterDelete();
        
        // Update order totals
        $this->updateOrderTotals();
    }

    /**
     * Update order totals
     */
    protected function updateOrderTotals()
    {
        $order = $this->order;
        
        if ($order) {
            $details = self::find()->where(['order_id' => $order->id])->all();
            
            $totalQuantity = 0;
            $subtotal = 0;
            $discountAmount = 0;
            $taxAmount = 0;
            
            foreach ($details as $detail) {
                $totalQuantity += $detail->quantity;
                $subtotal += $detail->quantity * $detail->unit_price;
                $discountAmount += $detail->discount_amount ?: 0;
                $taxAmount += $detail->tax_amount ?: 0;
            }
            
            $totalAmount = $subtotal - $discountAmount + $taxAmount + ($order->shipping_fee ?: 0) - ($order->points_amount ?: 0);
            
            $order->total_quantity = $totalQuantity;
            $order->subtotal = $subtotal;
            $order->discount_amount = $discountAmount;
            $order->tax_amount = $taxAmount;
            $order->total_amount = $totalAmount;
            
            // Update change amount
            $order->change_amount = max(0, $order->paid_amount - $totalAmount);
            
            // Update payment status
            $order->updatePaymentStatus();
            
            // Update points earned
            if ($order->customer_id && $order->status != Order::STATUS_CANCELED) {
                $order->points_earned = $order->calculatePoints();
            }
            
            $order->save(false);
        }
    }

    /**
     * Get available batches for selection
     * 
     * @return array
     */
    public function getAvailableBatches()
    {
        if (!$this->product_id || !$this->order || !$this->order->warehouse_id) {
            return [];
        }
        
        $batches = ProductBatch::find()
            ->where([
                'product_id' => $this->product_id,
                'warehouse_id' => $this->order->warehouse_id,
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

    /**
     * Get returned quantity
     * 
     * @return int
     */
    public function getReturnedQuantity()
    {
        return ReturnDetail::find()
            ->where(['order_detail_id' => $this->id])
            ->sum('quantity') ?: 0;
    }

    /**
     * Check if product can be returned
     * 
     * @param int $quantity
     * @return bool
     */
    public function canReturn($quantity = 1)
    {
        return ($this->quantity - $this->getReturnedQuantity()) >= $quantity;
    }
}