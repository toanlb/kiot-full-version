<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\behaviors\BlameableBehavior;

/**
 * This is the model class for table "product_price_history".
 *
 * @property int $id
 * @property int $product_id
 * @property float $cost_price
 * @property float $selling_price
 * @property string $effective_date
 * @property string $created_at
 * @property int|null $created_by
 * @property string|null $note
 *
 * @property Product $product
 * @property User $createdBy
 */
class ProductPriceHistory extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_price_history';
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
            [['product_id', 'cost_price', 'selling_price', 'effective_date'], 'required'],
            [['product_id', 'created_by'], 'integer'],
            [['cost_price', 'selling_price'], 'number'],
            [['effective_date', 'created_at'], 'safe'],
            [['note'], 'string'],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
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
            'product_id' => 'Sản phẩm',
            'cost_price' => 'Giá nhập',
            'selling_price' => 'Giá bán',
            'effective_date' => 'Ngày áp dụng',
            'created_at' => 'Ngày tạo',
            'created_by' => 'Người tạo',
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
     * Gets query for [[CreatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * Record price change history
     * 
     * @param int $productId
     * @param float $costPrice
     * @param float $sellingPrice
     * @param string $note
     * @param string|null $effectiveDate
     * @return bool
     */
    public static function recordPriceChange($productId, $costPrice, $sellingPrice, $note = '', $effectiveDate = null)
    {
        $model = new self();
        $model->product_id = $productId;
        $model->cost_price = $costPrice;
        $model->selling_price = $sellingPrice;
        $model->note = $note;
        $model->effective_date = $effectiveDate ?: new Expression('NOW()');
        
        return $model->save();
    }

    /**
     * Get price history for product
     * 
     * @param int $productId
     * @param int $limit
     * @return array
     */
    public static function getProductPriceHistory($productId, $limit = 10)
    {
        return self::find()
            ->where(['product_id' => $productId])
            ->orderBy(['effective_date' => SORT_DESC])
            ->limit($limit)
            ->all();
    }
}