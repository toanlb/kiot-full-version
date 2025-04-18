<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "stock_check".
 *
 * @property int $id
 * @property string $code
 * @property int $warehouse_id
 * @property string $check_date
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
 * @property StockCheckDetail[] $stockCheckDetails
 * @property Warehouse $warehouse
 */
class StockCheck extends ActiveRecord
{
    const STATUS_DRAFT = 0;
    const STATUS_CONFIRMED = 1;
    const STATUS_ADJUSTED = 2;
    const STATUS_CANCELED = 3;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'stock_check';
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
            [['code', 'warehouse_id', 'check_date'], 'required'],
            [['warehouse_id', 'status', 'created_by', 'approved_by'], 'integer'],
            [['check_date', 'created_at', 'updated_at', 'approved_at'], 'safe'],
            [['note'], 'string'],
            [['code'], 'string', 'max' => 50],
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
            'check_date' => 'Ngày kiểm',
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
     * Gets query for [[StockCheckDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStockCheckDetails()
    {
        return $this->hasMany(StockCheckDetail::class, ['stock_check_id' => 'id']);
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
            self::STATUS_ADJUSTED => 'Đã điều chỉnh',
            self::STATUS_CANCELED => 'Đã hủy',
        ];
    }

    /**
     * Generate stock check code
     * 
     * @return string
     */
    public static function generateCode()
    {
        $prefix = 'KK';
        $year = date('y');
        $month = date('m');
        
        $latestStockCheck = self::find()
            ->where(['LIKE', 'code', $prefix . $year . $month])
            ->orderBy(['id' => SORT_DESC])
            ->one();
            
        $sequence = '001';
        if ($latestStockCheck) {
            $parts = explode($prefix . $year . $month, $latestStockCheck->code);
            if (isset($parts[1])) {
                $sequence = str_pad((int)$parts[1] + 1, 3, '0', STR_PAD_LEFT);
            }
        }
        
        return $prefix . $year . $month . $sequence;
    }

    /**
     * Initialize stock check details
     * 
     * @return bool
     */
    public function initializeDetails()
    {
        if ($this->status != self::STATUS_DRAFT) {
            return false;
        }
        
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Clear existing details
            StockCheckDetail::deleteAll(['stock_check_id' => $this->id]);
            
            // Get all products in the warehouse
            $stocks = Stock::find()
                ->where(['warehouse_id' => $this->warehouse_id])
                ->andWhere(['>', 'quantity', 0])
                ->all();
                
            foreach ($stocks as $stock) {
                $detail = new StockCheckDetail();
                $detail->stock_check_id = $this->id;
                $detail->product_id = $stock->product_id;
                $detail->system_quantity = $stock->quantity;
                $detail->actual_quantity = $stock->quantity;
                $detail->difference = 0;
                $detail->unit_id = $stock->product->unit_id;
                
                if (!$detail->save()) {
                    throw new \Exception('Không thể lưu chi tiết kiểm kho');
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
     * Apply stock adjustments
     * 
     * @return bool
     */
    public function applyAdjustments()
    {
        if ($this->status != self::STATUS_CONFIRMED) {
            return false;
        }
        
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($this->stockCheckDetails as $detail) {
                if ($detail->difference == 0 || !$detail->adjustment_approved) {
                    continue;
                }
                
                $stock = Stock::findOne(['product_id' => $detail->product_id, 'warehouse_id' => $this->warehouse_id]);
                
                if (!$stock) {
                    $stock = new Stock();
                    $stock->product_id = $detail->product_id;
                    $stock->warehouse_id = $this->warehouse_id;
                    $stock->quantity = 0;
                }
                
                // Record movement
                $movementType = ($detail->difference > 0) ? StockMovement::TYPE_IN : StockMovement::TYPE_OUT;
                $quantity = abs($detail->difference);
                
                StockMovement::recordMovement([
                    'product_id' => $detail->product_id,
                    'source_warehouse_id' => $this->warehouse_id,
                    'destination_warehouse_id' => $this->warehouse_id,
                    'reference_id' => $this->id,
                    'reference_type' => 'stock_check',
                    'quantity' => $quantity,
                    'unit_id' => $detail->unit_id,
                    'movement_type' => StockMovement::TYPE_CHECK,
                    'movement_date' => $this->check_date,
                    'note' => 'Điều chỉnh từ phiếu kiểm kho: ' . $this->code,
                ]);
                
                // Update stock
                $stock->quantity = $detail->actual_quantity;
                
                if (!$stock->save()) {
                    throw new \Exception('Không thể cập nhật tồn kho');
                }
            }
            
            // Update stock check status
            $this->status = self::STATUS_ADJUSTED;
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
     * Get differences summary
     * 
     * @return array
     */
    public function getDifferencesSummary()
    {
        $details = $this->stockCheckDetails;
        
        $total = count($details);
        $matches = 0;
        $positives = 0;
        $negatives = 0;
        
        foreach ($details as $detail) {
            if ($detail->difference == 0) {
                $matches++;
            } elseif ($detail->difference > 0) {
                $positives++;
            } else {
                $negatives++;
            }
        }
        
        return [
            'total' => $total,
            'matches' => $matches,
            'positives' => $positives,
            'negatives' => $negatives,
        ];
    }
}