<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "supplier_product".
 *
 * @property int $id
 * @property int $supplier_id
 * @property int $product_id
 * @property string|null $supplier_product_code
 * @property string|null $supplier_product_name
 * @property float $unit_price
 * @property int|null $min_order_quantity
 * @property int|null $lead_time
 * @property int $is_primary_supplier
 * @property string|null $last_purchase_date
 * @property float|null $last_purchase_price
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Product $product
 * @property Supplier $supplier
 */
class SupplierProduct extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'supplier_product';
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
            [['supplier_id', 'product_id'], 'required'],
            [['supplier_id', 'product_id', 'min_order_quantity', 'lead_time', 'is_primary_supplier'], 'integer'],
            [['unit_price', 'last_purchase_price'], 'number'],
            [['last_purchase_date', 'created_at', 'updated_at'], 'safe'],
            [['supplier_product_code'], 'string', 'max' => 50],
            [['supplier_product_name'], 'string', 'max' => 255],
            [['supplier_id', 'product_id'], 'unique', 'targetAttribute' => ['supplier_id', 'product_id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['supplier_id'], 'exist', 'skipOnError' => true, 'targetClass' => Supplier::class, 'targetAttribute' => ['supplier_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'supplier_id' => 'Nhà cung cấp',
            'product_id' => 'Sản phẩm',
            'supplier_product_code' => 'Mã sản phẩm của NCC',
            'supplier_product_name' => 'Tên sản phẩm của NCC',
            'unit_price' => 'Đơn giá',
            'min_order_quantity' => 'Số lượng đặt tối thiểu',
            'lead_time' => 'Thời gian giao hàng (ngày)',
            'is_primary_supplier' => 'NCC chính',
            'last_purchase_date' => 'Lần mua cuối',
            'last_purchase_price' => 'Giá mua cuối',
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
     * Gets query for [[Supplier]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSupplier()
    {
        return $this->hasOne(Supplier::class, ['id' => 'supplier_id']);
    }

    /**
     * Update purchase info
     * 
     * @param float|null $price
     * @return bool
     */
    public function updatePurchaseInfo($price = null)
    {
        $this->last_purchase_date = new Expression('NOW()');
        
        if ($price !== null) {
            $this->last_purchase_price = $price;
        }
        
        return $this->save();
    }

    /**
     * Find primary supplier for product
     * 
     * @param int $productId
     * @return SupplierProduct|null
     */
    public static function findPrimarySupplier($productId)
    {
        return self::find()
            ->where(['product_id' => $productId, 'is_primary_supplier' => 1])
            ->one();
    }

    /**
     * Get suppliers for product
     * 
     * @param int $productId
     * @return array
     */
    public static function getSuppliersForProduct($productId)
    {
        $suppliers = self::find()
            ->alias('sp')
            ->select(['s.id', 's.name', 'sp.unit_price'])
            ->innerJoin(['s' => Supplier::tableName()], 's.id = sp.supplier_id')
            ->where(['sp.product_id' => $productId, 's.status' => Supplier::STATUS_ACTIVE])
            ->orderBy(['sp.is_primary_supplier' => SORT_DESC, 'sp.unit_price' => SORT_ASC])
            ->asArray()
            ->all();
            
        return $suppliers;
    }

    /**
     * Find cheapest supplier for product
     * 
     * @param int $productId
     * @return SupplierProduct|null
     */
    public static function findCheapestSupplier($productId)
    {
        return self::find()
            ->alias('sp')
            ->innerJoin(['s' => Supplier::tableName()], 's.id = sp.supplier_id')
            ->where(['sp.product_id' => $productId, 's.status' => Supplier::STATUS_ACTIVE])
            ->orderBy(['sp.unit_price' => SORT_ASC])
            ->one();
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
        
        // If this is primary supplier, update other suppliers
        if ($this->is_primary_supplier) {
            self::updateAll(
                ['is_primary_supplier' => 0],
                'product_id = :product_id AND supplier_id != :supplier_id',
                [':product_id' => $this->product_id, ':supplier_id' => $this->supplier_id]
            );
        }
    }
}