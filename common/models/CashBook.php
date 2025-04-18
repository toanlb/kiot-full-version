<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "cash_book".
 *
 * @property int $id
 * @property string $transaction_date
 * @property int|null $reference_id
 * @property string|null $reference_type
 * @property int $payment_method_id
 * @property int|null $shift_id
 * @property int|null $warehouse_id
 * @property float $amount
 * @property int $type
 * @property float $balance
 * @property string|null $description
 * @property string $created_at
 * @property int|null $created_by
 *
 * @property User $createdBy
 * @property PaymentMethod $paymentMethod
 * @property Shift $shift
 * @property Warehouse $warehouse
 */
class CashBook extends ActiveRecord
{
    const TYPE_IN = 1;
    const TYPE_OUT = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cash_book';
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
            [['transaction_date', 'payment_method_id', 'amount', 'type', 'balance'], 'required'],
            [['transaction_date', 'created_at'], 'safe'],
            [['reference_id', 'payment_method_id', 'shift_id', 'warehouse_id', 'type', 'created_by'], 'integer'],
            [['amount', 'balance'], 'number'],
            [['description'], 'string'],
            [['reference_type'], 'string', 'max' => 50],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
            [['payment_method_id'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentMethod::class, 'targetAttribute' => ['payment_method_id' => 'id']],
            [['shift_id'], 'exist', 'skipOnError' => true, 'targetClass' => Shift::class, 'targetAttribute' => ['shift_id' => 'id']],
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
            'transaction_date' => 'Ngày giao dịch',
            'reference_id' => 'ID tham chiếu',
            'reference_type' => 'Loại tham chiếu',
            'payment_method_id' => 'Phương thức thanh toán',
            'shift_id' => 'Ca làm việc',
            'warehouse_id' => 'Kho',
            'amount' => 'Số tiền',
            'type' => 'Loại',
            'balance' => 'Số dư',
            'description' => 'Nội dung',
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
     * Gets query for [[Warehouse]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWarehouse()
    {
        return $this->hasOne(Warehouse::class, ['id' => 'warehouse_id']);
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
            self::TYPE_IN => 'Thu',
            self::TYPE_OUT => 'Chi',
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
                
            case 'receipt':
                $model = Receipt::findOne($this->reference_id);
                return $model ? 'Phiếu thu: ' . $model->code : '';
                
            case 'payment':
                $model = Payment::findOne($this->reference_id);
                return $model ? 'Phiếu chi: ' . $model->code : '';
                
            case 'order_payment':
                $model = OrderPayment::findOne($this->reference_id);
                if ($model) {
                    $order = $model->order;
                    return $order ? 'Thanh toán đơn hàng: ' . $order->code : 'Thanh toán đơn hàng: #' . $model->order_id;
                }
                return '';
                
            default:
                return $this->reference_type . ': ' . $this->reference_id;
        }
    }

    /**
     * Get balance
     * 
     * @param int $paymentMethodId
     * @param string|null $date
     * @return float
     */
    public static function getBalance($paymentMethodId, $date = null)
    {
        $query = self::find()
            ->where(['payment_method_id' => $paymentMethodId]);
            
        if ($date) {
            $query->andWhere(['<=', 'transaction_date', $date]);
        }
        
        $record = $query->orderBy(['id' => SORT_DESC])->one();
        
        return $record ? $record->balance : 0;
    }

    /**
     * Get balances by payment method
     * 
     * @param string|null $date
     * @return array
     */
    public static function getBalancesByPaymentMethod($date = null)
    {
        $paymentMethods = PaymentMethod::find()
            ->where(['is_active' => 1])
            ->orderBy(['is_default' => SORT_DESC, 'sort_order' => SORT_ASC, 'name' => SORT_ASC])
            ->all();
            
        $balances = [];
        
        foreach ($paymentMethods as $paymentMethod) {
            $balances[$paymentMethod->id] = [
                'name' => $paymentMethod->name,
                'balance' => self::getBalance($paymentMethod->id, $date),
            ];
        }
        
        return $balances;
    }

    /**
     * Record cash transaction
     * 
     * @param array $params
     * @return bool|CashBook
     */
    public static function recordTransaction($params)
    {
        if (!isset($params['payment_method_id']) || !isset($params['amount']) || !isset($params['type'])) {
            return false;
        }
        
        $model = new self();
        $model->transaction_date = isset($params['transaction_date']) ? $params['transaction_date'] : new Expression('NOW()');
        $model->reference_id = isset($params['reference_id']) ? $params['reference_id'] : null;
        $model->reference_type = isset($params['reference_type']) ? $params['reference_type'] : null;
        $model->payment_method_id = $params['payment_method_id'];
        $model->shift_id = isset($params['shift_id']) ? $params['shift_id'] : null;
        $model->warehouse_id = isset($params['warehouse_id']) ? $params['warehouse_id'] : null;
        $model->amount = $params['amount'];
        $model->type = $params['type'];
        $model->description = isset($params['description']) ? $params['description'] : null;
        
        // Calculate balance
        $lastRecord = self::find()
            ->where(['payment_method_id' => $model->payment_method_id])
            ->orderBy(['id' => SORT_DESC])
            ->one();
            
        $balance = $lastRecord ? $lastRecord->balance : 0;
        
        if ($model->type == self::TYPE_IN) {
            $model->balance = $balance + $model->amount;
        } else {
            $model->balance = $balance - $model->amount;
        }
        
        if ($model->save()) {
            return $model;
        }
        
        return false;
    }
}