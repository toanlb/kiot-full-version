<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "product_image".
 *
 * @property int $id
 * @property int $product_id
 * @property string $image
 * @property int|null $sort_order
 * @property int $is_main
 * @property string $created_at
 *
 * @property Product $product
 */
class ProductImage extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_image';
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
            [['product_id', 'image'], 'required'],
            [['product_id', 'sort_order', 'is_main'], 'integer'],
            [['created_at'], 'safe'],
            [['image'], 'string', 'max' => 255],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
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
            'image' => 'Hình ảnh',
            'sort_order' => 'Thứ tự',
            'is_main' => 'Ảnh chính',
            'created_at' => 'Ngày tạo',
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
     * Get image URL
     *
     * @return string
     */
    public function getImageUrl()
    {
        if (strpos($this->image, 'http') === 0) {
            return $this->image;
        }
        
        return Yii::getAlias('@web/uploads/product/' . $this->image);
    }

    /**
     * Get image path
     *
     * @return string
     */
    public function getImagePath()
    {
        return Yii::getAlias('@webroot/uploads/product/' . $this->image);
    }
}