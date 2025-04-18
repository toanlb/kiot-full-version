<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "warehouse".
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $address
 * @property string|null $phone
 * @property int|null $manager_id
 * @property int $is_default
 * @property int $is_active
 * @property string|null $description
 * @property string $created_at
 * @property string $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 *
 * @property Order[] $orders
 * @property ReturnModel[] $returns
 * @property Shift[] $shifts
 * @property Stock[] $stocks
 * @property StockCheck[] $stockChecks
 * @property StockIn[] $stockIns
 * @property StockMovement[] $sourceStockMovements
 * @property StockMovement[] $destinationStockMovements
 * @property StockOut[] $stockOuts
 * @property StockTransfer[] $sourceStockTransfers
 * @property StockTransfer[] $destinationStockTransfers
 * @property User[] $users
 * @property User $manager
 * @property User $createdBy
 * @property User $updatedBy
 * @property UserWarehouse[] $userWarehouses
 */
class Warehouse extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'warehouse';
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
                'updatedByAttribute' => 'updated_by',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'name'], 'required'],
            [['manager_id', 'is_default', 'is_active', 'created_by', 'updated_by'], 'integer'],
            [['description'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['code'], 'string', 'max' => 50],
            [['name'], 'string', 'max' => 255],
            [['address'], 'string', 'max' => 500],
            [['phone'], 'string', 'max' => 20],
            [['code'], 'unique'],
            [['manager_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['manager_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Mã kho',
            'name' => 'Tên kho',
            'address' => 'Địa chỉ',
            'phone' => 'Số điện thoại',
            'manager_id' => 'Quản lý',
            'is_default' => 'Mặc định',
            'is_active' => 'Hoạt động',
            'description' => 'Mô tả',
            'created_at' => 'Ngày tạo',
            'updated_at' => 'Ngày cập nhật',
            'created_by' => 'Người tạo',
            'updated_by' => 'Người cập nhật',
        ];
    }

    /**
     * Gets query for [[Orders]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::class, ['warehouse_id' => 'id']);
    }

    /**
     * Gets query for [[Returns]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReturns()
    {
        return $this->hasMany(ReturnModel::class, ['warehouse_id' => 'id']);
    }

    /**
     * Gets query for [[Shifts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShifts()
    {
        return $this->hasMany(Shift::class, ['warehouse_id' => 'id']);
    }

    /**
     * Gets query for [[Stocks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStocks()
    {
        return $this->hasMany(Stock::class, ['warehouse_id' => 'id']);
    }

    /**
     * Gets query for [[StockChecks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStockChecks()
    {
        return $this->hasMany(StockCheck::class, ['warehouse_id' => 'id']);
    }

    /**
     * Gets query for [[StockIns]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStockIns()
    {
        return $this->hasMany(StockIn::class, ['warehouse_id' => 'id']);
    }

    /**
     * Gets query for [[SourceStockMovements]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSourceStockMovements()
    {
        return $this->hasMany(StockMovement::class, ['source_warehouse_id' => 'id']);
    }

    /**
     * Gets query for [[DestinationStockMovements]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDestinationStockMovements()
    {
        return $this->hasMany(StockMovement::class, ['destination_warehouse_id' => 'id']);
    }

    /**
     * Gets query for [[StockOuts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStockOuts()
    {
        return $this->hasMany(StockOut::class, ['warehouse_id' => 'id']);
    }

    /**
     * Gets query for [[SourceStockTransfers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSourceStockTransfers()
    {
        return $this->hasMany(StockTransfer::class, ['source_warehouse_id' => 'id']);
    }

    /**
     * Gets query for [[DestinationStockTransfers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDestinationStockTransfers()
    {
        return $this->hasMany(StockTransfer::class, ['destination_warehouse_id' => 'id']);
    }

    /**
     * Gets query for [[Users]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['warehouse_id' => 'id']);
    }

    /**
     * Lấy danh sách kho hàng dưới dạng mảng key-value (id => name)
     * @param bool $onlyActive Chỉ lấy kho hàng hoạt động
     * @return array
     */
    public static function getList($onlyActive = true)
    {
        $query = self::find();
        
        if ($onlyActive) {
            $query->where(['is_active' => 1]);
        }
        
        $warehouses = $query->orderBy(['is_default' => SORT_DESC, 'name' => SORT_ASC])->all();
        
        return ArrayHelper::map($warehouses, 'id', 'name');
    }

    /**
     * Gets query for [[Manager]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getManager()
    {
        return $this->hasOne(User::class, ['id' => 'manager_id']);
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
     * Gets query for [[UpdatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }

    /**
     * Gets query for [[UserWarehouses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserWarehouses()
    {
        return $this->hasMany(UserWarehouse::class, ['warehouse_id' => 'id']);
    }
}