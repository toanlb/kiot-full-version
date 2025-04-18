<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "supplier".
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $address
 * @property string|null $tax_code
 * @property string|null $contact_person
 * @property string|null $contact_phone
 * @property string|null $website
 * @property string|null $bank_name
 * @property string|null $bank_account
 * @property string|null $bank_account_name
 * @property float|null $debt_amount
 * @property int|null $payment_term
 * @property float|null $credit_limit
 * @property int $status
 * @property int|null $province_id
 * @property int|null $district_id
 * @property int|null $ward_id
 * @property string $created_at
 * @property string $updated_at
 * @property int|null $created_by
 *
 * @property User $createdBy
 * @property District $district
 * @property Province $province
 * @property StockIn[] $stockIns
 * @property SupplierDebt[] $supplierDebts
 * @property SupplierProduct[] $supplierProducts
 * @property Product[] $products
 * @property Ward $ward
 */
class Supplier extends ActiveRecord
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'supplier';
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
            [['payment_term', 'status', 'province_id', 'district_id', 'ward_id', 'created_by'], 'integer'],
            [['debt_amount', 'credit_limit'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['code', 'tax_code', 'bank_account'], 'string', 'max' => 50],
            [['name', 'email', 'contact_person', 'website', 'bank_name', 'bank_account_name'], 'string', 'max' => 255],
            [['phone', 'contact_phone'], 'string', 'max' => 20],
            [['address'], 'string', 'max' => 500],
            [['code'], 'unique'],
            [['email'], 'email'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
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
            'code' => 'Mã nhà cung cấp',
            'name' => 'Tên nhà cung cấp',
            'phone' => 'Điện thoại',
            'email' => 'Email',
            'address' => 'Địa chỉ',
            'tax_code' => 'Mã số thuế',
            'contact_person' => 'Người liên hệ',
            'contact_phone' => 'Điện thoại liên hệ',
            'website' => 'Website',
            'bank_name' => 'Tên ngân hàng',
            'bank_account' => 'Số tài khoản',
            'bank_account_name' => 'Tên tài khoản',
            'debt_amount' => 'Dư nợ',
            'payment_term' => 'Thời hạn thanh toán (ngày)',
            'credit_limit' => 'Hạn mức nợ',
            'status' => 'Trạng thái',
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
     * Gets query for [[District]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDistrict()
    {
        return $this->hasOne(District::class, ['id' => 'district_id']);
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
     * Gets query for [[StockIns]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStockIns()
    {
        return $this->hasMany(StockIn::class, ['supplier_id' => 'id']);
    }

    /**
     * Gets query for [[SupplierDebts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSupplierDebts()
    {
        return $this->hasMany(SupplierDebt::class, ['supplier_id' => 'id']);
    }

    /**
     * Gets query for [[SupplierProducts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSupplierProducts()
    {
        return $this->hasMany(SupplierProduct::class, ['supplier_id' => 'id']);
    }

    /**
     * Gets query for [[Products]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Product::class, ['id' => 'product_id'])->via('supplierProducts');
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
            self::STATUS_INACTIVE => 'Không hoạt động',
            self::STATUS_ACTIVE => 'Hoạt động',
        ];
    }

    /**
     * Generate supplier code
     * 
     * @return string
     */
    public static function generateCode()
    {
        $prefix = 'NCC';
        $year = date('y');
        $month = date('m');
        
        $latestSupplier = self::find()
            ->where(['LIKE', 'code', $prefix . $year . $month])
            ->orderBy(['id' => SORT_DESC])
            ->one();
            
        $sequence = '001';
        if ($latestSupplier) {
            $parts = explode($prefix . $year . $month, $latestSupplier->code);
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
     * Get dropdown list
     * 
     * @param bool $onlyActive
     * @return array
     */
    public static function getDropdownList($onlyActive = true)
    {
        $query = self::find()->orderBy(['name' => SORT_ASC]);
        
        if ($onlyActive) {
            $query->andWhere(['status' => self::STATUS_ACTIVE]);
        }
        
        return \yii\helpers\ArrayHelper::map($query->all(), 'id', 'name');
    }
}