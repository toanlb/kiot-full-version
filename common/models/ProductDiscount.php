<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "product_discount".
 *
 * @property int $id
 * @property int $discount_id
 * @property int|null $product_id
 * @property int|null $product_category_id
 * @property string $created_at
 *
 * @property Discount $discount
 * @property Product $product
 * @property ProductCategory $productCategory
 */
class ProductDiscount extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_discount';
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
                'updatedAtAttribute' => false,
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
            [['discount_id'], 'required'],
            [['discount_id', 'product_id', 'product_category_id'], 'integer'],
            [['created_at'], 'safe'],
            [['discount_id'], 'exist', 'skipOnError' => true, 'targetClass' => Discount::class, 'targetAttribute' => ['discount_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['product_category_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductCategory::class, 'targetAttribute' => ['product_category_id' => 'id']],
            [['discount_id', 'product_id'], 'unique', 'targetAttribute' => ['discount_id', 'product_id'], 'when' => function($model) {
                return $model->product_id !== null;
            }],
            [['discount_id', 'product_category_id'], 'unique', 'targetAttribute' => ['discount_id', 'product_category_id'], 'when' => function($model) {
                return $model->product_category_id !== null;
            }],
            [['product_id', 'product_category_id'], 'validateTarget'],
        ];
    }

    /**
     * Validate that either product_id or product_category_id is set, but not both
     */
    public function validateTarget($attribute, $params)
    {
        if (!$this->hasErrors()) {
            if ($this->product_id !== null && $this->product_category_id !== null) {
                $this->addError('product_id', 'Chỉ có thể chọn Sản phẩm hoặc Danh mục sản phẩm, không thể chọn cả hai.');
                $this->addError('product_category_id', 'Chỉ có thể chọn Sản phẩm hoặc Danh mục sản phẩm, không thể chọn cả hai.');
            }
            
            if ($this->product_id === null && $this->product_category_id === null) {
                $this->addError('product_id', 'Phải chọn Sản phẩm hoặc Danh mục sản phẩm.');
                $this->addError('product_category_id', 'Phải chọn Sản phẩm hoặc Danh mục sản phẩm.');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'discount_id' => 'Khuyến mãi',
            'product_id' => 'Sản phẩm',
            'product_category_id' => 'Danh mục sản phẩm',
            'created_at' => 'Ngày tạo',
        ];
    }

    /**
     * Gets query for [[Discount]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDiscount()
    {
        return $this->hasOne(Discount::class, ['id' => 'discount_id']);
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
     * Gets query for [[ProductCategory]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductCategory()
    {
        return $this->hasOne(ProductCategory::class, ['id' => 'product_category_id']);
    }

    /**
     * Get discount target name
     * 
     * @return string
     */
    public function getTargetName()
    {
        if ($this->product_id) {
            return $this->product ? $this->product->name : 'Sản phẩm #' . $this->product_id;
        } elseif ($this->product_category_id) {
            return $this->productCategory ? $this->productCategory->name : 'Danh mục #' . $this->product_category_id;
        } else {
            return 'Không xác định';
        }
    }

    /**
     * Find active discounts for product
     * 
     * @param int $productId
     * @return array
     */
    public static function findActiveDiscounts($productId)
    {
        $product = Product::findOne($productId);
        
        if (!$product) {
            return [];
        }
        
        $now = date('Y-m-d H:i:s');
        
        $query = self::find()
            ->alias('pd')
            ->innerJoin(['d' => Discount::tableName()], 'd.id = pd.discount_id')
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
                ['pd.product_category_id' => $product->category_id],
            ]);
            
        return $query->all();
    }

    /**
     * Find active discounts for category
     * 
     * @param int $categoryId
     * @return array
     */
    public static function findActiveDiscountsForCategory($categoryId)
    {
        $now = date('Y-m-d H:i:s');
        
        $query = self::find()
            ->alias('pd')
            ->innerJoin(['d' => Discount::tableName()], 'd.id = pd.discount_id')
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
            ->andWhere(['pd.product_category_id' => $categoryId]);
            
        return $query->all();
    }
}