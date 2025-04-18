<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "payment".
 *
 * @property int $id
 * @property string $code
 * @property int $payment_type
 * @property int|null $reference_id
 * @property string|null $reference_type
 * @property int|null $supplier_id
 * @property int|null $customer_id
 * @property float $amount
 * @property int $payment_method_id
 * @property string $payment_date
 * @property string|null $paid_to
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
class Payment extends ActiveRecord
{
    const TYPE_PURCHASE = 1;
    const TYPE_SUPPLIER_DEBT = 2;
    const TYPE_CUSTOMER_REFUND = 3;
    const TYPE_OTHER = 4;

    const STATUS_DRAFT = 0;
    const STATUS_CONFIRMED = 1;
    const STATUS_CANCELED = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'payment';
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
            [['code', 'payment_type', 'amount', 'payment_method_id', 'payment_date'], 'required'],
            [['payment_type', 'reference_id', 'supplier_id', 'customer_id', 'payment_method_id', 'status', 'created_by', 'approved_by'], 'integer'],
            [['amount'], 'number'],
            [['payment_date', 'created_at', 'updated_at', 'approved_at'], 'safe'],
            [['description'], 'string'],
            [['code', 'reference_type', 'paid_to', 'bank_name'], 'string', 'max' => 255],
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
            'payment_type' => 'Loại phiếu chi',
            'reference_id' => 'ID tham chiếu',
            'reference_type' => 'Loại tham chiếu',
            'supplier_id' => 'Nhà cung cấp',
            'customer_id' => 'Khách hàng',
            'amount' => 'Số tiền',
            'payment_method_id' => 'Phương thức thanh toán',
            'payment_date' => 'Ngày chi',
            'paid_to' => 'Người nhận',
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
     * Get payment type label
     * 
     * @return string
     */
    public function getPaymentTypeLabel()
    {
        $types = self::getPaymentTypes();
        return isset($types[$this->payment_type]) ? $types[$this->payment_type] : 'Không xác định';
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
     * Get payment types
     * 
     * @return array
     */
    public static function getPaymentTypes()
    {
        return [
            self::TYPE_PURCHASE => 'Mua hàng',
            self::TYPE_SUPPLIER_DEBT => 'Thanh toán công nợ NCC',
            self::TYPE_CUSTOMER_REFUND => 'Hoàn tiền khách hàng',
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
     * Generate payment code
     * 
     * @return string
     */
    public static function generateCode()
    {
        $prefix = 'PC';
        $year = date('y');
        $month = date('m');
        
        $latestPayment = self::find()
            ->where(['LIKE', 'code', $prefix . $year . $month])
            ->orderBy(['id' => SORT_DESC])
            ->one();
            
        $sequence = '001';
        if ($latestPayment) {
            $parts = explode($prefix . $year . $month, $latestPayment->code);
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
            case 'stock_in':
                $model = StockIn::findOne($this->reference_id);
                return $model ? 'Nhập kho: ' . $model->code : '';
                
            case 'supplier_debt':
                $model = SupplierDebt::findOne($this->reference_id);
                return $model ? 'Công nợ nhà cung cấp: #' . $model->id : '';
                
            case 'return':
                $model = ReturnModel::findOne($this->reference_id);
                return $model ? 'Trả hàng: ' . $model->code : '';
                
            default:
                return $this->reference_type . ': ' . $this->reference_id;
        }
    }

    /**
     * Process payment
     * 
     * @return bool
     */
    public function processPayment()
    {
        if ($this->status != self::STATUS_DRAFT) {
            return false;
        }
        
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Process based on payment type
            switch ($this->payment_type) {
                case self::TYPE_PURCHASE:
                    if ($this->supplier_id) {
                        // Update stock in if reference is stock_in
                        if ($this->reference_type == 'stock_in' && $this->reference_id) {
                            $stockIn = StockIn::findOne($this->reference_id);
                            if ($stockIn) {
                                $stockIn->paid_amount += $this->amount;
                                $stockIn->updatePaymentStatus();
                                
                                if (!$stockIn->save(false)) {
                                    throw new \Exception('Không thể cập nhật thanh toán phiếu nhập kho');
                                }
                            }
                        }
                    }
                    break;
                    
                case self::TYPE_SUPPLIER_DEBT:
                    if ($this->supplier_id) {
                        // Record supplier debt payment
                        $debt = SupplierDebt::recordDebt(
                            $this->supplier_id,
                            $this->amount,
                            SupplierDebt::TYPE_PAYMENT,
                            $this->id,
                            'payment',
                            $this->description ?: ('Thanh toán nợ từ phiếu chi: ' . $this->code),
                            $this->payment_date
                        );
                        
                        if (!$debt) {
                            throw new \Exception('Không thể ghi nhận thanh toán công nợ');
                        }
                    }
                    break;
                    
                case self::TYPE_CUSTOMER_REFUND:
                    if ($this->customer_id) {
                        // Record customer refund
                        $debt = CustomerDebt::recordDebt(
                            $this->customer_id,
                            $this->amount,
                            CustomerDebt::TYPE_PAYMENT,
                            $this->id,
                            'payment',
                            $this->description ?: ('Hoàn tiền khách hàng: ' . $this->code),
                            $this->payment_date
                        );
                        
                        if (!$debt) {
                            throw new \Exception('Không thể ghi nhận hoàn tiền khách hàng');
                        }
                    }
                    break;
                    
                case self::TYPE_OTHER:
                    // Nothing special to do for other payment
                    break;
            }
            
            // Record to cash book
            $cashBook = new CashBook();
            $cashBook->transaction_date = $this->payment_date;
            $cashBook->reference_id = $this->id;
            $cashBook->reference_type = 'payment';
            $cashBook->payment_method_id = $this->payment_method_id;
            $cashBook->amount = $this->amount;
            $cashBook->type = 2; // out
            $cashBook->description = $this->description ?: ('Chi tiền từ phiếu chi: ' . $this->code);
            
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
            
            // Update payment status
            $this->status = self::STATUS_CONFIRMED;
            $this->approved_by = Yii::$app->user->id;
            $this->approved_at = new Expression('NOW()');
            
            if (!$this->save(false)) {
                throw new \Exception('Không thể cập nhật trạng thái phiếu chi');
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
     * Cancel payment
     * 
     * @param string|null $reason
     * @return bool
     */
    public function cancelPayment($reason = null)
    {
        if ($this->status != self::STATUS_CONFIRMED) {
            return false;
        }
        
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Process based on payment type
            switch ($this->payment_type) {
                case self::TYPE_PURCHASE:
                    if ($this->supplier_id) {
                        // Update stock in if reference is stock_in
                        if ($this->reference_type == 'stock_in' && $this->reference_id) {
                            $stockIn = StockIn::findOne($this->reference_id);
                            if ($stockIn) {
                                $stockIn->paid_amount = max(0, $stockIn->paid_amount - $this->amount);
                                $stockIn->updatePaymentStatus();
                                
                                if (!$stockIn->save(false)) {
                                    throw new \Exception('Không thể cập nhật thanh toán phiếu nhập kho');
                                }
                            }
                        }
                    }
                    break;
                    
                case self::TYPE_SUPPLIER_DEBT:
                    if ($this->supplier_id) {
                        // Revert supplier debt payment
                        $debt = SupplierDebt::recordDebt(
                            $this->supplier_id,
                            $this->amount,
                            SupplierDebt::TYPE_DEBT,
                            $this->id,
                            'payment_cancel',
                            $reason ?: ('Hủy phiếu chi: ' . $this->code),
                            new Expression('NOW()')
                        );
                        
                        if (!$debt) {
                            throw new \Exception('Không thể hoàn tác thanh toán công nợ');
                        }
                    }
                    break;
                    
                case self::TYPE_CUSTOMER_REFUND:
                    if ($this->customer_id) {
                        // Revert customer refund
                        $debt = CustomerDebt::recordDebt(
                            $this->customer_id,
                            $this->amount,
                            CustomerDebt::TYPE_DEBT,
                            $this->id,
                            'payment_cancel',
                            $reason ?: ('Hủy phiếu chi: ' . $this->code),
                            new Expression('NOW()')
                        );
                        
                        if (!$debt) {
                            throw new \Exception('Không thể hoàn tác hoàn tiền khách hàng');
                        }
                    }
                    break;
            }
            
            // Record to cash book
            $cashBook = new CashBook();
            $cashBook->transaction_date = new Expression('NOW()');
            $cashBook->reference_id = $this->id;
            $cashBook->reference_type = 'payment_cancel';
            $cashBook->payment_method_id = $this->payment_method_id;
            $cashBook->amount = $this->amount;
            $cashBook->type = 1; // in
            $cashBook->description = $reason ?: ('Hủy phiếu chi: ' . $this->code);
            
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
            
            // Update payment status
            $this->status = self::STATUS_CANCELED;
            
            if (!$this->save(false)) {
                throw new \Exception('Không thể cập nhật trạng thái phiếu chi');
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