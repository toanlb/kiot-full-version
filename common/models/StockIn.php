<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "stock_in".
 *
 * @property int $id
 * @property string $code
 * @property int $warehouse_id
 * @property int|null $supplier_id
 * @property string $stock_in_date
 * @property string|null $reference_number
 * @property float $total_amount
 * @property float $discount_amount
 * @property float $tax_amount
 * @property float $final_amount
 * @property float $paid_amount
 * @property int $payment_status
 * @property int $status
 * @property string|null $note
 * @property string $created_at
 * @property string $updated_at
 * @property int|null $created_by
 * @property int|null $approved_by
 * @property string|null $approved_at
 *
 * @property User $approvedBy
 * @property User $createdBy
 * @property ProductBatch[] $productBatches
 * @property StockInDetail[] $stockInDetails
 * @property Supplier $supplier
 * @property Warehouse $warehouse
 */
class StockIn extends ActiveRecord
{
    const STATUS_DRAFT = 0;
    const STATUS_CONFIRMED = 1;
    const STATUS_COMPLETED = 2;
    const STATUS_CANCELED = 3;

    const PAYMENT_STATUS_UNPAID = 0;
    const PAYMENT_STATUS_PARTIALLY = 1;
    const PAYMENT_STATUS_PAID = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'stock_in';
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
            [['code', 'warehouse_id', 'stock_in_date'], 'required'],
            [['warehouse_id', 'supplier_id', 'payment_status', 'status', 'created_by', 'approved_by'], 'integer'],
            [['stock_in_date', 'created_at', 'updated_at', 'approved_at'], 'safe'],
            [['total_amount', 'discount_amount', 'tax_amount', 'final_amount', 'paid_amount'], 'number'],
            [['note'], 'string'],
            [['code', 'reference_number'], 'string', 'max' => 50],
            [['code'], 'unique'],
            [['approved_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['approved_by' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
            [['supplier_id'], 'exist', 'skipOnError' => true, 'targetClass' => Supplier::class, 'targetAttribute' => ['supplier_id' => 'id']],
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
            'code' => 'Mã phiếu',
            'warehouse_id' => 'Kho',
            'supplier_id' => 'Nhà cung cấp',
            'stock_in_date' => 'Ngày nhập',
            'reference_number' => 'Số tham chiếu',
            'total_amount' => 'Tổng tiền hàng',
            'discount_amount' => 'Chiết khấu',
            'tax_amount' => 'Thuế',
            'final_amount' => 'Thành tiền',
            'paid_amount' => 'Đã thanh toán',
            'payment_status' => 'Trạng thái thanh toán',
            'status' => 'Trạng thái',
            'note' => 'Ghi chú',
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
     * Gets query for [[ProductBatches]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductBatches()
    {
        return $this->hasMany(ProductBatch::class, ['stock_in_id' => 'id']);
    }

    /**
     * Gets query for [[StockInDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStockInDetails()
    {
        return $this->hasMany(StockInDetail::class, ['stock_in_id' => 'id']);
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
     * Get statuses
     * 
     * @return array
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_DRAFT => 'Nháp',
            self::STATUS_CONFIRMED => 'Đã xác nhận',
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
     * Update stock in payment status
     */
    public function updatePaymentStatus()
    {
        if ($this->paid_amount <= 0) {
            $this->payment_status = self::PAYMENT_STATUS_UNPAID;
        } elseif ($this->paid_amount < $this->final_amount) {
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
        return $this->final_amount - $this->paid_amount;
    }

    /**
     * Generate stock in code
     * 
     * @return string
     */
    public static function generateCode()
    {
        $prefix = 'NK';
        $year = date('y');
        $month = date('m');
        
        $latestStockIn = self::find()
            ->where(['LIKE', 'code', $prefix . $year . $month])
            ->orderBy(['id' => SORT_DESC])
            ->one();
            
        $sequence = '001';
        if ($latestStockIn) {
            $parts = explode($prefix . $year . $month, $latestStockIn->code);
            if (isset($parts[1])) {
                $sequence = str_pad((int)$parts[1] + 1, 3, '0', STR_PAD_LEFT);
            }
        }
        
        return $prefix . $year . $month . $sequence;
    }

    /**
     * Process stock in (add to stock)
     * 
     * @return bool
     */
    public function processStockIn()
    {
        if ($this->status != self::STATUS_CONFIRMED) {
            return false;
        }
        
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($this->stockInDetails as $detail) {
                // Add to stock
                Stock::increase($detail->product_id, $this->warehouse_id, $detail->quantity);
                
                // Record movement
                StockMovement::recordMovement([
                    'product_id' => $detail->product_id,
                    'destination_warehouse_id' => $this->warehouse_id,
                    'reference_id' => $this->id,
                    'reference_type' => 'stock_in',
                    'quantity' => $detail->quantity,
                    'unit_id' => $detail->unit_id,
                    'movement_type' => StockMovement::TYPE_IN,
                    'movement_date' => $this->stock_in_date,
                    'note' => 'Nhập kho từ phiếu: ' . $this->code,
                ]);
                
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
                        $batch->expiry_date = $detail->expiry_date;
                        $batch->quantity = 0;
                        $batch->cost_price = $detail->unit_price;
                        $batch->stock_in_id = $this->id;
                    }
                    
                    $batch->quantity += $detail->quantity;
                    
                    if (!$batch->save()) {
                        throw new \Exception('Không thể lưu thông tin lô hàng');
                    }
                }
                
                // Update product cost price if needed
                $product = Product::findOne($detail->product_id);
                if ($product && $product->cost_price != $detail->unit_price) {
                    $product->cost_price = $detail->unit_price;
                    
                    if (!$product->save()) {
                        throw new \Exception('Không thể cập nhật giá nhập sản phẩm');
                    }
                    
                    // Record price history
                    ProductPriceHistory::recordPriceChange(
                        $product->id,
                        $product->cost_price,
                        $product->selling_price,
                        'Cập nhật từ phiếu nhập kho: ' . $this->code,
                        $this->stock_in_date
                    );
                }
            }
            
            // Update stock in status
            $this->status = self::STATUS_COMPLETED;
            $this->save(false);
            
            // Update supplier debt if needed
            if ($this->supplier_id && $this->final_amount > 0) {
                $debt = new SupplierDebt();
                $debt->supplier_id = $this->supplier_id;
                $debt->reference_id = $this->id;
                $debt->reference_type = 'stock_in';
                $debt->amount = $this->final_amount;
                $debt->balance = $this->final_amount - $this->paid_amount;
                $debt->type = 1; // debt
                $debt->description = 'Nợ từ phiếu nhập kho: ' . $this->code;
                $debt->transaction_date = $this->stock_in_date;
                
                if (!$debt->save()) {
                    throw new \Exception('Không thể lưu thông tin công nợ nhà cung cấp');
                }
                
                // Update supplier debt amount
                $supplier = $this->supplier;
                $supplier->debt_amount += $debt->balance;
                $supplier->save(false);
            }
            
            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage());
            return false;
        }