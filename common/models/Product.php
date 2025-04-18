<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "product".
 *
 * @property int $id
 * @property int|null $category_id
 * @property string $code
 * @property string|null $barcode
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $short_description
 * @property int $unit_id
 * @property float $cost_price
 * @property float $selling_price
 * @property int|null $min_stock
 * @property int $status
 * @property int $is_combo
 * @property float|null $weight
 * @property string|null $dimension
 * @property int|null $warranty_period
 * @property string $created_at
 * @property string $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 *
 * @property OrderDetail[] $orderDetails
 * @property ProductAttributeValue[] $productAttributeValues
 * @property ProductAttribute[] $attributes
 * @property ProductBatch[] $productBatches
 * @property ProductCategory $category
 * @property ProductCombo[] $productCombos
 * @property ProductCombo[] $productComboDetails
 * @property ProductDiscount[] $productDiscounts
 * @property ProductImage[] $productImages
 * @property ProductImage $mainImage
 * @property ProductPriceHistory[] $productPriceHistories
 * @property ProductUnit $unit
 * @property ProductUnitConversion[] $productUnitConversions
 * @property ReturnDetail[] $returnDetails
 * @property Stock[] $stocks
 * @property StockInDetail[] $stockInDetails
 * @property StockMovement[] $stockMovements
 * @property StockOutDetail[] $stockOutDetails
 * @property StockTransferDetail[] $stockTransferDetails
 * @property SupplierProduct[] $supplierProducts
 * @property Supplier[] $suppliers
 * @property User $createdBy
 * @property User $updatedBy
 * @property Warranty[] $warranties
 * @property WarrantyDetail[] $warrantyDetails
 */
class Product extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product';
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
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['category_id', 'unit_id', 'min_stock', 'status', 'is_combo', 'warranty_period', 'created_by', 'updated_by'], 'integer'],
            [['code', 'name', 'slug', 'unit_id'], 'required'],
            [['description', 'short_description'], 'string'],
            [['cost_price', 'selling_price', 'weight'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['code', 'barcode'], 'string', 'max' => 50],
            [['name', 'slug'], 'string', 'max' => 255],
            [['dimension'], 'string', 'max' => 50],
            [['code'], 'unique'],
            [['slug'], 'unique'],
            [['barcode'], 'unique'],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductCategory::class, 'targetAttribute' => ['category_id' => 'id']],
            [['unit_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductUnit::class, 'targetAttribute' => ['unit_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'category_id' => 'Danh mục',
            'code' => 'Mã sản phẩm',
            'barcode' => 'Mã vạch',
            'name' => 'Tên sản phẩm',
            'slug' => 'Slug',
            'description' => 'Mô tả',
            'short_description' => 'Mô tả ngắn',
            'unit_id' => 'Đơn vị',
            'cost_price' => 'Giá nhập',
            'selling_price' => 'Giá bán',
            'min_stock' => 'Tồn kho tối thiểu',
            'status' => 'Trạng thái',
            'is_combo' => 'Là combo',
            'weight' => 'Cân nặng',
            'dimension' => 'Kích thước',
            'warranty_period' => 'Thời gian bảo hành',
            'created_at' => 'Ngày tạo',
            'updated_at' => 'Ngày cập nhật',
            'created_by' => 'Người tạo',
            'updated_by' => 'Người cập nhật',
        ];
    }

    /**
     * Gets query for [[OrderDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrderDetails()
    {
        return $this->hasMany(OrderDetail::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[ProductAttributeValues]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductAttributeValues()
    {
        return $this->hasMany(ProductAttributeValue::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[Attributes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAttributes0()
    {
        return $this->hasMany(ProductAttribute::class, ['id' => 'attribute_id'])->via('productAttributeValues');
    }

    /**
     * Gets query for [[ProductBatches]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductBatches()
    {
        return $this->hasMany(ProductBatch::class, ['product_id' => 'id']);
    }

    /**
     * Lấy danh sách sản phẩm dưới dạng mảng key-value (id => name)
     * @param bool $onlyActive Chỉ lấy sản phẩm hoạt động
     * @return array
     */
    public static function getList($onlyActive = true)
    {
        $query = self::find();
        
        if ($onlyActive) {
            $query->where(['status' => 1]);
        }
        
        $products = $query->orderBy(['name' => SORT_ASC])->all();
        
        return ArrayHelper::map($products, 'id', function($model) {
            return $model->code . ' - ' . $model->name;
        });
    }

    /**
     * Gets query for [[Category]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(ProductCategory::class, ['id' => 'category_id']);
    }

    /**
     * Gets query for [[ProductCombos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductCombos()
    {
        return $this->hasMany(ProductCombo::class, ['combo_id' => 'id']);
    }

    /**
     * Gets query for [[ProductComboDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductComboDetails()
    {
        return $this->hasMany(ProductCombo::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[ProductDiscounts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductDiscounts()
    {
        return $this->hasMany(ProductDiscount::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[ProductImages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductImages()
    {
        return $this->hasMany(ProductImage::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[MainImage]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMainImage()
    {
        return $this->hasOne(ProductImage::class, ['product_id' => 'id'])->andWhere(['is_main' => 1]);
    }

    /**
     * Gets query for [[ProductPriceHistories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductPriceHistories()
    {
        return $this->hasMany(ProductPriceHistory::class, ['product_id' => 'id']);
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
     * Gets query for [[ProductUnitConversions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductUnitConversions()
    {
        return $this->hasMany(ProductUnitConversion::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[ReturnDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReturnDetails()
    {
        return $this->hasMany(ReturnDetail::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[Stocks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStocks()
    {
        return $this->hasMany(Stock::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[StockInDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStockInDetails()
    {
        return $this->hasMany(StockInDetail::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[StockMovements]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStockMovements()
    {
        return $this->hasMany(StockMovement::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[StockOutDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStockOutDetails()
    {
        return $this->hasMany(StockOutDetail::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[StockTransferDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStockTransferDetails()
    {
        return $this->hasMany(StockTransferDetail::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[SupplierProducts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSupplierProducts()
    {
        return $this->hasMany(SupplierProduct::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[Suppliers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSuppliers()
    {
        return $this->hasMany(Supplier::class, ['id' => 'supplier_id'])->via('supplierProducts');
    }

    /**
     * Gets query for [[CreatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * Gets query for [[UpdatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }

    /**
     * Gets query for [[Warranties]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWarranties()
    {
        return $this->hasMany(Warranty::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[WarrantyDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWarrantyDetails()
    {
        return $this->hasMany(WarrantyDetail::class, ['replacement_product_id' => 'id']);
    }

    /**
     * Get stock quantity in a specific warehouse
     * 
     * @param int $warehouseId
     * @return int
     */
    public function getStockQuantity($warehouseId = null)
    {
        $query = Stock::find()->where(['product_id' => $this->id]);
        
        if ($warehouseId) {
            $query->andWhere(['warehouse_id' => $warehouseId]);
        }
        
        return $query->sum('quantity') ?: 0;
    }

    /**
     * Check if product is in stock in a specific warehouse
     * 
     * @param int $quantity
     * @param int $warehouseId
     * @return bool
     */
    public function isInStock($quantity = 1, $warehouseId = null)
    {
        return $this->getStockQuantity($warehouseId) >= $quantity;
    }
}