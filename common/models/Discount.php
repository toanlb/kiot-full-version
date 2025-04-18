<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "discount".
 *
 * @property int $id
 * @property string $name
 * @property string|null $code
 * @property int $discount_type
 * @property float $value
 * @property float|null $min_order_amount
 * @property float|null $max_discount_amount
 * @property string|null $start_date
 * @property string|null $end_date
 * @property int|null $usage_limit
 * @property int $usage_count
 * @property int $is_active
 * @property string $created_at
 * @property string $updated_at
 * @property int|null $created_by
 *
 * @property User $createdBy
 * @property ProductDiscount[] $productDiscounts
 * @property Product[] $products
 * @property ProductCategory[] $productCategories
 */
class Discount extends ActiveRecord
{
    const TYPE_PERCENTAGE = 1;
    const TYPE_AMOUNT = 2;
    const TYPE_ORDER_DISCOUNT = 3;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'discount';
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
                'updatedByAttribute' => false,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'discount_type', 'value'], 'required'],
            [['discount_type', 'usage_limit', 'usage_count', 'is_active', 'created_by'], 'integer'],
            [['value', 'min_order_amount', 'max_discount_amount'], 'number'],
            [['start_date', 'end_date', 'created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 50],
            [['code'], 'unique'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Tên khuyến mãi',
            'code' => 'Mã khuyến mãi',
            'discount_type' => 'Loại khuyến mãi',
            'value' => 'Giá trị',
            'min_order_amount' => 'Giá trị đơn hàng tối thiểu',
            'max_discount_amount' => 'Giảm giá tối đa',
            'start_date' => 'Ngày bắt đầu',
            'end_date' => 'Ngày kết thúc',
            'usage_limit' => 'Giới hạn sử dụng',
            'usage_count' => 'Số lần đã sử dụng',
            'is_active' => 'Hoạt động',
            'created_at' => 'Ngày tạo',
            'updated_at' => 'Ngày cập nhật',
            'created_by' => 'Người tạo',
        ];
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
     * Gets query for [[ProductDiscounts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductDiscounts()
    {
        return $this->hasMany(ProductDiscount::class, ['discount_id' => 'id']);
    }

    /**
     * Gets query for [[Products]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Product::class, ['id' => 'product_id'])->via('productDiscounts');
    }

    /**
     * Gets query for [[ProductCategories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductCategories()
    {
        return $this->hasMany(ProductCategory::class, ['id' => 'product_category_id'])->via('productDiscounts');
    }

    /**
     * Get discount type label
     * 
     * @return string
     */
    public function getDiscountTypeLabel()
    {
        $types = self::getDiscountTypes();
        return isset($types[$this->discount_type]) ? $types[$this->discount_type] : 'Không xác định';
    }

    /**
     * Get discount types
     * 
     * @return array
     */
    public static function getDiscountTypes()
    {
        return [
            self::TYPE_PERCENTAGE => 'Phần trăm',
            self::TYPE_AMOUNT => 'Số tiền',
            self::TYPE_ORDER_DISCOUNT => 'Giảm giá đơn hàng',
        ];
    }

    /**
     * Calculate discount amount
     * 
     * @param float $price
     * @param int $quantity
     * @return float
     */
    public function calculateDiscount($price, $quantity = 1)
    {
        $amount = $price * $quantity;
        $discount = 0;
        
        // Check min order amount
        if ($this->min_order_amount && $amount < $this->min_order_amount) {
            return 0;
        }
        
        switch ($this->discount_type) {
            case self::TYPE_PERCENTAGE:
                $discount = $amount * ($this->value / 100);
                break;
                
            case self::TYPE_AMOUNT:
                $discount = $this->value * $quantity;
                break;
                
            case self::TYPE_ORDER_DISCOUNT:
                $discount = $this->value;
                break;
        }
        
        // Check max discount amount
        if ($this->max_discount_amount && $discount > $this->max_discount_amount) {
            $discount = $this->max_discount_amount;
        }
        
        return $discount;
    }

    /**
     * Check if discount is valid
     * 
     * @return bool
     */
    public function isValid()
    {
        // Check active status
        if (!$this->is_active) {
            return false;
        }
        
        // Check start date
        if ($this->start_date && strtotime($this->start_date) > time()) {
            return false;
        }
        
        // Check end date
        if ($this->end_date && strtotime($this->end_date) < time()) {
            return false;
        }
        
        // Check usage limit
        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return false;
        }
        
        return true;
    }

    /**
     * Find discount by code
     * 
     * @param string $code
     * @return Discount|null
     */
    public static function findByCode($code)
    {
        return self::findOne(['code' => $code, 'is_active' => 1]);
    }

    /**
     * Apply discount
     * 
     * @return bool
     */
    public function applyDiscount()
    {
        if (!$this->isValid()) {
            return false;
        }
        
        $this->usage_count++;
        return $this->save(false);
    }

    /**
     * Find applicable discounts for product
     * 
     * @param int $productId
     * @param int|null $categoryId
     * @return array
     */
    public static function findApplicableDiscounts($productId, $categoryId = null)
    {
        $now = date('Y-m-d H:i:s');
        
        $query = self::find()
            ->alias('d')
            ->leftJoin(['pd' => ProductDiscount::tableName()], 'pd.discount_id = d.id')
            ->where(['d.is_active' => 1])
            ->andWhere(['or', 
                ['IS', 'd.start_date', null], 
                ['<=', 'd.start_date', $now]
            ])
            ->andWhere(['or', 
                ['IS', 'd.end_date', null], 
                ['>=', 'd.end_date', $now]
            ])
            ->andWhere(['or',
                ['IS', 'd.usage_limit', null],
                ['>', 'd.usage_limit', 'd.usage_count']
            ])
            ->andWhere(['or',
                ['pd.product_id' => $productId],
                ['pd.product_category_id' => $categoryId],
                ['IS', 'pd.product_id', null],
                ['IS', 'pd.product_category_id', null],
            ]);
            
        return $query->all();
    }
}