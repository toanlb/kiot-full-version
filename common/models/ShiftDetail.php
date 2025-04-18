<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "shift_detail".
 *
 * @property int $id
 * @property int $shift_id
 * @property int $payment_method_id
 * @property int $transaction_type
 * @property float $total_amount
 * @property int $transaction_count
 * @property string|null $note
 *
 * @property PaymentMethod $paymentMethod
 * @property Shift $shift
 */
class ShiftDetail extends ActiveRecord
{
    const TYPE_SALES = 1;
    const TYPE_RETURNS = 2;
    const TYPE_RECEIPTS = 3;
    const TYPE_PAYMENTS = 4;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'shift_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['shift_id', 'payment_method_id', 'transaction_type'], 'required'],
            [['shift_id', 'payment_method_id', 'transaction_type', 'transaction_count'], 'integer'],
            [['total_amount'], 'number'],
            [['note'], 'string'],
            [['payment_method_id'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentMethod::class, 'targetAttribute' => ['payment_method_id' => 'id']],
            [['shift_id'], 'exist', 'skipOnError' => true, 'targetClass' => Shift::class, 'targetAttribute' => ['shift_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'shift_id' => 'Ca làm việc',
            'payment_method_id' => 'Phương thức thanh toán',
            'transaction_type' => 'Loại giao dịch',
            'total_amount' => 'Tổng tiền',
            'transaction_count' => 'Số giao dịch',
            'note' => 'Ghi chú',
        ];
    }

    /**
     * Gets query for [[PaymentMethod]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentMethod()
    {
        return $this->hasOne(PaymentMethod::class, ['id' => 'payment_method_id']);
    }

    /**
     * Gets query for [[Shift]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShift()
    {
        return $this->hasOne(Shift::class, ['id' => 'shift_id']);
    }

    /**
     * Get transaction type label
     * 
     * @return string
     */
    public function getTransactionTypeLabel()
    {
        $types = self::getTransactionTypes();
        return isset($types[$this->transaction_type]) ? $types[$this->transaction_type] : 'Không xác định';
    }

    /**
     * Get transaction types
     * 
     * @return array
     */
    public static function getTransactionTypes()
    {
        return [
            self::TYPE_SALES => 'Bán hàng',
            self::TYPE_RETURNS => 'Trả hàng',
            self::TYPE_RECEIPTS => 'Thu',
            self::TYPE_PAYMENTS => 'Chi',
        ];
    }

    /**
     * Update shift totals
     * 
     * @param bool $save
     * @return bool
     */
    public function updateShiftTotals($save = true)
    {
        $shift = $this->shift;
        
        if (!$shift) {
            return false;
        }
        
        // Reset totals
        $shift->total_sales = ShiftDetail::find()
            ->where(['shift_id' => $shift->id, 'transaction_type' => self::TYPE_SALES])
            ->sum('total_amount') ?: 0;
            
        $shift->total_returns = ShiftDetail::find()
            ->where(['shift_id' => $shift->id, 'transaction_type' => self::TYPE_RETURNS])
            ->sum('total_amount') ?: 0;
            
        $shift->total_receipts = ShiftDetail::find()
            ->where(['shift_id' => $shift->id, 'transaction_type' => self::TYPE_RECEIPTS])
            ->sum('total_amount') ?: 0;
            
        $shift->total_payments = ShiftDetail::find()
            ->where(['shift_id' => $shift->id, 'transaction_type' => self::TYPE_PAYMENTS])
            ->sum('total_amount') ?: 0;
            
        // Calculate expected amount
        $shift->calculateExpectedAmount();
        
        // Calculate difference
        $shift->calculateDifference();
        
        if ($save) {
            return $shift->save(false);
        }
        
        return true;
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
        
        // Update shift totals
        $this->updateShiftTotals();
    }

    /**
     * After delete
     */
    public function afterDelete()
    {
        parent::afterDelete();
        
        // Update shift totals
        $this->updateShiftTotals();
    }
}