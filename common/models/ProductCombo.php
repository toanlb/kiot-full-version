<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "product_combo".
 *
 * @property int $id
 * @property int $combo_id
 * @property int $product_id
 * @property int $quantity
 * @property int $unit_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Product $combo
 * @property Product $product
 * @property ProductUnit $unit
 */
class ProductCombo extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_combo';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['combo_id', 'product_id', 'quantity', 'unit_id'], 'required'],
            [['combo_id', 'product_id', 'quantity', 'unit_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['combo_id', 'product_id'], 'unique', 'targetAttribute' => ['combo_id', 'product_id']],
            [['combo_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['combo_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['unit_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductUnit::class, 'targetAttribute' => ['unit_id' => 'id']],
            ['combo_id', 'validateCombo'],
        ];
    }

    /**
     * Validate combo product
     */
    public function validateCombo($attribute, $params)
    {
        if (!$this->hasErrors() && $this->$attribute) {
            if ($this->$attribute == $this->product_id) {
                $this->addError($attribute, 'Sản phẩm không thể là thành phần của chính nó.');
                return;
            }
            
            $product = Product::findOne($this->$attribute);
            if (!$product || $product->is_combo == 0) {
                $this->addError($attribute, 'Sản phẩm combo không hợp lệ.');
                return;
            }
            
            // Check for circular reference
            if ($this->isProductInCombo($this->$attribute, $this->product_id)) {
                $this->addError($attribute, 'Tạo vòng lặp giữa các sản phẩm combo.');
            }
        }
    }

    /**
     * Check if a product is in a combo chain recursively
     * 
     * @param int $productId
     * @param int $comboId
     * @return bool
     */
    protected function isProductInCombo($productId, $comboId)
    {
        $combos = self::find()
            ->where(['product_id' => $comboId])
            ->all();
            
        foreach ($combos as $combo) {
            if ($combo->combo_id == $productId) {
                return true;
            }
            
            if ($this->isProductInCombo($productId, $combo->combo_id)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'combo_id' => 'Sản phẩm combo',
            'product_id' => 'Sản phẩm thành phần',
            'quantity' => 'Số lượng',
            'unit_id' => 'Đơn vị',
            'created_at' => 'Ngày tạo',
            'updated_at' => 'Ngày cập nhật',
        ];
    }

    /**
     * Gets query for [[Combo]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCombo()
    {
        return $this->hasOne(Product::class, ['id' => 'combo_id']);
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
     * Calculate cost price of combo based on components
     * 
     * @param int $comboId
     * @return float
     */
    public static function calculateComboCost($comboId)
    {
        $totalCost = 0;
        
        $comboItems = self::find()
            ->where(['combo_id' => $comboId])
            ->all();
            
        foreach ($comboItems as $item) {
            $product = $item->product;
            
            // Convert quantity if needed
            $quantity = $item->quantity;
            if ($item->unit_id != $product->unit_id) {
                $convertedQuantity = ProductUnitConversion::convert(
                    $item->product_id,
                    $item->unit_id,
                    $product->unit_id,
                    $quantity
                );
                
                if ($convertedQuantity !== null) {
                    $quantity = $convertedQuantity;
                }
            }
            
            $totalCost += $product->cost_price * $quantity;
        }
        
        return $totalCost;
    }
}