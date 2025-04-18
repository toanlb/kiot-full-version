<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "customer".
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $address
 * @property int|null $customer_group_id
 * @property string|null $tax_code
 * @property string|null $birthday
 * @property int|null $gender
 * @property int $status
 * @property float|null $credit_limit
 * @property float|null $debt_amount
 * @property string|null $company_name
 * @property int|null $province_id
 * @property int|null $district_id
 * @property int|null $ward_id
 * @property string $created_at
 * @property string $updated_at
 * @property int|null $created_by
 *
 * @property User $createdBy
 * @property CustomerDebt[] $customerDebts
 * @property CustomerGroup $customerGroup
 * @property CustomerPoint $customerPoint
 * @property CustomerPointHistory[] $customerPointHistories
 * @property District $district
 * @property Order[] $orders
 * @property Province $province
 * @property Return[] $returns
 * @property Ward $ward
 * @property Warranty[] $warranties
 */
class Customer extends ActiveRecord
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    const GENDER_OTHER = 0;
    const GENDER_MALE = 1;
    const GENDER_FEMALE = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'customer';
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
            [['code', 'name'], 'required'],
            [['customer_group_id', 'gender', 'status', 'province_id', 'district_id', 'ward_id', 'created_by'], 'integer'],
            [['birthday', 'created_at', 'updated_at'], 'safe'],
            [['credit_limit', 'debt_amount'], 'number'],
            [['code', 'tax_code'], 'string', 'max' => 50],
            [['name', 'email', 'company_name'], 'string', 'max' => 255],
            [['phone'], 'string', 'max' => 20],
            [['address'], 'string', 'max' => 500],
            [['code'], 'unique'],
            [['phone'], 'unique'],
            [['email'], 'unique'],
            [['email'], 'email'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
            [['customer_group_id'], 'exist', 'skipOnError' => true, 'targetClass' => CustomerGroup::class, 'targetAttribute' => ['customer_group_id' => 'id']],
            [['district_id'], 'exist', 'skipOnError' => true, 'targetClass' => District::class, 'targetAttribute' => ['district_id' => 'id']],
            [['province_id'], 'exist', 'skipOnError' => true, 'targetClass' => Province::class, 'targetAttribute' => ['province_id' => 'id']],
            [['ward_id'], 'exist', 'skipOnError' => true, 'targetClass' => Ward::class, 'targetAttribute' => ['ward_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Mã khách hàng',
            'name' => 'Tên khách hàng',
            'phone' => 'Điện thoại',
            'email' => 'Email',
            'address' => 'Địa chỉ',
            'customer_group_id' => 'Nhóm khách hàng',
            'tax_code' => 'Mã số thuế',
            'birthday' => 'Ngày sinh',
            'gender' => 'Giới tính',
            'status' => 'Trạng thái',
            'credit_limit' => 'Hạn mức nợ',
            'debt_amount' => 'Dư nợ',
            'company_name' => 'Tên công ty',
            'province_id' => 'Tỉnh/Thành phố',
            'district_id' => 'Quận/Huyện',
            'ward_id' => 'Phường/Xã',
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
     * Gets query for [[CustomerDebts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerDebts()
    {
        return $this->hasMany(CustomerDebt::class, ['customer_id' => 'id']);
    }

    /**
     * Gets query for [[CustomerGroup]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerGroup()
    {
        return $this->hasOne(CustomerGroup::class, ['id' => 'customer_group_id']);
    }

    /**
     * Gets query for [[CustomerPoint]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerPoint()
    {
        return $this->hasOne(CustomerPoint::class, ['customer_id' => 'id']);
    }

    /**
     * Gets query for [[CustomerPointHistories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerPointHistories()
    {
        return $this->hasMany(CustomerPointHistory::class, ['customer_id' => 'id']);
    }

    /**
     * Gets query for [[District]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDistrict()
    {
        return $this->hasOne(District::class, ['id' => 'district_id']);
    }

    /**
     * Gets query for [[Orders]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::class, ['customer_id' => 'id']);
    }

    /**
     * Gets query for [[Province]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProvince()
    {
        return $this->hasOne(Province::class, ['id' => 'province_id']);
    }

    /**
     * Gets query for [[Returns]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReturns()
    {
        return $this->hasMany(ReturnModel::class, ['customer_id' => 'id']);
    }

    /**
     * Gets query for [[Ward]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWard()
    {
        return $this->hasOne(Ward::class, ['id' => 'ward_id']);
    }

    /**
     * Gets query for [[Warranties]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWarranties()
    {
        return $this->hasMany(Warranty::class, ['customer_id' => 'id']);
    }

    /**
     * Get gender label
     * 
     * @return string
     */
    public function getGenderLabel()
    {
        $genders = self::getGenders();
        return isset($genders[$this->gender]) ? $genders[$this->gender] : 'Không xác định';
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
     * Get genders
     * 
     * @return array
     */
    public static function getGenders()
    {
        return [
            self::GENDER_OTHER => 'Khác',
            self::GENDER_MALE => 'Nam',
            self::GENDER_FEMALE => 'Nữ',
        ];
    }

    /**
     * Get statuses
     * 
     * @return array
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_INACTIVE => 'Không hoạt động',
            self::STATUS_ACTIVE => 'Hoạt động',
        ];
    }

    /**
     * Generate customer code
     * 
     * @return string
     */
    public static function generateCode()
    {
        $prefix = 'KH';
        $year = date('y');
        $month = date('m');
        
        $latestCustomer = self::find()
            ->where(['LIKE', 'code', $prefix . $year . $month])
            ->orderBy(['id' => SORT_DESC])
            ->one();
            
        $sequence = '001';
        if ($latestCustomer) {
            $parts = explode($prefix . $year . $month, $latestCustomer->code);
            if (isset($parts[1])) {
                $sequence = str_pad((int)$parts[1] + 1, 3, '0', STR_PAD_LEFT);
            }
        }
        
        return $prefix . $year . $month . $sequence;
    }

    /**
     * Get full address
     * 
     * @return string
     */
    public function getFullAddress()
    {
        $parts = [];
        
        if ($this->address) {
            $parts[] = $this->address;
        }
        
        if ($this->ward) {
            $parts[] = $this->ward->name;
        }
        
        if ($this->district) {
            $parts[] = $this->district->name;
        }
        
        if ($this->province) {
            $parts[] = $this->province->name;
        }
        
        return implode(', ', $parts);
    }

    /**
     * Get points
     * 
     * @return int
     */
    public function getPoints()
    {
        return $this->customerPoint ? $this->customerPoint->points : 0;
    }

    /**
     * Check credit limit
     * 
     * @param float $additionalAmount
     * @return bool
     */
    public function checkCreditLimit($additionalAmount = 0)
    {
        if ($this->credit_limit <= 0) {
            return true;
        }
        
        return ($this->debt_amount + $additionalAmount) <= $this->credit_limit;
    }

    /**
     * After save
     * 
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        
        // Create customer point record if not exists
        if ($insert) {
            $point = CustomerPoint::findOne(['customer_id' => $this->id]);
            
            if (!$point) {
                $point = new CustomerPoint();
                $point->customer_id = $this->id;
                $point->points = 0;
                $point->total_points_earned = 0;
                $point->total_points_used = 0;
                $point->updated_at = new Expression('NOW()');
                $point->save();
            }
        }
    }
}