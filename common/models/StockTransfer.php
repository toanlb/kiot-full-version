<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "stock_transfer".
 *
 * @property int $id
 * @property string $code
 * @property int $source_warehouse_id
 * @property int $destination_warehouse_id
 * @property string $transfer_date
 * @property int $status
 * @property string|null $note
 * @property string $created_at
 * @property string $updated_at
 * @property int|null $created_by
 * @property int|null $approved_by
 * @property string|null $approved_at
 * @property int|null $received_by
 * @property string|null $received_at
 *
 * @property User $approvedBy
 * @property User $createdBy
 * @property Warehouse $destinationWarehouse
 * @property User $receivedBy
 * @property Warehouse $sourceWarehouse
 * @property StockTransferDetail[] $stockTransferDetails
 */
class StockTransfer extends ActiveRecord
{
    const STATUS_DRAFT = 0;
    const STATUS_CONFIRMED = 1;
    const STATUS_IN_PROGRESS = 2;
    const STATUS_RECEIVED = 3;
    const STATUS_CANCELED = 4;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'stock_transfer';
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
            [['code', 'source_warehouse_id', 'destination_warehouse_id', 'transfer_date'], 'required'],
            [['source_warehouse_id', 'destination_warehouse_id', 'status', 'created_by', 'approved_by', 'received_by'], 'integer'],
            [['transfer_date', 'created_at', 'updated_at', 'approved_at', 'received_at'], 'safe'],
            [['note'], 'string'],
            [['code'], 'string', 'max' => 50],
            [['code'], 'unique'],
            [['approved_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['approved_by' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
            [['destination_warehouse_id'], 'exist', 'skipOnError' => true, 'targetClass' => Warehouse::class, 'targetAttribute' => ['destination_warehouse_id' => 'id']],
            [['received_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['received_by' => 'id']],
            [['source_warehouse_id'], 'exist', 'skipOnError' => true, 'targetClass' => Warehouse::class, 'targetAttribute' => ['source_warehouse_id' => 'id']],
            ['destination_warehouse_id', 'validateWarehouse'],
        ];
    }

    /**
     * Validate warehouse
     */
    public function validateWarehouse($attribute, $params)
    {
        if (!$this->hasErrors() && $this->$attribute && $this->source_warehouse_id) {
            if ($this->$attribute == $this->source_warehouse_id) {
                $this->addError($attribute, 'Kho nguồn và kho đích không thể giống nhau.');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Mã phiếu',
            'source_warehouse_id' => 'Kho nguồn',
            'destination_warehouse_id' => 'Kho đích',
            'transfer_date' => 'Ngày chuyển',
            'status' => 'Trạng thái',
            'note' => 'Ghi chú',
            'created_at' => 'Ngày tạo',
            'updated_at' => 'Ngày cập nhật',
            'created_by' => 'Người tạo',
            'approved_by' => 'Người duyệt',
            'approved_at' => 'Ngày duyệt',
            'received_by' => 'Người nhận',
            'received_at' => 'Ngày nhận',
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
     * Gets query for [[DestinationWarehouse]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDestinationWarehouse()
    {
        return $this->hasOne(Warehouse::class, ['id' => 'destination_warehouse_id']);
    }

    /**
     * Gets query for [[ReceivedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReceivedBy()
    {
        return $this->hasOne(User::class, ['id' => 'received_by']);
    }

    /**
     * Gets query for [[SourceWarehouse]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSourceWarehouse()
    {
        return $this->hasOne(Warehouse::class, ['id' => 'source_warehouse_id']);
    }

    /**
     * Gets query for [[StockTransferDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStockTransferDetails()
    {
        return $this->hasMany(StockTransferDetail::class, ['stock_transfer_id' => 'id']);
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
            self::STATUS_IN_PROGRESS => 'Đang vận chuyển',
            self::STATUS_RECEIVED => 'Đã nhận',
            self::STATUS_CANCELED => 'Đã hủy',
        ];
    }

    /**
     * Generate stock transfer code
     * 
     * @return string
     */
    public static function generateCode()
    {
        $prefix = 'CK';
        $year = date('y');
        $month = date('m');
        
        $latestTransfer = self::find()
            ->where(['LIKE', 'code', $prefix . $year . $month])
            ->orderBy(['id' => SORT_DESC])
            ->one();
            
        $sequence = '001';
        if ($latestTransfer) {
            $parts = explode($prefix . $year . $month, $latestTransfer->code);
            if (isset($parts[1])) {
                $sequence = str_pad((int)$parts[1] + 1, 3, '0', STR_PAD_LEFT);
            }
        }
        
        return $prefix . $year . $month . $sequence;
    }

    /**
     * Process stock transfer (update stock)
     * 
     * @return bool
     */
    public function processTransfer()
    {
        if ($this->status != self::STATUS_CONFIRMED) {
            return false;
        }
        
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($this->stockTransferDetails as $detail) {
                // Check stock
                $stock = Stock::findOne(['product_id' => $detail->product_id, 'warehouse_id' => $this->source_warehouse_id]);
                
                if (!$stock || $stock->quantity < $detail->quantity) {
                    throw new \Exception('Không đủ số lượng tồn kho cho sản phẩm ' . $detail->product->name);
                }
                
                // Handle batch if specified
                if ($detail->batch_number) {
                    $batch = ProductBatch::findOne([
                        'product_id' => $detail->product_id,
                        'warehouse_id' => $this->source_warehouse_id,
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
                    $batches = ProductBatch::findAvailableBatches($detail->product_id, $this->source_warehouse_id);
                    
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
                
                // Reduce stock from source warehouse
                Stock::decrease($detail->product_id, $this->source_warehouse_id, $detail->quantity);
                
                // Record movement
                StockMovement::recordMovement([
                    'product_id' => $detail->product_id,
                    'source_warehouse_id' => $this->source_warehouse_id,
                    'destination_warehouse_id' => $this->destination_warehouse_id,
                    'reference_id' => $this->id,
                    'reference_type' => 'stock_transfer',
                    'quantity' => $detail->quantity,
                    'unit_id' => $detail->unit_id,
                    'movement_type' => StockMovement::TYPE_TRANSFER,
                    'movement_date' => $this->transfer_date,
                    'note' => 'Chuyển kho từ phiếu: ' . $this->code,
                ]);
            }
            
            // Update stock transfer status
            $this->status = self::STATUS_IN_PROGRESS;
            $this->save(false);
            
            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage());
            return false;
        }
    }

    /**
     * Process receive (update destination stock)
     * 
     * @param array $receivedQuantities Array of [detail_id => received_quantity]
     * @return bool
     */
    public function processReceive($receivedQuantities)
    {
        if ($this->status != self::STATUS_IN_PROGRESS) {
            return false;
        }
        
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($this->stockTransferDetails as $detail) {
                $receivedQuantity = isset($receivedQuantities[$detail->id]) ? $receivedQuantities[$detail->id] : 0;
                
                if ($receivedQuantity <= 0) {
                    continue;
                }
                
                if ($receivedQuantity > $detail->quantity) {
                    $receivedQuantity = $detail->quantity;
                }
                
                // Increase stock in destination warehouse
                Stock::increase($detail->product_id, $this->destination_warehouse_id, $receivedQuantity);
                
                // Create batch in destination warehouse if needed
                if ($detail->batch_number) {
                    $batch = ProductBatch::findOne([
                        'product_id' => $detail->product_id,
                        'warehouse_id' => $this->destination_warehouse_id,
                        'batch_number' => $detail->batch_number,
                    ]);
                    
                    if (!$batch) {
                        $sourceBatch = ProductBatch::findOne([
                            'product_id' => $detail->product_id,
                            'warehouse_id' => $this->source_warehouse_id,
                            'batch_number' => $detail->batch_number,
                        ]);
                        
                        $batch = new ProductBatch();
                        $batch->product_id = $detail->product_id;
                        $batch->warehouse_id = $this->destination_warehouse_id;
                        $batch->batch_number = $detail->batch_number;
                        $batch->manufacturing_date = $sourceBatch ? $sourceBatch->manufacturing_date : null;
                        $batch->expiry_date = $sourceBatch ? $sourceBatch->expiry_date : null;
                        $batch->quantity = 0;
                        $batch->cost_price = $sourceBatch ? $sourceBatch->cost_price : $detail->product->cost_price;
                    }
                    
                    $batch->quantity += $receivedQuantity;
                    
                    if (!$batch->save()) {
                        throw new \Exception('Không thể lưu thông tin lô hàng');
                    }
                }
                
                // Update detail
                $detail->received_quantity = $receivedQuantity;
                if (!$detail->save()) {
                    throw new \Exception('Không thể cập nhật thông tin chi tiết chuyển kho');
                }
            }
            
            // Update stock transfer
            $this->received_by = Yii::$app->user->id;
            $this->received_at = new Expression('NOW()');
            $this->status = self::STATUS_RECEIVED;
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