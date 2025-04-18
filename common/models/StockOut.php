<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "stock_out".
 *
 * @property int $id
 * @property string $code
 * @property int $warehouse_id
 * @property int|null $reference_id
 * @property string|null $reference_type
 * @property string|null $recipient
 * @property string $stock_out_date
 * @property float $total_amount
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
 * @property StockOutDetail[] $stockOutDetails
 * @property Warehouse $warehouse
 */
class StockOut extends ActiveRecord
{
    const STATUS_DRAFT = 0;
    const STATUS_CONFIRMED = 1;
    const STATUS_COMPLETED = 2;
    const STATUS_CANCELED = 3;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'stock_out';
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
            [['code', 'warehouse_id', 'stock_out_date'], 'required'],
            [['warehouse_id', 'reference_id', 'status', 'created_by', 'approved_by'], 'integer'],
            [['stock_out_date', 'created_at', 'updated_at', 'approved_at'], 'safe'],
            [['total_amount'], 'number'],
            [['note'], 'string'],
            [['code'], 'string', 'max' => 50],
            [['reference_type'], 'string', 'max' => 50],
            [['recipient'], 'string', 'max' => 255],
            [['code'], 'unique'],
            [['approved_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['approved_by' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
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
            'reference_id' => 'ID tham chiếu',
            'reference_type' => 'Loại tham chiếu',
            'recipient' => 'Người nhận',
            'stock_out_date' => 'Ngày xuất',
            'total_amount' => 'Tổng tiền',
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
     * Gets query for [[StockOutDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStockOutDetails()
    {
        return $this->hasMany(StockOutDetail::class, ['stock_out_id' => 'id']);
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
            self::STATUS_COMPLETED => 'Hoàn thành',
            self::STATUS_CANCELED => 'Đã hủy',
        ];
    }

    /**
     * Generate stock out code
     * 
     * @return string
     */
    public static function generateCode()
    {
        $prefix = 'XK';
        $year = date('y');
        $month = date('m');
        
        $latestStockOut = self::find()
            ->where(['LIKE', 'code', $prefix . $year . $month])
            ->orderBy(['id' => SORT_DESC])
            ->one();
            
        $sequence = '001';
        if ($latestStockOut) {
            $parts = explode($prefix . $year . $month, $latestStockOut->code);
            if (isset($parts[1])) {
                $sequence = str_pad((int)$parts[1] + 1, 3, '0', STR_PAD_LEFT);
            }
        }
        
        return $prefix . $year . $month . $sequence;
    }

    /**
     * Process stock out (remove from stock)
     * 
     * @return bool
     */
    public function processStockOut()
    {
        if ($this->status != self::STATUS_CONFIRMED) {
            return false;
        }
        
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($this->stockOutDetails as $detail) {
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
                    'reference_type' => 'stock_out',
                    'quantity' => $detail->quantity,
                    'unit_id' => $detail->unit_id,
                    'movement_type' => StockMovement::TYPE_OUT,
                    'movement_date' => $this->stock_out_date,
                    'note' => 'Xuất kho từ phiếu: ' . $this->code,
                ]);
            }
            
            // Update stock out status
            $this->status = self::STATUS_COMPLETED;
            $this->save(false);
            
            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage());
            return false;
        }
    }
}