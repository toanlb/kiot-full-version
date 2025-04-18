<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "receipt".
 *
 * @property int $id
 * @property string $code
 * @property int $receipt_type
 * @property int|null $reference_id
 * @property string|null $reference_type
 * @property int|null $customer_id
 * @property int|null $supplier_id
 * @property float $amount
 * @property int $payment_method_id
 * @property string $receipt_date
 * @property string|null $received_from
 * @property string|null $account_number
 * @property string|null $bank_name
 * @property string|null $transaction_code
 * @property string|null $description
 * @property int $status
 * @property string $created_at
 * @property string $updated_at
 * @property int|null $created_by
 * @property int|null $approved_by
 * @property string|null $approved_at
 *
 * @property User $approvedBy
 * @property User $createdBy
 * @property Customer $customer
 * @property PaymentMethod $paymentMethod
 * @property Supplier $supplier
 */
class Receipt extends ActiveRecord
{
    const TYPE_SALES = 1;
    const TYPE_DEBT_PAYMENT = 2;
    const TYPE_SUPPLIER_REFUND = 3;
    const TYPE_OTHER = 4;

    const STATUS_DRAFT = 0;
    const STATUS_CONFIRMED = 1;
    const STATUS_CANCELED = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'receipt';
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
            [['code', 'receipt_type', 'amount', 'payment_method_id', 'receipt_date'], 'required'],
            [['receipt_type', 'reference_id', 'customer_id', 'supplier_id', 'payment_method_id', 'status', 'created_by', 'approved_by'], 'integer'],
            [['amount'], 'number'],
            [['receipt_date', 'created_at', 'updated_at', 'approved_at'], 'safe'],
            [['description'], 'string'],
            [['code', 'reference_type', 'received_from', 'bank_name'], 'string', 'max' => 255],
            [['account_number', 'transaction_code'], 'string', 'max' => 100],
            [['code'], 'unique'],
            [['approved_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['approved_by' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customer::class, 'targetAttribute' => ['customer_id' => 'id']],
            [['payment_method_id'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentMethod::class, 'targetAttribute' => ['payment_method_id' => 'id']],
            [['supplier_id'], 'exist', 'skipOnError' => true, 'targetClass' => Supplier::class, 'targetAttribute' => ['supplier_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Mã phiếu',
            'receipt_type' => 'Loại phiếu thu',
            'reference_id' => 'ID tham chiếu',
            'reference_type' => 'Loại tham chiếu',
            'customer_id' => 'Khách hàng',
            'supplier_id' => 'Nhà cung cấp',
            'amount' => 'Số tiền',
            'payment_method_id' => 'Phương thức thanh toán',
            'receipt_date' => 'Ngày thu',
            'received_from' => 'Người nộp',
            'account_number' => 'Số tài khoản',
            'bank_name' => 'Tên ngân hàng',
            'transaction_code' => 'Mã giao dịch',
            'description' => 'Nội dung',
            'status' => 'Trạng thái',
            'created_at' => 'Ngày tạo',
            'updated_at' => 'Ngày cập nhật',
            'created_by' => 'Người tạo',
            'approved_by' => 'Người duyệt',
            'approved_at' => 'Ngày duyệt',
        ];
    }

    /**
     * Gets query for [[ApprovedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getApprovedBy()
    {
        return $this->hasOne(User::class, ['id' => 'approved_by']);
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
     * Gets query for [[PaymentMethod]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentMethod()
    {
        return $this->hasOne(PaymentMethod::class, ['id' => 'payment_method_id']);
    }

    /**
     * Gets query for [[Supplier]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSupplier()
    {
        return $this->hasOne(Supplier::class, ['id' => 'supplier_id']);
    }

    /**
     * Get receipt type label
     * 
     * @return string
     */
    public function getReceiptTypeLabel()
    {
        $types = self::getReceiptTypes();
        return isset($types[$this->receipt_type]) ? $types[$this->receipt_type] : 'Không xác định';
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
     * Get receipt types
     * 
     * @return array
     */
    public static function getReceiptTypes()
    {
        return [
            self::TYPE_SALES => 'Bán hàng',
            self::TYPE_DEBT_PAYMENT => 'Thanh toán công nợ',
            self::TYPE_SUPPLIER_REFUND => 'Nhà cung cấp hoàn tiền',
            self::TYPE_OTHER => 'Khác',
        ];
    }

    /**
     * Get statuses
     * 
     * @return array
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_DRAFT => 'Nháp',
            self::STATUS_CONFIRMED => 'Đã xác nhận',
            self::STATUS_CANCELED => 'Đã hủy',
        ];
    }

    /**
     * Generate receipt code
     * 
     * @return string
     */
    public static function generateCode()
    {
        $prefix = 'PT';
        $year = date('y');
        $month = date('m');
        
        $latestReceipt = self::find()
            ->where(['LIKE', 'code', $prefix . $year . $month])
            ->orderBy(['id' => SORT_DESC])
            ->one();
            
        $sequence = '001';
        if ($latestReceipt) {
            $parts = explode($prefix . $year . $month, $latestReceipt->code);
            if (isset($parts[1])) {
                $sequence = str_pad((int)$parts[1] + 1, 3, '0', STR_PAD_LEFT);
            }
        }
        
        return $prefix . $year . $month . $sequence;
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
                
            case 'customer_debt':
                $model = CustomerDebt::findOne($this->reference_id);
                return $model ? 'Công nợ khách hàng: #' . $model->id : '';
                
            case 'supplier_debt':
                $model = SupplierDebt::findOne($this->reference_id);
                return $model ? 'Công nợ nhà cung cấp: #' . $model->id : '';
                
            default:
                return $this->reference_type . ': ' . $this->reference_id;
        }
    }

    /**
     * Process receipt
     * 
     * @return bool
     */
    public function processReceipt()
    {
        if ($this->status != self::STATUS_DRAFT) {
            return false;
        }
        
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Process based on receipt type
            switch ($this->receipt_type) {
                case self::TYPE_SALES:
                    // Nothing special to do for sales receipt
                    break;
                    
                case self::TYPE_DEBT_PAYMENT:
                    if ($this->customer_id) {
                        // Record customer debt payment
                        $debt = CustomerDebt::recordDebt(
                            $this->customer_id,
                            $this->amount,
                            CustomerDebt::TYPE_PAYMENT,
                            $this->id,
                            'receipt',
                            $this->description ?: ('Thu nợ từ phiếu thu: ' . $this->code),
                            $this->receipt_date
                        );
                        
                        if (!$debt) {
                            throw new \Exception('Không thể ghi nhận thanh toán công nợ');
                        }
                    }
                    break;
                    
                case self::TYPE_SUPPLIER_REFUND:
                    if ($this->supplier_id) {
                        // Record supplier payment
                        $debt = SupplierDebt::recordDebt(
                            $this->supplier_id,
                            $this->amount,
                            SupplierDebt::TYPE_PAYMENT,
                            $this->id,
                            'receipt',
                            $this->description ?: ('Nhà cung cấp hoàn tiền: ' . $this->code),
                            $this->receipt_date
                        );
                        
                        if (!$debt) {
                            throw new \Exception('Không thể ghi nhận hoàn tiền từ nhà cung cấp');
                        }
                    }
                    break;
                    
                case self::TYPE_OTHER:
                    // Nothing special to do for other receipt
                    break;
            }
            
            // Record to cash book
            $cashBook = new CashBook();
            $cashBook->transaction_date = $this->receipt_date;
            $cashBook->reference_id = $this->id;
            $cashBook->reference_type = 'receipt';
            $cashBook->payment_method_id = $this->payment_method_id;
            $cashBook->amount = $this->amount;
            $cashBook->type = 1; // in
            $cashBook->description = $this->description ?: ('Thu tiền từ phiếu thu: ' . $this->code);
            
            // Calculate balance
            $lastRecord = CashBook::find()
                ->where(['payment_method_id' => $this->payment_method_id])
                ->orderBy(['id' => SORT_DESC])
                ->one();
                
            $balance = $lastRecord ? $lastRecord->balance : 0;
            $cashBook->balance = $balance + $this->amount;
            
            if (!$cashBook->save()) {
                throw new \Exception('Không thể ghi sổ quỹ');
            }
            
            // Update receipt status
            $this->status = self::STATUS_CONFIRMED;
            $this->approved_by = Yii::$app->user->id;
            $this->approved_at = new Expression('NOW()');
            
            if (!$this->save(false)) {
                throw new \Exception('Không thể cập nhật trạng thái phiếu thu');
            }
            
            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage());
            return false;
        }
    }

    /**
     * Cancel receipt
     * 
     * @param string|null $reason
     * @return bool
     */
    public function cancelReceipt($reason = null)
    {
        if ($this->status != self::STATUS_CONFIRMED) {
            return false;
        }
        
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Process based on receipt type
            switch ($this->receipt_type) {
                case self::TYPE_DEBT_PAYMENT:
                    if ($this->customer_id) {
                        // Revert customer debt payment
                        $debt = CustomerDebt::recordDebt(
                            $this->customer_id,
                            $this->amount,
                            CustomerDebt::TYPE_DEBT,
                            $this->id,
                            'receipt_cancel',
                            $reason ?: ('Hủy phiếu thu: ' . $this->code),
                            new Expression('NOW()')
                        );
                        
                        if (!$debt) {
                            throw new \Exception('Không thể hoàn tác thanh toán công nợ');
                        }
                    }
                    break;
                    
                case self::TYPE_SUPPLIER_REFUND:
                    if ($this->supplier_id) {
                        // Revert supplier payment
                        $debt = SupplierDebt::recordDebt(
                            $this->supplier_id,
                            $this->amount,
                            SupplierDebt::TYPE_DEBT,
                            $this->id,
                            'receipt_cancel',
                            $reason ?: ('Hủy phiếu thu: ' . $this->code),
                            new Expression('NOW()')
                        );
                        
                        if (!$debt) {
                            throw new \Exception('Không thể hoàn tác hoàn tiền từ nhà cung cấp');
                        }
                    }
                    break;
            }
            
            // Record to cash book
            $cashBook = new CashBook();
            $cashBook->transaction_date = new Expression('NOW()');
            $cashBook->reference_id = $this->id;
            $cashBook->reference_type = 'receipt_cancel';
            $cashBook->payment_method_id = $this->payment_method_id;
            $cashBook->amount = $this->amount;
            $cashBook->type = 2; // out
            $cashBook->description = $reason ?: ('Hủy phiếu thu: ' . $this->code);
            
            // Calculate balance
            $lastRecord = CashBook::find()
                ->where(['payment_method_id' => $this->payment_method_id])
                ->orderBy(['id' => SORT_DESC])
                ->one();
                
            $balance = $lastRecord ? $lastRecord->balance : 0;
            $cashBook->balance = $balance - $this->amount;
            
            if (!$cashBook->save()) {
                throw new \Exception('Không thể ghi sổ quỹ');
            }
            
            // Update receipt status
            $this->status = self::STATUS_CANCELED;
            
            if (!$this->save(false)) {
                throw new \Exception('Không thể cập nhật trạng thái phiếu thu');
            }
            
            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage());
            return false;
        }
    }
}