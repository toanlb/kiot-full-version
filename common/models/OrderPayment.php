<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "order_payment".
 *
 * @property int $id
 * @property int $order_id
 * @property int $payment_method_id
 * @property float $amount
 * @property string $payment_date
 * @property string|null $reference_number
 * @property int $status
 * @property string|null $note
 * @property string $created_at
 * @property int|null $created_by
 *
 * @property User $createdBy
 * @property Order $order
 * @property PaymentMethod $paymentMethod
 */
class OrderPayment extends ActiveRecord
{
    const STATUS_PENDING = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_FAILED = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order_payment';
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
            [['order_id', 'payment_method_id', 'amount', 'payment_date'], 'required'],
            [['order_id', 'payment_method_id', 'status', 'created_by'], 'integer'],
            [['amount'], 'number'],
            [['payment_date', 'created_at'], 'safe'],
            [['note'], 'string'],
            [['reference_number'], 'string', 'max' => 100],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::class, 'targetAttribute' => ['order_id' => 'id']],
            [['payment_method_id'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentMethod::class, 'targetAttribute' => ['payment_method_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => 'Đơn hàng',
            'payment_method_id' => 'Phương thức thanh toán',
            'amount' => 'Số tiền',
            'payment_date' => 'Ngày thanh toán',
            'reference_number' => 'Mã tham chiếu',
            'status' => 'Trạng thái',
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
     * Gets query for [[Order]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
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
            self::STATUS_PENDING => 'Chờ xử lý',
            self::STATUS_SUCCESS => 'Thành công',
            self::STATUS_FAILED => 'Thất bại',
        ];
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
        
        // Update order paid amount if payment is successful
        if ($this->status == self::STATUS_SUCCESS) {
            $this->updateOrderPaidAmount();
        }
    }

    /**
     * After delete
     */
    public function afterDelete()
    {
        parent::afterDelete();
        
        // Update order paid amount
        $this->updateOrderPaidAmount();
    }

    /**
     * Update order paid amount
     */
    protected function updateOrderPaidAmount()
    {
        $order = $this->order;
        
        if ($order) {
            $paidAmount = self::find()
                ->where(['order_id' => $order->id, 'status' => self::STATUS_SUCCESS])
                ->sum('amount');
                
            $order->paid_amount = $paidAmount ?: 0;
            $order->change_amount = max(0, $order->paid_amount - $order->total_amount);
            
            // Update payment status
            $order->updatePaymentStatus();
            
            // Update order status if fully paid
            if ($order->payment_status == Order::PAYMENT_STATUS_PAID && $order->status < Order::STATUS_PAID) {
                $order->status = Order::STATUS_PAID;
            }
            
            $order->save(false);
            
            // Record to cash book
            if ($this->status == self::STATUS_SUCCESS) {
                $cashBook = new CashBook();
                $cashBook->transaction_date = $this->payment_date;
                $cashBook->reference_id = $this->id;
                $cashBook->reference_type = 'order_payment';
                $cashBook->payment_method_id = $this->payment_method_id;
                $cashBook->shift_id = $order->shift_id;
                $cashBook->warehouse_id = $order->warehouse_id;
                $cashBook->amount = $this->amount;
                $cashBook->type = 1; // in
                $cashBook->description = 'Thu tiền từ đơn hàng: ' . $order->code;
                
                // Calculate balance
                $lastRecord = CashBook::find()
                    ->where(['payment_method_id' => $this->payment_method_id])
                    ->orderBy(['id' => SORT_DESC])
                    ->one();
                    
                $balance = $lastRecord ? $lastRecord->balance : 0;
                $cashBook->balance = $balance + $this->amount;
                
                $cashBook->save();
            }
        }
    }

    /**
     * Process order payment
     * 
     * @param int $orderId
     * @param int $paymentMethodId
     * @param float $amount
     * @param string|null $referenceNumber
     * @param string|null $note
     * @return bool|OrderPayment
     */
    public static function processPayment($orderId, $paymentMethodId, $amount, $referenceNumber = null, $note = null)
    {
        if ($amount <= 0) {
            return false;
        }
        
        $order = Order::findOne($orderId);
        if (!$order) {
            return false;
        }
        
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $model = new self();
            $model->order_id = $orderId;
            $model->payment_method_id = $paymentMethodId;
            $model->amount = $amount;
            $model->payment_date = new Expression('NOW()');
            $model->reference_number = $referenceNumber;
            $model->status = self::STATUS_SUCCESS;
            $model->note = $note;
            
            if (!$model->save()) {
                throw new \Exception('Không thể lưu thông tin thanh toán');
            }
            
            $transaction->commit();
            return $model;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage());
            return false;
        }
    }
}