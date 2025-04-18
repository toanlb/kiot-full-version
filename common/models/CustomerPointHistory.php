<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "customer_point_history".
 *
 * @property int $id
 * @property int $customer_id
 * @property int|null $reference_id
 * @property string|null $reference_type
 * @property int $points
 * @property int $balance
 * @property int $type
 * @property string|null $note
 * @property string $created_at
 * @property int|null $created_by
 *
 * @property User $createdBy
 * @property Customer $customer
 */
class CustomerPointHistory extends ActiveRecord
{
    const TYPE_ADD = 1;
    const TYPE_DEDUCT = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'customer_point_history';
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
            [['customer_id', 'points', 'balance', 'type'], 'required'],
            [['customer_id', 'reference_id', 'points', 'balance', 'type', 'created_by'], 'integer'],
            [['note'], 'string'],
            [['created_at'], 'safe'],
            [['reference_type'], 'string', 'max' => 50],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customer::class, 'targetAttribute' => ['customer_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'customer_id' => 'Khách hàng',
            'reference_id' => 'ID tham chiếu',
            'reference_type' => 'Loại tham chiếu',
            'points' => 'Điểm',
            'balance' => 'Số dư',
            'type' => 'Loại',
            'note' => 'Ghi chú',
            'created_at' => 'Ngày tạo',
            'created_by' => 'Người tạo',
        ];
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
     * Gets query for [[Customer]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['id' => 'customer_id']);
    }

    /**
     * Get type label
     * 
     * @return string
     */
    public function getTypeLabel()
    {
        $types = self::getTypes();
        return isset($types[$this->type]) ? $types[$this->type] : 'Không xác định';
    }

    /**
     * Get types
     * 
     * @return array
     */
    public static function getTypes()
    {
        return [
            self::TYPE_ADD => 'Cộng điểm',
            self::TYPE_DEDUCT => 'Trừ điểm',
        ];
    }

    /**
     * Get reference label
     * 
     * @return string
     */
    public function getReferenceLabel()
    {
        if (!$this->reference_id || !$this->reference_type) {
            return '';
        }
        
        switch ($this->reference_type) {
            case 'order':
                $model = Order::findOne($this->reference_id);
                return $model ? 'Đơn hàng: ' . $model->code : '';
                
            case 'return':
                $model = ReturnModel::findOne($this->reference_id);
                return $model ? 'Trả hàng: ' . $model->code : '';
                
            default:
                return $this->reference_type . ': ' . $this->reference_id;
        }
    }

    /**
     * Format points
     * 
     * @return string
     */
    public function getFormattedPoints()
    {
        $prefix = $this->type == self::TYPE_ADD ? '+' : '-';
        return $prefix . number_format($this->points);
    }
}