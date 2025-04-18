<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "payment_method".
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string|null $description
 * @property int $is_default
 * @property int $is_active
 * @property int $require_reference
 * @property string|null $icon
 * @property int|null $sort_order
 * @property string $created_at
 * @property string $updated_at
 *
 * @property CashBook[] $cashBooks
 * @property Order[] $orders
 * @property OrderPayment[] $orderPayments
 * @property Payment[] $payments
 * @property Receipt[] $receipts
 * @property Return[] $returns
 * @property ShiftDetail[] $shiftDetails
 */
class PaymentMethod extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'payment_method';
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
            [['name', 'code'], 'required'],
            [['description'], 'string'],
            [['is_default', 'is_active', 'require_reference', 'sort_order'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'icon'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 50],
            [['code'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Tên phương thức',
            'code' => 'Mã',
            'description' => 'Mô tả',
            'is_default' => 'Mặc định',
            'is_active' => 'Hoạt động',
            'require_reference' => 'Yêu cầu tham chiếu',
            'icon' => 'Biểu tượng',
            'sort_order' => 'Thứ tự',
            'created_at' => 'Ngày tạo',
            'updated_at' => 'Ngày cập nhật',
        ];
    }

    /**
     * Gets query for [[CashBooks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCashBooks()
    {
        return $this->hasMany(CashBook::class, ['payment_method_id' => 'id']);
    }

    /**
     * Gets query for [[Orders]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::class, ['payment_method_id' => 'id']);
    }

    /**
     * Gets query for [[OrderPayments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrderPayments()
    {
        return $this->hasMany(OrderPayment::class, ['payment_method_id' => 'id']);
    }

    /**
     * Gets query for [[Payments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPayments()
    {
        return $this->hasMany(Payment::class, ['payment_method_id' => 'id']);
    }

    /**
     * Gets query for [[Receipts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReceipts()
    {
        return $this->hasMany(Receipt::class, ['payment_method_id' => 'id']);
    }

    /**
     * Gets query for [[Returns]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReturns()
    {
        return $this->hasMany(ReturnModel::class, ['payment_method_id' => 'id']);
    }

    /**
     * Gets query for [[ShiftDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShiftDetails()
    {
        return $this->hasMany(ShiftDetail::class, ['payment_method_id' => 'id']);
    }

    /**
     * Get dropdown list
     * 
     * @param bool $onlyActive
     * @return array
     */
    public static function getDropdownList($onlyActive = true)
    {
        $query = self::find()->orderBy(['is_default' => SORT_DESC, 'sort_order' => SORT_ASC, 'name' => SORT_ASC]);
        
        if ($onlyActive) {
            $query->andWhere(['is_active' => 1]);
        }
        
        return \yii\helpers\ArrayHelper::map($query->all(), 'id', 'name');
    }

    /**
     * After save
     * 
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        
        // If this is default, update other payment methods
        if ($this->is_default) {
            self::updateAll(
                ['is_default' => 0],
                'id != :id',
                [':id' => $this->id]
            );
        }
    }

    /**
     * Get default payment method
     * 
     * @return PaymentMethod|null
     */
    public static function getDefault()
    {
        return self::find()->where(['is_default' => 1, 'is_active' => 1])->one();
    }
}