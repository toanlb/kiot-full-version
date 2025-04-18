<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "return".
 *
 * @property int $id
 * @property string $code
 * @property int|null $order_id
 * @property int|null $customer_id
 * @property int $user_id
 * @property int $warehouse_id
 * @property string $return_date
 * @property int $total_quantity
 * @property float $subtotal
 * @property float $tax_amount
 * @property float $total_amount
 * @property float $refund_amount
 * @property int|null $points_adjusted
 * @property int|null $payment_method_id
 * @property int $status
 * @property string|null $reason
 * @property string|null $note
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Customer $customer
 * @property Order $order
 * @property PaymentMethod $paymentMethod
 * @property ReturnDetail[] $returnDetails
 * @property User $user
 * @property Warehouse $warehouse
 */
class ReturnModel extends ActiveRecord
{
    const STATUS_DRAFT = 0;
    const STATUS_CONFIRMED = 1;
    const STATUS_REFUNDED = 2;
    const STATUS_COMPLETED = 3;
    const STATUS_CANCELED = 4;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'return';
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
            [['code', 'user_id', 'warehouse_id', 'return_date'], 'required'],
            [['order_id', 'customer_id', 'user_id', 'warehouse_id', 'total_quantity', 'points_adjusted', 'payment_method_id', 'status'], 'integer'],
            [['return_date', 'created_at', 'updated_at'], 'safe'],
            [['subtotal', 'tax_amount', 'total_amount', 'refund_amount'], 'number'],
            [['reason', 'note'], 'string'],
            [['code'], 'string', 'max' => 50],
            [['code'], 'unique'],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customer::class, 'targetAttribute' => ['customer_id' => 'id']],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::class, 'targetAttribute' => ['order_id' => 'id']],
            [['payment_method_id'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentMethod::class, 'targetAttribute' => ['payment_method_id' => 'id']],
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
            'code' => 'Mã trả hàng',
            'order_id' => 'Đơn hàng',
            'customer_id' => 'Khách hàng',
            'user_id' => 'Người tạo',
            'warehouse_id' => 'Kho',
            'return_date' => 'Ngày trả hàng',
            'total_quantity' => 'Tổng số lượng',
            'subtotal' => 'Tổng tiền hàng',
            'tax_amount' => 'Thuế',
            'total_amount' => 'Thành tiền',
            'refund_amount' => 'Tiền hoàn lại',
            'points_adjusted' => 'Điểm điều chỉnh',
            'payment_method_id' => 'Phương thức hoàn tiền',
            'status' => 'Trạng thái',
            'reason' => 'Lý do',
            'note' => 'Ghi chú',
            'created_at' => 'Ngày tạo',
            'updated_at' => 'Ngày cập nhật',
        ];
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
     * Gets query for [[ReturnDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReturnDetails()
    {
        return $this->hasMany(ReturnDetail::class, ['return_id' => 'id']);
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
            self::STATUS_DRAFT => 'Nháp',
            self::STATUS_CONFIRMED => 'Đã xác nhận',
            self::STATUS_REFUNDED => 'Đã hoàn tiền',
            self::STATUS_COMPLETED => 'Hoàn thành',
            self::STATUS_CANCELED => 'Đã hủy',
        ];
    }

    /**
     * Generate return code
     * 
     * @return string
     */
    public static function generateCode()
    {
        $prefix = 'TH';
        $year = date('y');
        $month = date('m');
        
        $latestReturn = self::find()
            ->where(['LIKE', 'code', $prefix . $year . $month])
            ->orderBy(['id' => SORT_DESC])
            ->one();
            
        $sequence = '001';
        if ($latestReturn) {
            $parts = explode($prefix . $year . $month, $latestReturn->code);
            if (isset($parts[1])) {
                $sequence = str_pad((int)$parts[1] + 1, 3, '0', STR_PAD_LEFT);
            }
        }
        
        return $prefix . $year . $month . $sequence;
    }

    /**
     * Process return
     * 
     * @return bool
     */
    public function processReturn()
    {
        if ($this->status != self::STATUS_CONFIRMED) {
            return false;
        }
        
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Update stock for restocking items
            foreach ($this->returnDetails as $detail) {
                if ($detail->restocking) {
                    // Add to stock
                    Stock::increase($detail->product_id, $this->warehouse_id, $detail->quantity);
                    
                    // Create batch if needed
                    if ($detail->batch_number) {
                        $batch = ProductBatch::findOne([
                            'product_id' => $detail->product_id,
                            'warehouse_id' => $this->warehouse_id,
                            'batch_number' => $detail->batch_number,
                        ]);
                        
                        if (!$batch) {
                            $batch = new ProductBatch();
                            $batch->product_id = $detail->product_id;
                            $batch->warehouse_id = $this->warehouse_id;
                            $batch->batch_number = $detail->batch_number;
                            $batch->quantity = 0;
                            $batch->cost_price = $detail->unit_price;
                        }
                        
                        $batch->quantity += $detail->quantity;
                        
                        if (!$batch->save()) {
                            throw new \Exception('Không thể lưu thông tin lô hàng');
                        }
                    }
                    
                    // Record movement
                    StockMovement::recordMovement([
                        'product_id' => $detail->product_id,
                        'destination_warehouse_id' => $this->warehouse_id,
                        'reference_id' => $this->id,
                        'reference_type' => 'return',
                        'quantity' => $detail->quantity,
                        'unit_id' => $detail->unit_id,
                        'movement_type' => StockMovement::TYPE_IN,
                        'movement_date' => $this->return_date,
                        'note' => 'Nhập kho từ trả hàng: ' . $this->code,
                    ]);
                }
            }
            
            // Process customer points adjustment
            if ($this->customer_id && $this->points_adjusted) {
                if ($this->points_adjusted > 0) {
                    // Add points back to customer
                    $result = CustomerPoint::addPoints(
                        $this->customer_id,
                        $this->points_adjusted,
                        $this->id,
                        'return',
                        'Hoàn điểm từ trả hàng: ' . $this->code
                    );
                } else {
                    // Deduct points from customer
                    $result = CustomerPoint::usePoints(
                        $this->customer_id,
                        abs($this->points_adjusted),
                        $this->id,
                        'return',
                        'Điều chỉnh điểm từ trả hàng: ' . $this->code
                    );
                }
                
                if (!$result) {
                    throw new \Exception('Không thể điều chỉnh điểm thưởng');
                }
            }
            
            // Process refund
            if ($this->refund_amount > 0) {
                if ($this->order_id) {
                    // Adjust order payment
                    $order = $this->order;
                    if ($order) {
                        $order->paid_amount = max(0, $order->paid_amount - $this->refund_amount);
                        $order->updatePaymentStatus();
                        
                        if (!$order->save(false)) {
                            throw new \Exception('Không thể cập nhật thanh toán đơn hàng');
                        }
                    }
                }
                
                // Process customer debt if needed
                if ($this->customer_id) {
                    $result = CustomerDebt::recordDebt(
                        $this->customer_id,
                        $this->refund_amount,
                        CustomerDebt::TYPE_PAYMENT,
                        $this->id,
                        'return',
                        'Hoàn tiền từ trả hàng: ' . $this->code,
                        $this->return_date
                    );
                    
                    if (!$result) {
                        throw new \Exception('Không thể ghi nhận thanh toán công nợ');
                    }
                }
                
                // Record to cash book
                if ($this->payment_method_id) {
                    $cashBook = new CashBook();
                    $cashBook->transaction_date = $this->return_date;
                    $cashBook->reference_id = $this->id;
                    $cashBook->reference_type = 'return';
                    $cashBook->payment_method_id = $this->payment_method_id;
                    $cashBook->warehouse_id = $this->warehouse_id;
                    $cashBook->amount = $this->refund_amount;
                    $cashBook->type = 2; // out
                    $cashBook->description = 'Chi tiền hoàn trả: ' . $this->code;
                    
                    // Calculate balance
                    $lastRecord = CashBook::find()
                        ->where(['payment_method_id' => $this->payment_method_id])
                        ->orderBy(['id' => SORT_DESC])
                        ->one();
                        
                    $balance = $lastRecord ? $lastRecord->balance : 0;
                    $cashBook->balance = $balance - $this->refund_amount;
                    
                    if (!$cashBook->save()) {
                        throw new \Exception('Không thể ghi sổ quỹ');
                    }
                }
                
                $this->status = self::STATUS_REFUNDED;
            } else {
                $this->status = self::STATUS_COMPLETED;
            }
            
            if (!$this->save(false)) {
                throw new \Exception('Không thể cập nhật trạng thái trả hàng');
            }
            
            // If part of a shift, update shift totals
            if ($this->order && $this->order->shift_id) {
                $shift = $this->order->shift;
                if ($shift) {
                    $shift->total_returns += $this->refund_amount;
                    $shift->expected_amount = $shift->opening_amount + $shift->total_sales + $shift->total_receipts - $shift->total_returns - $shift->total_payments;
                    
                    if (!$shift->save(false)) {
                        throw new \Exception('Không thể cập nhật thông tin ca làm việc');
                    }
                }
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
     * Calculate totals
     */
    public function calculateTotals()
    {
        $details = $this->returnDetails;
        
        $totalQuantity = 0;
        $subtotal = 0;
        $taxAmount = 0;
        
        foreach ($details as $detail) {
            $totalQuantity += $detail->quantity;
            $subtotal += $detail->quantity * $detail->unit_price;
            $taxAmount += $detail->tax_amount ?: 0;
        }
        
        $this->total_quantity = $totalQuantity;
        $this->subtotal = $subtotal;
        $this->tax_amount = $taxAmount;
        $this->total_amount = $subtotal + $taxAmount;
    }
}