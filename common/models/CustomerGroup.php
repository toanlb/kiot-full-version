<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "customer_group".
 *
 * @property int $id
 * @property string $name
 * @property float|null $discount_rate
 * @property string|null $description
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Customer[] $customers
 */
class CustomerGroup extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'customer_group';
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
            [['discount_rate'], 'number', 'min' => 0, 'max' => 100],
            [['description'], 'string'],
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
            'name' => 'Tên nhóm',
            'discount_rate' => 'Tỷ lệ chiết khấu (%)',
            'description' => 'Mô tả',
            'created_at' => 'Ngày tạo',
            'updated_at' => 'Ngày cập nhật',
        ];
    }

    /**
     * Gets query for [[Customers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomers()
    {
        return $this->hasMany(Customer::class, ['customer_group_id' => 'id']);
    }

    /**
     * Get dropdown list
     * 
     * @return array
     */
    public static function getDropdownList()
    {
        return \yii\helpers\ArrayHelper::map(self::find()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name');
    }

    /**
     * Get customer count
     * 
     * @return int
     */
    public function getCustomerCount()
    {
        return Customer::find()->where(['customer_group_id' => $this->id])->count();
    }
}