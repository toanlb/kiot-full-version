<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "shift".
 *
 * @property int $id
 * @property int $user_id
 * @property int $warehouse_id
 * @property int|null $cashier_id
 * @property string $start_time
 * @property string|null $end_time
 * @property float $opening_amount
 * @property float $total_sales
 * @property float $total_returns
 * @property float $total_receipts
 * @property float $total_payments
 * @property float $expected_amount
 * @property float|null $actual_amount
 * @property float $difference
 * @property string|null $explanation
 * @property int $status
 * @property string|null $note
 * @property string $created_at
 * @property string $updated_at
 *
 * @property CashBook[] $cashBooks
 * @property User $cashier
 * @property Order[] $orders
 * @property ShiftDetail[] $shiftDetails
 * @property User $user
 * @property Warehouse $warehouse
 */
class Shift extends ActiveRecord
{
    const STATUS_OPEN = 0;
    const STATUS_CLOSED = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'shift';
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
            [['user_id', 'warehouse_id', 'start_time'], 'required'],
            [['user_id', 'warehouse_id', 'cashier_id', 'status'], 'integer'],
            [['start_time', 'end_time', 'created_at', 'updated_at'], 'safe'],
            [['opening_amount', 'total_sales', 'total_returns', 'total_receipts', 'total_payments', 'expected_amount', 'actual_amount', 'difference'], 'number'],
            [['explanation', 'note'], 'string'],
            [['cashier_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['cashier_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['warehouse_id'], 'exist', 'skipOnError' => true, 'targetClass' => Warehouse::class, 'targetAttribute' => ['warehouse_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'Người tạo',
            'warehouse_id' => 'Kho',
            'cashier_id' => 'Thu ngân',
            'start_time' => 'Thời gian bắt đầu',
            'end_time' => 'Thời gian kết thúc',
            'opening_amount' => 'Số dư đầu',
            'total_sales' => 'Tổng bán hàng',
            'total_returns' => 'Tổng trả hàng',
            'total_receipts' => 'Tổng thu',
            'total_payments' => 'Tổng chi',
            'expected_amount' => 'Số dư dự kiến',
            'actual_amount' => 'Số dư thực tế',
            'difference' => 'Chênh lệch',
            'explanation' => 'Lý do chênh lệch',
            'status' => 'Trạng thái',
            'note' => 'Ghi chú',
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
        return $this->hasMany(CashBook::class, ['shift_id' => 'id']);
    }

    /**
     * Gets query for [[Cashier]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCashier()
    {
        return $this->hasOne(User::class, ['id' => 'cashier_id']);
    }

    /**
     * Gets query for [[Orders]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::class, ['shift_id' => 'id']);
    }

    /**
     * Gets query for [[ShiftDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShiftDetails()
    {
        return $this->hasMany(ShiftDetail::class, ['shift_id' => 'id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Gets query for [[Warehouse]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWarehouse()
    {
        return $this->hasOne(Warehouse::class, ['id' => 'warehouse_id']);
    }

    /**
     * Get status label
     * 
     * @return string
     */
    public function getStatusLabel()
    {
        $statuses = self::getStatuses();
        return isset($statuses[$this->status]) ? $statuses[$this->status] : 'Không xác định';
    }

    /**
     * Get statuses
     * 
     * @return array
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_OPEN => 'Đang mở',
            self::STATUS_CLOSED => 'Đã đóng',
        ];
    }

    /**
     * Calculate expected amount
     */
    public function calculateExpectedAmount()
    {
        $this->expected_amount = $this->opening_amount + $this->total_sales + $this->total_receipts - $this->total_returns - $this->total_payments;
    }

    /**
     * Calculate difference
     */
    public function calculateDifference()
    {
        if ($this->actual_amount !== null) {
            $this->difference = $this->actual_amount - $this->expected_amount;
        } else {
            $this->difference = 0;
        }
    }

    /**
     * Before save
     * 
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Calculate expected amount
            $this->calculateExpectedAmount();
            
            // Calculate difference
            $this->calculateDifference();
            
            return true;
        }
        
        return false;
    }

    /**
     * Check if shift is closed
     * 
     * @return bool
     */
    public function isClosed()
    {
        return $this->status == self::STATUS_CLOSED;
    }

    /**
     * Find active shift
     * 
     * @param int $warehouseId
     * @param int|null $userId
     * @return Shift|null
     */
    public static function findActive($warehouseId, $userId = null)
    {
        $query = self::find()
            ->where([
                'warehouse_id' => $warehouseId,
                'status' => self::STATUS_OPEN,
            ]);
            
        if ($userId !== null) {
            $query->andWhere(['user_id' => $userId]);
        }
        
        return $query->one();
    }

    /**
     * Close shift
     * 
     * @param float $actualAmount
     * @param string|null $explanation
     * @return bool
     */
    public function closeShift($actualAmount, $explanation = null)
    {
        if ($this->status == self::STATUS_CLOSED) {
            return false;
        }
        
        $this->actual_amount = $actualAmount;
        $this->explanation = $explanation;
        $this->end_time = new Expression('NOW()');
        $this->status = self::STATUS_CLOSED;
        
        return $this->save();
    }

    /**
     * Get total by transaction type
     * 
     * @param int $transactionType
     * @return float
     */
    public function getTotalByTransactionType($transactionType)
    {
        return ShiftDetail::find()
            ->where([
                'shift_id' => $this->id,
                'transaction_type' => $transactionType,
            ])
            ->sum('total_amount') ?: 0;
    }

    /**
     * Get summary by payment method
     * 
     * @return array
     */
    public function getSummaryByPaymentMethod()
    {
        $details = ShiftDetail::find()
            ->alias('sd')
            ->select([
                'sd.payment_method_id', 
                'pm.name', 
                'sd.transaction_type',
                'SUM(sd.total_amount) as total',
                'COUNT(*) as count'
            ])
            ->innerJoin(['pm' => PaymentMethod::tableName()], 'sd.payment_method_id = pm.id')
            ->where(['sd.shift_id' => $this->id])
            ->groupBy(['sd.payment_method_id', 'pm.name', 'sd.transaction_type'])
            ->asArray()
            ->all();
            
        return $details;
    }
}