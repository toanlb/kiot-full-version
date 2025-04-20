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
 * @property int $payment_status 0: unpaid, 1: partially, 2: paid
 * @property int $status 0: draft, 1: confirmed, 2: completed, 3: canceled
 * @property string|null $note
 * @property string $created_at
 * @property string $updated_at
 * @property int|null $created_by
 * @property int|null $approved_by
 * @property string|null $approved_at
 *
 * @property ProductBatch[] $productBatches
 * @property StockInDetail[] $stockInDetails
 * @property StockMovement[] $stockMovements
 * @property Supplier $supplier
 * @property User $createdBy
 * @property User $approvedBy
 * @property Warehouse $warehouse
 */
class StockIn extends ActiveRecord
{
    const STATUS_DRAFT = 0;
    const STATUS_CONFIRMED = 1;
    const STATUS_COMPLETED = 2;
    const STATUS_CANCELED = 3;
    
    const PAYMENT_STATUS_UNPAID = 0;
    const PAYMENT_STATUS_PARTIALLY_PAID = 1;
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
            [['code', 'reference_number'], 'string', 'max' => 100],
            [['code'], 'unique'],
            [['payment_status'], 'in', 'range' => [self::PAYMENT_STATUS_UNPAID, self::PAYMENT_STATUS_PARTIALLY_PAID, self::PAYMENT_STATUS_PAID]],
            [['status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_CONFIRMED, self::STATUS_COMPLETED, self::STATUS_CANCELED]],
            [['warehouse_id'], 'exist', 'skipOnError' => true, 'targetClass' => Warehouse::class, 'targetAttribute' => ['warehouse_id' => 'id']],
            [['supplier_id'], 'exist', 'skipOnError' => true, 'targetClass' => Supplier::class, 'targetAttribute' => ['supplier_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
            [['approved_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['approved_by' => 'id']],
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
            'stock_in_date' => 'Ngày nhập kho',
            'reference_number' => 'Số tham chiếu',
            'total_amount' => 'Tổng tiền',
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
     * Gets query for [[StockMovements]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStockMovements()
    {
        return $this->hasMany(StockMovement::class, ['reference_id' => 'id'])
            ->andWhere(['reference_type' => 'stock_in']);
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
     * Gets query for [[CreatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
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
     * Gets query for [[Warehouse]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWarehouse()
    {
        return $this->hasOne(Warehouse::class, ['id' => 'warehouse_id']);
    }
    
    /**
     * Gets the status label
     *
     * @return string
     */
    public function getStatusLabel()
    {
        $statuses = self::getStatusList();
        return isset($statuses[$this->status]) ? $statuses[$this->status] : '';
    }
    
    /**
     * Gets the payment status label
     *
     * @return string
     */
    public function getPaymentStatusLabel()
    {
        $statuses = self::getPaymentStatusList();
        return isset($statuses[$this->payment_status]) ? $statuses[$this->payment_status] : '';
    }
    
    /**
     * Returns list of statuses
     *
     * @return array
     */
    public static function getStatusList()
    {
        return [
            self::STATUS_DRAFT => 'Nháp',
            self::STATUS_CONFIRMED => 'Đã xác nhận',
            self::STATUS_COMPLETED => 'Hoàn thành',
            self::STATUS_CANCELED => 'Đã hủy',
        ];
    }
    
    /**
     * Returns list of payment statuses
     *
     * @return array
     */
    public static function getPaymentStatusList()
    {
        return [
            self::PAYMENT_STATUS_UNPAID => 'Chưa thanh toán',
            self::PAYMENT_STATUS_PARTIALLY_PAID => 'Thanh toán một phần',
            self::PAYMENT_STATUS_PAID => 'Đã thanh toán',
        ];
    }
    
    /**
     * Checks if the stock in can be edited
     *
     * @return boolean
     */
    public function canEdit()
    {
        return $this->status === self::STATUS_DRAFT;
    }
    
    /**
     * Checks if the stock in can be confirmed
     *
     * @return boolean
     */
    public function canConfirm()
    {
        return $this->status === self::STATUS_DRAFT;
    }
    
    /**
     * Checks if the stock in can be completed
     *
     * @return boolean
     */
    public function canComplete()
    {
        return $this->status === self::STATUS_CONFIRMED;
    }
    
    /**
     * Checks if the stock in can be canceled
     *
     * @return boolean
     */
    public function canCancel()
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_CONFIRMED]);
    }
    
    /**
     * Calculates the remaining amount to be paid
     *
     * @return float
     */
    public function getRemainingAmount()
    {
        return max(0, $this->final_amount - $this->paid_amount);
    }
    
    /**
     * Updates the payment status based on paid amount
     */
    public function updatePaymentStatus()
    {
        $remainingAmount = $this->getRemainingAmount();
        
        if ($remainingAmount <= 0) {
            $this->payment_status = self::PAYMENT_STATUS_PAID;
        } elseif ($this->paid_amount > 0) {
            $this->payment_status = self::PAYMENT_STATUS_PARTIALLY_PAID;
        } else {
            $this->payment_status = self::PAYMENT_STATUS_UNPAID;
        }
        
        $this->save(false);
    }
    
    /**
     * Generates a new stock in code
     *
     * @return string
     */
    public static function generateCode()
    {
        $prefix = 'NK';
        $date = date('ymd');
        $lastStock = self::find()
            ->where(['like', 'code', $prefix . $date])
            ->orderBy(['id' => SORT_DESC])
            ->one();
            
        $lastNumber = 1;
        if ($lastStock) {
            $lastNumberStr = substr($lastStock->code, -4);
            $lastNumber = (int)$lastNumberStr + 1;
        }
        
        return $prefix . $date . str_pad($lastNumber, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Calculates total amount from details
     */
    public function calculateTotals()
    {
        $details = $this->stockInDetails;
        
        $this->total_amount = 0;
        $this->discount_amount = 0;
        $this->tax_amount = 0;
        
        foreach ($details as $detail) {
            $this->total_amount += $detail->total_price;
            $this->discount_amount += $detail->discount_amount;
            $this->tax_amount += $detail->tax_amount;
        }
        
        $this->final_amount = $this->total_amount - $this->discount_amount + $this->tax_amount;
        $this->save(false);
    }
}