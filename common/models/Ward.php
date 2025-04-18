<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "ward".
 *
 * @property int $id
 * @property int $district_id
 * @property string $name
 * @property string|null $code
 *
 * @property Customer[] $customers
 * @property District $district
 * @property Supplier[] $suppliers
 */
class Ward extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ward';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['district_id', 'name'], 'required'],
            [['district_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 20],
            [['district_id'], 'exist', 'skipOnError' => true, 'targetClass' => District::class, 'targetAttribute' => ['district_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'district_id' => 'Quận/Huyện',
            'name' => 'Tên phường/xã',
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
        return $this->hasMany(Customer::class, ['ward_id' => 'id']);
    }

    /**
     * Gets query for [[District]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDistrict()
    {
        return $this->hasOne(District::class, ['id' => 'district_id']);
    }

    /**
     * Gets query for [[Suppliers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSuppliers()
    {
        return $this->hasMany(Supplier::class, ['ward_id' => 'id']);
    }

    /**
     * Get dropdown list
     * 
     * @param int|null $districtId
     * @return array
     */
    public static function getDropdownList($districtId = null)
    {
        $query = self::find()->orderBy(['name' => SORT_ASC]);
        
        if ($districtId) {
            $query->where(['district_id' => $districtId]);
        }
        
        return \yii\helpers\ArrayHelper::map(
            $query->all(),
            'id',
            'name'
        );
    }

    /**
     * Get wards by district
     * 
     * @param int $districtId
     * @return array
     */
    public static function getWardsByDistrict($districtId)
    {
        return self::find()->where(['district_id' => $districtId])->orderBy(['name' => SORT_ASC])->all();
    }
}