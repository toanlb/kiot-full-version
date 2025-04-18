<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "customer_debt".
 *
 * @property int $id
 * @property int $customer_id
 * @property int|null $reference_id
 * @property string|null $reference_type
 * @property float $amount
 * @property float $balance
 * @property int $type
 * @property string|null $description
 * @property string $transaction_date
 * @property string $created_at
 * @property int|null $created_by
 *
 * @property User $createdBy
 * @property Customer $customer
 */
class CustomerDebt extends ActiveRecord
{
    const TYPE_DEBT = 1;
    const TYPE_PAYMENT = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'customer_debt';
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
            [['customer_id', 'amount', 'balance', 'type', 'transaction_date'], 'required'],
            [['customer_id', 'reference_id', 'type', 'created_by'], 'integer'],
            [['amount', 'balance'], 'number'],
            [['description'], 'string'],
            [['transaction_date', 'created_at'], 'safe'],
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
            'amount' => 'Số tiền',
            'balance' => 'Số dư',
            'type' => 'Loại',
            'description' => 'Mô tả',
            'transaction_date' => 'Ngày giao dịch',
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
            self::TYPE_DEBT => 'Nợ',
            self::TYPE_PAYMENT => 'Thanh toán',
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
                
            default:
                return $this->reference_type . ': ' . $this->reference_id;
        }
    }

    /**
     * Format amount
     * 
     * @return string
     */
    public function getFormattedAmount()
    {
        $prefix = $this->type == self::TYPE_DEBT ? '+' : '-';
        return $prefix . Yii::$app->formatter->asDecimal($this->amount);
    }

    /**
     * Record customer debt
     * 
     * @param int $customerId
     * @param float $amount
     * @param int $type
     * @param int|null $referenceId
     * @param string|null $referenceType
     * @param string|null $description
     * @param string|null $transactionDate
     * @return bool|CustomerDebt
     */
    public static function recordDebt($customerId, $amount, $type, $referenceId = null, $referenceType = null, $description = null, $transactionDate = null)
    {
        if ($amount <= 0 || !in_array($type, [self::TYPE_DEBT, self::TYPE_PAYMENT])) {
            return false;
        }
        
        $customer = Customer::findOne($customerId);
        if (!$customer) {
            return false;
        }
        
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Update customer debt
            $balance = $customer->debt_amount;
            
            if ($type == self::TYPE_DEBT) {
                $customer->debt_amount += $amount;
                $balance = $customer->debt_amount;
            } else {
                if ($amount > $customer->debt_amount) {
                    $amount = $customer->debt_amount;
                }
                
                $customer->debt_amount -= $amount;
                $balance = $customer->debt_amount;
            }
            
            if (!$customer->save(false)) {
                throw new \Exception('Không thể cập nhật công nợ khách hàng');
            }
            
            // Create debt record
            $model = new self();
            $model->customer_id = $customerId;
            $model->reference_id = $referenceId;
            $model->reference_type = $referenceType;
            $model->amount = $amount;
            $model->balance = $balance;
            $model->type = $type;
            $model->description = $description;
            $model->transaction_date = $transactionDate ?: new Expression('NOW()');
            
            if (!$model->save()) {
                throw new \Exception('Không thể lưu thông tin công nợ');
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