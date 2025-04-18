<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "warranty".
 *
 * @property int $id
 * @property string $code
 * @property int|null $order_id
 * @property int|null $order_detail_id
 * @property int $product_id
 * @property int|null $customer_id
 * @property string|null $serial_number
 * @property string $start_date
 * @property string $end_date
 * @property int $status_id
 * @property int $active
 * @property string|null $note
 * @property string $created_at
 * @property string $updated_at
 * @property int|null $created_by
 *
 * @property User $createdBy
 * @property Customer $customer
 * @property Order $order
 * @property OrderDetail $orderDetail
 * @property Product $product
 * @property WarrantyStatus $status
 * @property WarrantyDetail[] $warrantyDetails
 */
class Warranty extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'warranty';
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
            [['code', 'product_id', 'start_date', 'end_date', 'status_id'], 'required'],
            [['order_id', 'order_detail_id', 'product_id', 'customer_id', 'status_id', 'active', 'created_by'], 'integer'],
            [['start_date', 'end_date', 'created_at', 'updated_at'], 'safe'],
            [['note'], 'string'],
            [['code', 'serial_number'], 'string', 'max' => 100],
            [['code'], 'unique'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customer::class, 'targetAttribute' => ['customer_id' => 'id']],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::class, 'targetAttribute' => ['order_id' => 'id']],
            [['order_detail_id'], 'exist', 'skipOnError' => true, 'targetClass' => OrderDetail::class, 'targetAttribute' => ['order_detail_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['status_id'], 'exist', 'skipOnError' => true, 'targetClass' => WarrantyStatus::class, 'targetAttribute' => ['status_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Mã bảo hành',
            'order_id' => 'Đơn hàng',
            'order_detail_id' => 'Chi tiết đơn hàng',
            'product_id' => 'Sản phẩm',
            'customer_id' => 'Khách hàng',
            'serial_number' => 'Số sê-ri',
            'start_date' => 'Ngày bắt đầu',
            'end_date' => 'Ngày kết thúc',
            'status_id' => 'Trạng thái',
            'active' => 'Hoạt động',
            'note' => 'Ghi chú',
            'created_at' => 'Ngày tạo',
            'updated_at' => 'Ngày cập nhật',
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
     * Gets query for [[OrderDetail]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrderDetail()
    {
        return $this->hasOne(OrderDetail::class, ['id' => 'order_detail_id']);
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
     * Gets query for [[Status]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStatus()
    {
        return $this->hasOne(WarrantyStatus::class, ['id' => 'status_id']);
    }

    /**
     * Gets query for [[WarrantyDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWarrantyDetails()
    {
        return $this->hasMany(WarrantyDetail::class, ['warranty_id' => 'id']);
    }

    /**
     * Generate warranty code
     * 
     * @return string
     */
    public static function generateCode()
    {
        $prefix = 'BH';
        $year = date('y');
        $month = date('m');
        
        $latestWarranty = self::find()
            ->where(['LIKE', 'code', $prefix . $year . $month])
            ->orderBy(['id' => SORT_DESC])
            ->one();
            
        $sequence = '001';
        if ($latestWarranty) {
            $parts = explode($prefix . $year . $month, $latestWarranty->code);
            if (isset($parts[1])) {
                $sequence = str_pad((int)$parts[1] + 1, 3, '0', STR_PAD_LEFT);
            }
        }
        
        return $prefix . $year . $month . $sequence;
    }

    /**
     * Get warranty period
     * 
     * @return int
     */
    public function getWarrantyPeriod()
    {
        $startDate = strtotime($this->start_date);
        $endDate = strtotime($this->end_date);
        
        return round(($endDate - $startDate) / (60 * 60 * 24));
    }

    /**
     * Get remaining days
     * 
     * @return int
     */
    public function getRemainingDays()
    {
        $today = strtotime(date('Y-m-d'));
        $endDate = strtotime($this->end_date);
        
        $remainingDays = round(($endDate - $today) / (60 * 60 * 24));
        
        return max(0, $remainingDays);
    }

    /**
     * Check if warranty is valid
     * 
     * @return bool
     */
    public function isValid()
    {
        return $this->active && $this->getRemainingDays() > 0;
    }

    /**
     * Get last warranty detail
     * 
     * @return WarrantyDetail|null
     */
    public function getLastDetail()
    {
        return WarrantyDetail::find()
            ->where(['warranty_id' => $this->id])
            ->orderBy(['service_date' => SORT_DESC])
            ->one();
    }

    /**
     * Find by serial number
     * 
     * @param string $serialNumber
     * @return Warranty|null
     */
    public static function findBySerialNumber($serialNumber)
    {
        return self::find()
            ->where(['serial_number' => $serialNumber, 'active' => 1])
            ->one();
    }

    /**
     * Find by customer
     * 
     * @param int $customerId
     * @param bool $onlyActive
     * @return Warranty[]
     */
    public static function findByCustomer($customerId, $onlyActive = true)
    {
        $query = self::find()
            ->where(['customer_id' => $customerId]);
            
        if ($onlyActive) {
            $query->andWhere(['active' => 1]);
        }
        
        return $query->orderBy(['end_date' => SORT_DESC])->all();
    }
    
    /**
     * Find by product
     * 
     * @param int $productId
     * @param bool $onlyActive
     * @return Warranty[]
     */
    public static function findByProduct($productId, $onlyActive = true)
    {
        $query = self::find()
            ->where(['product_id' => $productId]);
            
        if ($onlyActive) {
            $query->andWhere(['active' => 1]);
        }
        
        return $query->orderBy(['end_date' => SORT_DESC])->all();
    }
}