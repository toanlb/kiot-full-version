<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "stock_movement".
 *
 * @property int $id
 * @property int $product_id
 * @property int|null $source_warehouse_id
 * @property int|null $destination_warehouse_id
 * @property int|null $reference_id
 * @property string|null $reference_type
 * @property int $quantity
 * @property int $balance
 * @property int $unit_id
 * @property int $movement_type
 * @property string $movement_date
 * @property string|null $note
 * @property string $created_at
 * @property int|null $created_by
 *
 * @property User $createdBy
 * @property Warehouse $destinationWarehouse
 * @property Product $product
 * @property Warehouse $sourceWarehouse
 * @property ProductUnit $unit
 */
class StockMovement extends ActiveRecord
{
    const TYPE_IN = 1;
    const TYPE_OUT = 2;
    const TYPE_TRANSFER = 3;
    const TYPE_CHECK = 4;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'stock_movement';
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
            [['product_id', 'quantity', 'balance', 'unit_id', 'movement_type', 'movement_date'], 'required'],
            [['product_id', 'source_warehouse_id', 'destination_warehouse_id', 'reference_id', 'quantity', 'balance', 'unit_id', 'movement_type', 'created_by'], 'integer'],
            [['movement_date', 'created_at'], 'safe'],
            [['note'], 'string'],
            [['reference_type'], 'string', 'max' => 50],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
            [['destination_warehouse_id'], 'exist', 'skipOnError' => true, 'targetClass' => Warehouse::class, 'targetAttribute' => ['destination_warehouse_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['source_warehouse_id'], 'exist', 'skipOnError' => true, 'targetClass' => Warehouse::class, 'targetAttribute' => ['source_warehouse_id' => 'id']],
            [['unit_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductUnit::class, 'targetAttribute' => ['unit_id' => 'id']],
            ['movement_type', 'validateWarehouse'],
        ];
    }

    /**
     * Validate warehouse based on movement type
     */
    public function validateWarehouse($attribute, $params)
    {
        if (!$this->hasErrors()) {
            switch ($this->movement_type) {
                case self::TYPE_IN:
                    if (!$this->destination_warehouse_id) {
                        $this->addError('destination_warehouse_id', 'Kho đích là bắt buộc đối với nhập kho.');
                    }
                    break;
                case self::TYPE_OUT:
                    if (!$this->source_warehouse_id) {
                        $this->addError('source_warehouse_id', 'Kho nguồn là bắt buộc đối với xuất kho.');
                    }
                    break;
                case self::TYPE_TRANSFER:
                    if (!$this->source_warehouse_id) {
                        $this->addError('source_warehouse_id', 'Kho nguồn là bắt buộc đối với chuyển kho.');
                    }
                    if (!$this->destination_warehouse_id) {
                        $this->addError('destination_warehouse_id', 'Kho đích là bắt buộc đối với chuyển kho.');
                    }
                    if ($this->source_warehouse_id == $this->destination_warehouse_id) {
                        $this->addError('destination_warehouse_id', 'Kho nguồn và kho đích không thể giống nhau.');
                    }
                    break;
                case self::TYPE_CHECK:
                    if (!$this->source_warehouse_id && !$this->destination_warehouse_id) {
                        $this->addError('source_warehouse_id', 'Phải chỉ định ít nhất một kho.');
                    }
                    break;
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
            'product_id' => 'Sản phẩm',
            'source_warehouse_id' => 'Kho nguồn',
            'destination_warehouse_id' => 'Kho đích',
            'reference_id' => 'ID tham chiếu',
            'reference_type' => 'Loại tham chiếu',
            'quantity' => 'Số lượng',
            'balance' => 'Số dư',
            'unit_id' => 'Đơn vị',
            'movement_type' => 'Loại di chuyển',
            'movement_date' => 'Ngày di chuyển',
            'note' => 'Ghi chú',
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
     * Gets query for [[DestinationWarehouse]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDestinationWarehouse()
    {
        return $this->hasOne(Warehouse::class, ['id' => 'destination_warehouse_id']);
    }

    /**
     * Gets query for [[Product]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
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
     * Gets query for [[Unit]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUnit()
    {
        return $this->hasOne(ProductUnit::class, ['id' => 'unit_id']);
    }

    /**
     * Get movement type label
     * 
     * @return string
     */
    public function getMovementTypeLabel()
    {
        $types = self::getMovementTypes();
        return isset($types[$this->movement_type]) ? $types[$this->movement_type] : 'Không xác định';
    }

    /**
     * Get movement types
     * 
     * @return array
     */
    public static function getMovementTypes()
    {
        return [
            self::TYPE_IN => 'Nhập kho',
            self::TYPE_OUT => 'Xuất kho',
            self::TYPE_TRANSFER => 'Chuyển kho',
            self::TYPE_CHECK => 'Kiểm kho',
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
            case 'stock_in':
                $model = StockIn::findOne($this->reference_id);
                return $model ? 'Nhập kho: ' . $model->code : '';
            
            case 'stock_out':
                $model = StockOut::findOne($this->reference_id);
                return $model ? 'Xuất kho: ' . $model->code : '';
                
            case 'stock_transfer':
                $model = StockTransfer::findOne($this->reference_id);
                return $model ? 'Chuyển kho: ' . $model->code : '';
                
            case 'stock_check':
                $model = StockCheck::findOne($this->reference_id);
                return $model ? 'Kiểm kho: ' . $model->code : '';
                
            case 'order':
                $model = Order::findOne($this->reference_id);
                return $model ? 'Đơn hàng: ' . $model->code : '';
                
            case 'return':
                $model = ReturnModel::findOne($this->reference_id);
                return $model ? 'Trả hàng: ' . $model->code : '';
                
            default:
                return $this->reference_type . ': ' . $this->reference_id;
        }
    }

    /**
     * Record stock movement
     * 
     * @param array $params
     * @return StockMovement|null
     */
    public static function recordMovement($params)
    {
        $model = new self();
        $model->attributes = $params;
        
        // Calculate balance
        $warehouseId = null;
        if ($model->movement_type == self::TYPE_IN) {
            $warehouseId = $model->destination_warehouse_id;
        } elseif ($model->movement_type == self::TYPE_OUT || $model->movement_type == self::TYPE_TRANSFER) {
            $warehouseId = $model->source_warehouse_id;
        } elseif ($model->movement_type == self::TYPE_CHECK) {
            $warehouseId = $model->source_warehouse_id ?: $model->destination_warehouse_id;
        }
        
        if ($warehouseId) {
            $stock = Stock::findOne(['product_id' => $model->product_id, 'warehouse_id' => $warehouseId]);
            $model->balance = $stock ? $stock->quantity : 0;
        }
        
        if ($model->save()) {
            return $model;
        }
        
        return null;
    }
}