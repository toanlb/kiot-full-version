<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "province".
 *
 * @property int $id
 * @property string $name
 * @property string|null $code
 * @property string|null $region
 *
 * @property Customer[] $customers
 * @property District[] $districts
 * @property Supplier[] $suppliers
 */
class Province extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'province';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name', 'region'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Tên tỉnh/thành',
            'code' => 'Mã',
            'region' => 'Vùng miền',
        ];
    }

    /**
     * Gets query for [[Customers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomers()
    {
        return $this->hasMany(Customer::class, ['province_id' => 'id']);
    }

    /**
     * Gets query for [[Districts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDistricts()
    {
        return $this->hasMany(District::class, ['province_id' => 'id']);
    }

    /**
     * Gets query for [[Suppliers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSuppliers()
    {
        return $this->hasMany(Supplier::class, ['province_id' => 'id']);
    }

    /**
     * Get dropdown list
     * 
     * @return array
     */
    public static function getDropdownList()
    {
        return \yii\helpers\ArrayHelper::map(
            self::find()->orderBy(['name' => SORT_ASC])->all(),
            'id',
            'name'
        );
    }

    /**
     * Get regions
     * 
     * @return array
     */
    public static function getRegions()
    {
        return \yii\helpers\ArrayHelper::map(
            self::find()->select('region')->distinct()->where(['not', ['region' => null]])->all(),
            'region',
            'region'
        );
    }

    /**
     * Get provinces by region
     * 
     * @param string $region
     * @return array
     */
    public static function getProvincesByRegion($region)
    {
        return self::find()->where(['region' => $region])->orderBy(['name' => SORT_ASC])->all();
    }
}