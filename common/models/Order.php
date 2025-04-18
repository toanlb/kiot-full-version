<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "order".
 *
 * @property int $id
 * @property string $code
 * @property int|null $customer_id
 * @property int $user_id
 * @property int|null $shift_id
 * @property int $warehouse_id
 * @property string $order_date
 * @property int $total_quantity
 * @property float $subtotal
 * @property float $discount_amount
 * @property float $tax_amount
 * @property float $total_amount
 * @property float $paid_amount
 * @property float $change_amount
 * @property int|null $points_earned
 * @property int|null $points_used
 * @property float|null $points_amount
 * @property int|null $payment_method_id
 * @property int $payment_status
 * @property string|null $shipping_address
 * @property float|null $shipping_fee
 * @property int|null $shipping_status
 * @property string|null $delivery_date
 * @property int $status
 * @property string|null $note
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Customer $customer
 * @property OrderDetail[] $orderDetails
 * @property OrderPayment[] $orderPayments
 * @property PaymentMethod $paymentMethod
 * @property Return[] $returns
 * @property Shift $shift
 * @property User $user
 * @property Warehouse $warehouse
 * @property Warranty[] $warranties
 */
class Order extends ActiveRecord
{
    const STATUS_DRAFT = 0;
    const STATUS_CONFIRMED = 1;
    const STATUS_PAID = 2;
    const STATUS_SHIPPED = 3;
    const STATUS_COMPLETED = 4;
    const STATUS_CANCELED = 5;

    const PAYMENT_STATUS_UNPAID = 0;
    const PAYMENT_STATUS_PARTIALLY = 1;
    const PAYMENT_STATUS_PAID = 2;

    const SHIPPING_STATUS_NOT_SHIPPED = 0;
    const SHIPPING_STATUS_SHIPPING = 1;
    const SHIPPING_STATUS_DELIVERED = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order';
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
            [['code', 'user_id', 'warehouse_id', 'order_date'], 'required'],
            [['customer_id', 'user_id', 'shift_id', 'warehouse_id', 'total_quantity', 'points_earned', 'points_used', 'payment_method_id', 'payment_status', 'shipping_status', 'status'], 'integer'],
            [['order_date', 'delivery_date', 'created_at', 'updated_at'], 'safe'],
            [['subtotal', 'discount_amount', 'tax_amount', 'total_amount', 'paid_amount', 'change_amount', 'points_amount', 'shipping_fee'], 'number'],
            [['note'], 'string'],
            [['code'], 'string', 'max' => 50],
            [['shipping_address'], 'string', 'max' => 500],
            [['code'], 'unique'],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customer::class, 'targetAttribute' => ['customer_id' => 'id']],
            [['payment_method_id'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentMethod::class, 'targetAttribute' => ['payment_method_id' => 'id']],
            [['shift_id'], 'exist', 'skipOnError' => true, 'targetClass' => Shift::class, 'targetAttribute' => ['shift_id' => 'id']],
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
            'code' => 'Mã đơn hàng',
            'customer_id' => 'Khách hàng',
            'user_id' => 'Người tạo',
            'shift_id' => 'Ca làm việc',
            'warehouse_id' => 'Kho',
            'order_date' => 'Ngày đặt hàng',
            'total_quantity' => 'Tổng số lượng',
            'subtotal' => 'Tổng tiền hàng',
            'discount_amount' => 'Chiết khấu',
            'tax_amount' => 'Thuế',
            'total_amount' => 'Thành tiền',
            'paid_amount' => 'Đã thanh toán',
            'change_amount' => 'Tiền thừa',
            'points_earned' => 'Điểm được thưởng',
            'points_used' => 'Điểm sử dụng',
            'points_amount' => 'Giá trị điểm',
            'payment_method_id' => 'Phương thức thanh toán',
            'payment_status' => 'Trạng thái thanh toán',
            'shipping_address' => 'Địa chỉ giao hàng',
            'shipping_fee' => 'Phí giao hàng',
            'shipping_status' => 'Trạng thái giao hàng',
            'delivery_date' => 'Ngày giao hàng',
            'status' => 'Trạng thái',
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
     * Gets query for [[OrderDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrderDetails()
    {
        return $this->hasMany(OrderDetail::class, ['order_id' => 'id']);
    }

    /**
     * Gets query for [[OrderPayments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrderPayments()
    {
        return $this->hasMany(OrderPayment::class, ['order_id' => 'id']);
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
     * Gets query for [[Returns]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReturns()
    {
        return $this->hasMany(ReturnModel::class, ['order_id' => 'id']);
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
     * Gets query for [[Warranties]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWarranties()
    {
        return $this->hasMany(Warranty::class, ['order_id' => 'id']);
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
     * Get payment status label
     * 
     * @return string
     */
    public function getPaymentStatusLabel()
    {
        $statuses = self::getPaymentStatuses();
        return isset($statuses[$this->payment_status]) ? $statuses[$this->payment_status] : 'Không xác định';
    }

    /**
     * Get shipping status label
     * 
     * @return string
     */
    public function getShippingStatusLabel()
    {
        $statuses = self::getShippingStatuses();
        return isset($statuses[$this->shipping_status]) ? $statuses[$this->shipping_status] : 'Không xác định';
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
            self::STATUS_PAID => 'Đã thanh toán',
            self::STATUS_SHIPPED => 'Đã giao hàng',
            self::STATUS_COMPLETED => 'Hoàn thành',
            self::STATUS_CANCELED => 'Đã hủy',
        ];
    }

    /**
     * Get payment statuses
     * 
     * @return array
     */
    public static function getPaymentStatuses()
    {
        return [
            self::PAYMENT_STATUS_UNPAID => 'Chưa thanh toán',
            self::PAYMENT_STATUS_PARTIALLY => 'Thanh toán một phần',
            self::PAYMENT_STATUS_PAID => 'Đã thanh toán',
        ];
    }

    /**
     * Get shipping statuses
     * 
     * @return array
     */
    public static function getShippingStatuses()
    {
        return [
            self::SHIPPING_STATUS_NOT_SHIPPED => 'Chưa giao hàng',
            self::SHIPPING_STATUS_SHIPPING => 'Đang giao hàng',
            self::SHIPPING_STATUS_DELIVERED => 'Đã giao hàng',
        ];
    }

    /**
     * Generate order code
     * 
     * @return string
     */
    public static function generateCode()
    {
        $prefix = 'DH';
        $year = date('y');
        $month = date('m');
        
        $latestOrder = self::find()
            ->where(['LIKE', 'code', $prefix . $year . $month])
            ->orderBy(['id' => SORT_DESC])
            ->one();
            
        $sequence = '001';
        if ($latestOrder) {
            $parts = explode($prefix . $year . $month, $latestOrder->code);
            if (isset($parts[1])) {
                $sequence = str_pad((int)$parts[1] + 1, 3, '0', STR_PAD_LEFT);
            }
        }
        
        return $prefix . $year . $month . $sequence;
    }

    /**
     * Process order
     * 
     * @return bool
     */
    public function processOrder()
    {
        if ($this->status != self::STATUS_CONFIRMED) {
            return false;
        }
        
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Process stock
            foreach ($this->orderDetails as $detail) {
                // Check stock
                $stock = Stock::findOne(['product_id' => $detail->product_id, 'warehouse_id' => $this->warehouse_id]);
                
                if (!$stock || $stock->quantity < $detail->quantity) {
                    throw new \Exception('Không đủ số lượng tồn kho cho sản phẩm ' . $detail->product->name);
                }
                
                // Handle batch if specified
                if ($detail->batch_number) {
                    $batch = ProductBatch::findOne([
                        'product_id' => $detail->product_id,
                        'warehouse_id' => $this->warehouse_id,
                        'batch_number' => $detail->batch_number,
                    ]);
                    
                    if (!$batch || $batch->quantity < $detail->quantity) {
                        throw new \Exception('Không đủ số lượng tồn kho cho lô hàng ' . $detail->batch_number);
                    }
                    
                    $batch->quantity -= $detail->quantity;
                    if (!$batch->save()) {
                        throw new \Exception('Không thể cập nhật thông tin lô hàng');
                    }
                } else {
                    // If no batch specified, find available batches using FEFO
                    $remainingQuantity = $detail->quantity;
                    $batches = ProductBatch::findAvailableBatches($detail->product_id, $this->warehouse_id);
                    
                    foreach ($batches as $batch) {
                        $quantityToReduce = min($batch->quantity, $remainingQuantity);
                        $batch->quantity -= $quantityToReduce;
                        
                        if (!$batch->save()) {
                            throw new \Exception('Không thể cập nhật thông tin lô hàng');
                        }
                        
                        $remainingQuantity -= $quantityToReduce;
                        if ($remainingQuantity <= 0) {
                            break;
                        }
                    }
                }
                
                // Reduce stock
                Stock::decrease($detail->product_id, $this->warehouse_id, $detail->quantity);
                
                // Record movement
                StockMovement::recordMovement([
                    'product_id' => $detail->product_id,
                    'source_warehouse_id' => $this->warehouse_id,
                    'reference_id' => $this->id,
                    'reference_type' => 'order',
                    'quantity' => $detail->quantity,
                    'unit_id' => $detail->unit_id,
                    'movement_type' => StockMovement::TYPE_OUT,
                    'movement_date' => $this->order_date,
                    'note' => 'Xuất kho cho đơn hàng: ' . $this->code,
                ]);
            }
            
            // Process customer debt if needed
            if ($this->customer_id && $this->total_amount > $this->paid_amount) {
                $debtAmount = $this->total_amount - $this->paid_amount;
                
                $debt = CustomerDebt::recordDebt(
                    $this->customer_id,
                    $debtAmount,
                    CustomerDebt::TYPE_DEBT,
                    $this->id,
                    'order',
                    'Nợ từ đơn hàng: ' . $this->code,
                    $this->order_date
                );
                
                if (!$debt) {
                    throw new \Exception('Không thể ghi nhận công nợ khách hàng');
                }
            }
            
            // Process customer points
            if ($this->customer_id) {
                // Add earned points
                if ($this->points_earned > 0) {
                    $result = CustomerPoint::addPoints(
                        $this->customer_id,
                        $this->points_earned,
                        $this->id,
                        'order',
                        'Điểm thưởng từ đơn hàng: ' . $this->code
                    );
                    
                    if (!$result) {
                        throw new \Exception('Không thể cộng điểm thưởng');
                    }
                }
                
                // Deduct used points
                if ($this->points_used > 0) {
                    $result = CustomerPoint::usePoints(
                        $this->customer_id,
                        $this->points_used,
                        $this->id,
                        'order',
                        'Sử dụng điểm cho đơn hàng: ' . $this->code
                    );
                    
                    if (!$result) {
                        throw new \Exception('Không thể trừ điểm thưởng');
                    }
                }
            }
            
            // Update order status
            if ($this->payment_status == self::PAYMENT_STATUS_PAID) {
                $this->status = self::STATUS_PAID;
            } else {
                $this->status = self::STATUS_CONFIRMED;
            }
            
            if (!$this->save(false)) {
                throw new \Exception('Không thể cập nhật trạng thái đơn hàng');
            }
            
            // Create warranty records for products with warranty
            foreach ($this->orderDetails as $detail) {
                $product = $detail->product;
                
                if ($product->warranty_period > 0) {
                    $warranty = new Warranty();
                    $warranty->code = Warranty::generateCode();
                    $warranty->order_id = $this->id;
                    $warranty->order_detail_id = $detail->id;
                    $warranty->product_id = $detail->product_id;
                    $warranty->customer_id = $this->customer_id;
                    $warranty->start_date = date('Y-m-d');
                    $warranty->end_date = date('Y-m-d', strtotime('+' . $product->warranty_period . ' days'));
                    $warranty->status_id = 1; // default status
                    $warranty->active = 1;
                    $warranty->note = 'Bảo hành từ đơn hàng: ' . $this->code;
                    
                    if (!$warranty->save()) {
                        throw new \Exception('Không thể tạo thông tin bảo hành');
                    }
                }
            }
            
            // If part of a shift, update shift totals
            if ($this->shift_id) {
                $shift = $this->shift;
                if ($shift) {
                    $shift->total_sales += $this->total_amount;
                    $shift->expected_amount = $shift->opening_amount + $shift->total_sales + $shift->total_receipts - $shift->total_returns - $shift->total_payments;
                    
                    if (!$shift->save(false)) {
                        throw new \Exception('Không thể cập nhật thông tin ca làm việc');
                    }
                    
                    // Update shift details
                    if ($this->payment_method_id) {
                        $shiftDetail = ShiftDetail::findOne([
                            'shift_id' => $this->shift_id,
                            'payment_method_id' => $this->payment_method_id,
                            'transaction_type' => 1, // sales
                        ]);
                        
                        if (!$shiftDetail) {
                            $shiftDetail = new ShiftDetail();
                            $shiftDetail->shift_id = $this->shift_id;
                            $shiftDetail->payment_method_id = $this->payment_method_id;
                            $shiftDetail->transaction_type = 1; // sales
                            $shiftDetail->total_amount = 0;
                            $shiftDetail->transaction_count = 0;
                        }
                        
                        $shiftDetail->total_amount += $this->paid_amount;
                        $shiftDetail->transaction_count += 1;
                        
                        if (!$shiftDetail->save()) {
                            throw new \Exception('Không thể cập nhật chi tiết ca làm việc');
                        }
                    }
                }
            }
            
            // Record to cash book
            if ($this->paid_amount > 0 && $this->payment_method_id) {
                $cashBook = new CashBook();
                $cashBook->transaction_date = $this->order_date;
                $cashBook->reference_id = $this->id;
                $cashBook->reference_type = 'order';
                $cashBook->payment_method_id = $this->payment_method_id;
                $cashBook->shift_id = $this->shift_id;
                $cashBook->warehouse_id = $this->warehouse_id;
                $cashBook->amount = $this->paid_amount;
                $cashBook->type = 1; // in
                $cashBook->description = 'Thu tiền từ đơn hàng: ' . $this->code;
                
                // Calculate balance
                $lastRecord = CashBook::find()
                    ->where(['payment_method_id' => $this->payment_method_id])
                    ->orderBy(['id' => SORT_DESC])
                    ->one();
                    
                $balance = $lastRecord ? $lastRecord->balance : 0;
                $cashBook->balance = $balance + $this->paid_amount;
                
                if (!$cashBook->save()) {
                    throw new \Exception('Không thể ghi sổ quỹ');
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
     * Update payment status
     */
    public function updatePaymentStatus()
    {
        if ($this->paid_amount <= 0) {
            $this->payment_status = self::PAYMENT_STATUS_UNPAID;
        } elseif ($this->paid_amount < $this->total_amount) {
            $this->payment_status = self::PAYMENT_STATUS_PARTIALLY;
        } else {
            $this->payment_status = self::PAYMENT_STATUS_PAID;
        }
    }

    /**
     * Get remaining amount
     * 
     * @return float
     */
    public function getRemainingAmount()
    {
        return $this->total_amount - $this->paid_amount;
    }

    /**
     * Calculate order points
     * 
     * @return int
     */
    public function calculatePoints()
    {
        // Get point calculation settings
        $amountPerPoint = 10000; // Default 10,000 VND = 1 point
        
        $setting = Setting::findOne(['category' => 'point', 'key' => 'amount_per_point']);
        if ($setting) {
            $amountPerPoint = (float)$setting->value;
        }
        
        $minAmount = 0; // Default no minimum
        
        $setting = Setting::findOne(['category' => 'point', 'key' => 'min_amount']);
        if ($setting) {
            $minAmount = (float)$setting->value;
        }
        
        // Calculate points
        if ($this->total_amount < $minAmount) {
            return 0;
        }
        
        return floor($this->total_amount / $amountPerPoint);
    }
}