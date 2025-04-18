<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "product_unit_conversion".
 *
 * @property int $id
 * @property int $product_id
 * @property int $from_unit_id
 * @property int $to_unit_id
 * @property float $conversion_factor
 * @property string $created_at
 * @property string $updated_at
 *
 * @property ProductUnit $fromUnit
 * @property Product $product
 * @property ProductUnit $toUnit
 */
class ProductUnitConversion extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_unit_conversion';
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
            [['product_id', 'from_unit_id', 'to_unit_id', 'conversion_factor'], 'required'],
            [['product_id', 'from_unit_id', 'to_unit_id'], 'integer'],
            [['conversion_factor'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['product_id', 'from_unit_id', 'to_unit_id'], 'unique', 'targetAttribute' => ['product_id', 'from_unit_id', 'to_unit_id']],
            [['from_unit_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductUnit::class, 'targetAttribute' => ['from_unit_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['to_unit_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductUnit::class, 'targetAttribute' => ['to_unit_id' => 'id']],
            ['conversion_factor', 'compare', 'compareValue' => 0, 'operator' => '>', 'type' => 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Sản phẩm',
            'from_unit_id' => 'Đơn vị nguồn',
            'to_unit_id' => 'Đơn vị đích',
            'conversion_factor' => 'Hệ số chuyển đổi',
            'created_at' => 'Ngày tạo',
            'updated_at' => 'Ngày cập nhật',
        ];
    }

    /**
     * Gets query for [[FromUnit]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFromUnit()
    {
        return $this->hasOne(ProductUnit::class, ['id' => 'from_unit_id']);
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
     * Gets query for [[ToUnit]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getToUnit()
    {
        return $this->hasOne(ProductUnit::class, ['id' => 'to_unit_id']);
    }

    /**
     * Convert quantity from one unit to another
     * 
     * @param int $productId
     * @param int $fromUnitId
     * @param int $toUnitId
     * @param float $quantity
     * @return float|null
     */
    public static function convert($productId, $fromUnitId, $toUnitId, $quantity)
    {
        if ($fromUnitId == $toUnitId) {
            return $quantity;
        }
        
        $conversion = self::findOne([
            'product_id' => $productId,
            'from_unit_id' => $fromUnitId,
            'to_unit_id' => $toUnitId,
        ]);
        
        if ($conversion) {
            return $quantity * $conversion->conversion_factor;
        }
        
        // Check for inverse conversion
        $inverseConversion = self::findOne([
            'product_id' => $productId,
            'from_unit_id' => $toUnitId,
            'to_unit_id' => $fromUnitId,
        ]);
        
        if ($inverseConversion) {
            return $quantity / $inverseConversion->conversion_factor;
        }
        
        // Try to find a path through other conversions
        $conversions = self::find()
            ->where(['product_id' => $productId])
            ->all();
            
        $conversionMap = [];
        foreach ($conversions as $conv) {
            if (!isset($conversionMap[$conv->from_unit_id])) {
                $conversionMap[$conv->from_unit_id] = [];
            }
            $conversionMap[$conv->from_unit_id][$conv->to_unit_id] = $conv->conversion_factor;
            
            if (!isset($conversionMap[$conv->to_unit_id])) {
                $conversionMap[$conv->to_unit_id] = [];
            }
            $conversionMap[$conv->to_unit_id][$conv->from_unit_id] = 1 / $conv->conversion_factor;
        }
        
        // Breadth-first search to find conversion path
        $queue = [[$fromUnitId, $quantity]];
        $visited = [$fromUnitId => true];
        
        while (!empty($queue)) {
            list($currentUnitId, $currentQuantity) = array_shift($queue);
            
            if ($currentUnitId == $toUnitId) {
                return $currentQuantity;
            }
            
            if (isset($conversionMap[$currentUnitId])) {
                foreach ($conversionMap[$currentUnitId] as $nextUnitId => $factor) {
                    if (!isset($visited[$nextUnitId])) {
                        $visited[$nextUnitId] = true;
                        $queue[] = [$nextUnitId, $currentQuantity * $factor];
                    }
                }
            }
        }
        
        return null;
    }
}