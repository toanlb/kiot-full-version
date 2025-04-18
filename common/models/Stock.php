<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "stock".
 *
 * @property int $id
 * @property int $product_id
 * @property int $warehouse_id
 * @property int $quantity
 * @property int|null $min_stock
 * @property string $updated_at
 *
 * @property Product $product
 * @property Warehouse $warehouse
 */
class Stock extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'stock';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => false,
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
            [['product_id', 'warehouse_id'], 'required'],
            [['product_id', 'warehouse_id', 'quantity', 'min_stock'], 'integer'],
            [['updated_at'], 'safe'],
            [['product_id', 'warehouse_id'], 'unique', 'targetAttribute' => ['product_id', 'warehouse_id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['warehouse_id'], 'exist', 'skipOnError' => true, 'targetClass' => Warehouse::class, 'targetAttribute' => ['warehouse_id' => 'id']],
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
            'warehouse_id' => 'Kho',
            'quantity' => 'Số lượng',
            'min_stock' => 'Tồn kho tối thiểu',
            'updated_at' => 'Ngày cập nhật',
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
     * Gets query for [[Warehouse]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWarehouse()
    {
        return $this->hasOne(Warehouse::class, ['id' => 'warehouse_id']);
    }

    /**
     * Increase stock
     * 
     * @param int $productId
     * @param int $warehouseId
     * @param int $quantity
     * @return bool
     */
    public static function increase($productId, $warehouseId, $quantity)
    {
        if ($quantity <= 0) {
            return false;
        }
        
        $stock = self::findOne(['product_id' => $productId, 'warehouse_id' => $warehouseId]);
        
        if (!$stock) {
            $stock = new self();
            $stock->product_id = $productId;
            $stock->warehouse_id = $warehouseId;
            $stock->quantity = 0;
        }
        
        $stock->quantity += $quantity;
        
        return $stock->save();
    }

    /**
     * Decrease stock
     * 
     * @param int $productId
     * @param int $warehouseId
     * @param int $quantity
     * @param bool $allowNegative
     * @return bool
     */
    public static function decrease($productId, $warehouseId, $quantity, $allowNegative = false)
    {
        if ($quantity <= 0) {
            return false;
        }
        
        $stock = self::findOne(['product_id' => $productId, 'warehouse_id' => $warehouseId]);
        
        if (!$stock) {
            $stock = new self();
            $stock->product_id = $productId;
            $stock->warehouse_id = $warehouseId;
            $stock->quantity = 0;
        }
        
        if (!$allowNegative && $stock->quantity < $quantity) {
            return false;
        }
        
        $stock->quantity -= $quantity;
        
        return $stock->save();
    }

    /**
     * Check if stock is below min_stock
     * 
     * @return bool
     */
    public function isBelowMinStock()
    {
        $minStock = $this->min_stock;
        
        if ($minStock === null) {
            $minStock = $this->product->min_stock;
        }
        
        return $minStock !== null && $this->quantity < $minStock;
    }

    /**
     * Get low stock products
     * 
     * @param int|null $warehouseId
     * @return array
     */
    public static function getLowStockProducts($warehouseId = null)
    {
        $query = self::find()
            ->alias('s')
            ->innerJoin(['p' => Product::tableName()], 's.product_id = p.id')
            ->where([
                'OR',
                'p.min_stock IS NOT NULL AND s.quantity < p.min_stock',
                's.min_stock IS NOT NULL AND s.quantity < s.min_stock'
            ]);
            
        if ($warehouseId) {
            $query->andWhere(['s.warehouse_id' => $warehouseId]);
        }
        
        return $query->all();
    }
}