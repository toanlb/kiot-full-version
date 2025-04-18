<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "product_batch".
 *
 * @property int $id
 * @property int $product_id
 * @property int $warehouse_id
 * @property string $batch_number
 * @property string|null $manufacturing_date
 * @property string|null $expiry_date
 * @property int $quantity
 * @property float $cost_price
 * @property int|null $stock_in_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Product $product
 * @property StockIn $stockIn
 * @property Warehouse $warehouse
 */
class ProductBatch extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_batch';
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
            [['product_id', 'warehouse_id', 'batch_number'], 'required'],
            [['product_id', 'warehouse_id', 'quantity', 'stock_in_id'], 'integer'],
            [['manufacturing_date', 'expiry_date', 'created_at', 'updated_at'], 'safe'],
            [['cost_price'], 'number'],
            [['batch_number'], 'string', 'max' => 100],
            [['product_id', 'warehouse_id', 'batch_number'], 'unique', 'targetAttribute' => ['product_id', 'warehouse_id', 'batch_number']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['stock_in_id'], 'exist', 'skipOnError' => true, 'targetClass' => StockIn::class, 'targetAttribute' => ['stock_in_id' => 'id']],
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
            'batch_number' => 'Số lô',
            'manufacturing_date' => 'Ngày sản xuất',
            'expiry_date' => 'Ngày hết hạn',
            'quantity' => 'Số lượng',
            'cost_price' => 'Giá nhập',
            'stock_in_id' => 'Phiếu nhập kho',
            'created_at' => 'Ngày tạo',
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
     * Gets query for [[StockIn]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStockIn()
    {
        return $this->hasOne(StockIn::class, ['id' => 'stock_in_id']);
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
     * Get expiry status
     * 
     * @return int 0: not expired, 1: expiring soon, 2: expired
     */
    public function getExpiryStatus()
    {
        if (!$this->expiry_date) {
            return 0;
        }
        
        $today = date('Y-m-d');
        $expiry = date('Y-m-d', strtotime($this->expiry_date));
        
        if ($expiry < $today) {
            return 2; // expired
        }
        
        $interval = date_diff(date_create($today), date_create($expiry));
        $daysRemaining = $interval->days;
        
        if ($daysRemaining <= 30) {
            return 1; // expiring soon
        }
        
        return 0; // not expired
    }

    /**
     * Check if batch has expired
     * 
     * @return bool
     */
    public function isExpired()
    {
        return $this->getExpiryStatus() == 2;
    }

    /**
     * Check if batch is expiring soon
     * 
     * @return bool
     */
    public function isExpiringSoon()
    {
        return $this->getExpiryStatus() == 1;
    }

    /**
     * Get expiry status label
     * 
     * @return string
     */
    public function getExpiryStatusLabel()
    {
        $status = $this->getExpiryStatus();
        
        switch ($status) {
            case 0:
                return 'Còn hạn';
            case 1:
                return 'Sắp hết hạn';
            case 2:
                return 'Đã hết hạn';
            default:
                return 'Không xác định';
        }
    }

    /**
     * Get days until expiry
     * 
     * @return int|null
     */
    public function getDaysUntilExpiry()
    {
        if (!$this->expiry_date) {
            return null;
        }
        
        $today = date_create(date('Y-m-d'));
        $expiry = date_create(date('Y-m-d', strtotime($this->expiry_date)));
        
        $interval = date_diff($today, $expiry);
        
        return $interval->invert ? -$interval->days : $interval->days;
    }

    /**
     * Find available batches for a product in a warehouse
     * 
     * @param int $productId
     * @param int $warehouseId
     * @param int $requiredQuantity
     * @param bool $preferFefo First Expiry First Out
     * @return array
     */
    public static function findAvailableBatches($productId, $warehouseId, $requiredQuantity = 1, $preferFefo = true)
    {
        $query = self::find()
            ->where([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
            ])
            ->andWhere(['>', 'quantity', 0]);
            
        if ($preferFefo) {
            $query->orderBy(['expiry_date' => SORT_ASC, 'id' => SORT_ASC]);
        } else {
            $query->orderBy(['id' => SORT_ASC]);
        }
        
        return $query->all();
    }
}