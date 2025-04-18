<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\ArrayHelper;

/**
 * This is the model class for table "product_unit".
 *
 * @property int $id
 * @property string $name
 * @property string|null $abbreviation
 * @property int $is_default
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Product[] $products
 * @property ProductCombo[] $productCombos
 * @property ProductUnitConversion[] $fromProductUnitConversions
 * @property ProductUnitConversion[] $toProductUnitConversions
 * @property StockInDetail[] $stockInDetails
 * @property StockMovement[] $stockMovements
 * @property StockOutDetail[] $stockOutDetails
 * @property StockTransferDetail[] $stockTransferDetails
 */
class ProductUnit extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_unit';
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
            [['is_default'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['abbreviation'], 'string', 'max' => 10],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Tên đơn vị',
            'abbreviation' => 'Viết tắt',
            'is_default' => 'Mặc định',
            'created_at' => 'Ngày tạo',
            'updated_at' => 'Ngày cập nhật',
        ];
    }

    /**
     * Gets query for [[Products]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Product::class, ['unit_id' => 'id']);
    }

    /**
     * Gets query for [[ProductCombos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductCombos()
    {
        return $this->hasMany(ProductCombo::class, ['unit_id' => 'id']);
    }

    /**
     * Gets query for [[FromProductUnitConversions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFromProductUnitConversions()
    {
        return $this->hasMany(ProductUnitConversion::class, ['from_unit_id' => 'id']);
    }

    /**
     * Gets query for [[ToProductUnitConversions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getToProductUnitConversions()
    {
        return $this->hasMany(ProductUnitConversion::class, ['to_unit_id' => 'id']);
    }

    /**
     * Gets query for [[StockInDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStockInDetails()
    {
        return $this->hasMany(StockInDetail::class, ['unit_id' => 'id']);
    }

    /**
     * Gets query for [[StockMovements]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStockMovements()
    {
        return $this->hasMany(StockMovement::class, ['unit_id' => 'id']);
    }

    /**
     * Gets query for [[StockOutDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStockOutDetails()
    {
        return $this->hasMany(StockOutDetail::class, ['unit_id' => 'id']);
    }

    /**
     * Gets query for [[StockTransferDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStockTransferDetails()
    {
        return $this->hasMany(StockTransferDetail::class, ['unit_id' => 'id']);
    }
    
    /**
     * Get dropdown list
     * 
     * @param bool $onlyDefault Include only default unit
     * @return array
     */
    public static function getDropdownList($onlyDefault = false)
    {
        $query = self::find()->orderBy(['is_default' => SORT_DESC, 'name' => SORT_ASC]);
        
        if ($onlyDefault) {
            $query->andWhere(['is_default' => 1]);
        }
        
        return \yii\helpers\ArrayHelper::map($query->all(), 'id', 'name');
    }

    /**
     * Lấy danh sách đơn vị tính dưới dạng mảng key-value (id => name)
     * @return array
     */
    public static function getList()
    {
        $units = self::find()->orderBy(['is_default' => SORT_DESC, 'name' => SORT_ASC])->all();
        
        return ArrayHelper::map($units, 'id', 'name');
    }
}