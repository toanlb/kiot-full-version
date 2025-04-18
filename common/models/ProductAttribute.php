<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "product_attribute".
 *
 * @property int $id
 * @property string $name
 * @property int|null $sort_order
 * @property int $is_filterable
 * @property string $created_at
 * @property string $updated_at
 *
 * @property ProductAttributeValue[] $productAttributeValues
 * @property Product[] $products
 */
class ProductAttribute extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_attribute';
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
            [['name'], 'required'],
            [['sort_order', 'is_filterable'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Tên thuộc tính',
            'sort_order' => 'Thứ tự',
            'is_filterable' => 'Có thể lọc',
            'created_at' => 'Ngày tạo',
            'updated_at' => 'Ngày cập nhật',
        ];
    }

    /**
     * Gets query for [[ProductAttributeValues]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductAttributeValues()
    {
        return $this->hasMany(ProductAttributeValue::class, ['attribute_id' => 'id']);
    }

    /**
     * Gets query for [[Products]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Product::class, ['id' => 'product_id'])->via('productAttributeValues');
    }

    /**
     * Get dropdown list
     * 
     * @param bool $onlyFilterable Include only filterable attributes
     * @return array
     */
    public static function getDropdownList($onlyFilterable = false)
    {
        $query = self::find()->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC]);
        
        if ($onlyFilterable) {
            $query->andWhere(['is_filterable' => 1]);
        }
        
        return \yii\helpers\ArrayHelper::map($query->all(), 'id', 'name');
    }
}