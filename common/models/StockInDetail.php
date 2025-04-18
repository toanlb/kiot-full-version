<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "stock_in_detail".
 *
 * @property int $id
 * @property int $stock_in_id
 * @property int $product_id
 * @property string|null $batch_number
 * @property string|null $expiry_date
 * @property int $quantity
 * @property int $unit_id
 * @property float $unit_price
 * @property float|null $discount_percent
 * @property float|null $discount_amount
 * @property float|null $tax_percent
 * @property float|null $tax_amount
 * @property float $total_price
 * @property string|null $note
 *
 * @property Product $product
 * @property StockIn $stockIn
 * @property ProductUnit $unit
 */
class StockInDetail extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'stock_in_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['stock_in_id', 'product_id', 'quantity', 'unit_id'], 'required'],
            [['stock_in_id', 'product_id', 'quantity', 'unit_id'], 'integer'],
            [['expiry_date'], 'safe'],
            [['unit_price', 'discount_percent', 'discount_amount', 'tax_percent', 'tax_amount', 'total_price'], 'number'],
            [['note'], 'string'],
            [['batch_number'], 'string', 'max' => 100],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['stock_in_id'], 'exist', 'skipOnError' => true, 'targetClass' => StockIn::class, 'targetAttribute' => ['stock_in_id' => 'id']],
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
            'stock_in_id' => 'Phiếu nhập kho',
            'product_id' => 'Sản phẩm',
            'batch_number' => 'Số lô',
            'expiry_date' => 'Ngày hết hạn',
            'quantity' => 'Số lượng',
            'unit_id' => 'Đơn vị',
            'unit_price' => 'Đơn giá',
            'discount_percent' => 'Chiết khấu (%)',
            'discount_amount' => 'Chiết khấu',
            'tax_percent' => 'Thuế (%)',
            'tax_amount' => 'Thuế',
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
     * Gets query for [[StockIn]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStockIn()
    {
        return $this->hasOne(StockIn::class, ['id' => 'stock_in_id']);
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
        
        $this->total_price = $amount;
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
        
        // Update stock in totals
        $this->updateStockInTotals();
    }

    /**
     * After delete
     */
    public function afterDelete()
    {
        parent::afterDelete();
        
        // Update stock in totals
        $this->updateStockInTotals();
    }

    /**
     * Update stock in totals
     */
    protected function updateStockInTotals()
    {
        $stockIn = $this->stockIn;
        
        if ($stockIn) {
            $details = self::find()->where(['stock_in_id' => $stockIn->id])->all();
            
            $totalAmount = 0;
            $discountAmount = 0;
            $taxAmount = 0;
            
            foreach ($details as $detail) {
                $totalAmount += $detail->quantity * $detail->unit_price;
                $discountAmount += $detail->discount_amount ?: 0;
                $taxAmount += $detail->tax_amount ?: 0;
            }
            
            $finalAmount = $totalAmount - $discountAmount + $taxAmount;
            
            $stockIn->total_amount = $totalAmount;
            $stockIn->discount_amount = $discountAmount;
            $stockIn->tax_amount = $taxAmount;
            $stockIn->final_amount = $finalAmount;
            
            // Update payment status
            $stockIn->updatePaymentStatus();
            
            $stockIn->save(false);
        }
    }
}