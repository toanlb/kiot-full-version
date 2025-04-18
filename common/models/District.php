<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "district".
 *
 * @property int $id
 * @property int $province_id
 * @property string $name
 * @property string|null $code
 *
 * @property Customer[] $customers
 * @property Province $province
 * @property Supplier[] $suppliers
 * @property Ward[] $wards
 */
class District extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'district';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['province_id', 'name'], 'required'],
            [['province_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 20],
            [['province_id'], 'exist', 'skipOnError' => true, 'targetClass' => Province::class, 'targetAttribute' => ['province_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'province_id' => 'Tỉnh/Thành',
            'name' => 'Tên quận/huyện',
            'code' => 'Mã',
        ];
    }

    /**
     * Gets query for [[Customers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomers()
    {
        return $this->hasMany(Customer::class, ['district_id' => 'id']);
    }

    /**
     * Gets query for [[Province]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProvince()
    {
        return $this->hasOne(Province::class, ['id' => 'province_id']);
    }

    /**
     * Gets query for [[Suppliers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSuppliers()
    {
        return $this->hasMany(Supplier::class, ['district_id' => 'id']);
    }

    /**
     * Gets query for [[Wards]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWards()
    {
        return $this->hasMany(Ward::class, ['district_id' => 'id']);
    }

    /**
     * Get dropdown list
     * 
     * @param int|null $provinceId
     * @return array
     */
    public static function getDropdownList($provinceId = null)
    {
        $query = self::find()->orderBy(['name' => SORT_ASC]);
        
        if ($provinceId) {
            $query->where(['province_id' => $provinceId]);
        }
        
        return \yii\helpers\ArrayHelper::map(
            $query->all(),
            'id',
            'name'
        );
    }

    /**
     * Get districts by province
     * 
     * @param int $provinceId
     * @return array
     */
    public static function getDistrictsByProvince($provinceId)
    {
        return self::find()->where(['province_id' => $provinceId])->orderBy(['name' => SORT_ASC])->all();
    }
}