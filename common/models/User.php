<?php

namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\helpers\ArrayHelper;

/**
 * User model
 *
 * @property int $id
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 * @property string $verification_token
 * @property string $full_name
 * @property string $phone
 * @property string $avatar
 * @property int $warehouse_id
 * @property string $last_login_at
 *
 * @property LoginHistory[] $loginHistories
 * @property Order[] $orders
 * @property Return[] $returns
 * @property Shift[] $shifts
 * @property Shift[] $cashierShifts
 * @property StockCheck[] $stockChecks
 * @property StockIn[] $stockIns
 * @property StockOut[] $stockOuts
 * @property StockTransfer[] $stockTransfers
 * @property UserProfile $userProfile
 * @property UserWarehouse[] $userWarehouses
 * @property Warehouse[] $warehouses
 * @property Warehouse $warehouse
 * @property Warehouse[] $managedWarehouses
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_INACTIVE = 9;
    const STATUS_ACTIVE = 10;

    /**
     * @var string Mật khẩu mới
     */
    public $password;
    
    /**
     * @var array Vai trò người dùng
     */
    private $_roles;
    
    /**
     * @var array Kho được phép truy cập
     */
    private $_warehouses;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_DELETED]],
            [['username', 'email', 'full_name'], 'required'],
            [['status', 'created_at', 'updated_at', 'warehouse_id'], 'integer'],
            [['last_login_at'], 'safe'],
            [['username', 'password_hash', 'password_reset_token', 'email', 'verification_token', 'full_name', 'avatar'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            [['phone'], 'string', 'max' => 20],
            [['username'], 'unique'],
            [['email'], 'unique'],
            [['password_reset_token'], 'unique'],
            [['warehouse_id'], 'exist', 'skipOnError' => true, 'targetClass' => Warehouse::class, 'targetAttribute' => ['warehouse_id' => 'id']],
            // Thêm các trường an toàn cho form input
            [['password', 'roles', 'warehouses'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Tên đăng nhập',
            'auth_key' => 'Auth Key',
            'password_hash' => 'Password Hash',
            'password_reset_token' => 'Password Reset Token',
            'email' => 'Email',
            'status' => 'Trạng thái',
            'created_at' => 'Ngày tạo',
            'updated_at' => 'Ngày cập nhật',
            'verification_token' => 'Verification Token',
            'full_name' => 'Họ tên',
            'phone' => 'Số điện thoại',
            'avatar' => 'Ảnh đại diện',
            'warehouse_id' => 'Kho mặc định',
            'last_login_at' => 'Lần đăng nhập cuối',
            'password' => 'Mật khẩu',
            'roles' => 'Vai trò người dùng',
            'warehouses' => 'Kho được phép truy cập',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds user by verification email token
     *
     * @param string $token verify email token
     * @return static|null
     */
    public static function findByVerificationToken($token)
    {
        return static::findOne([
            'verification_token' => $token,
            'status' => self::STATUS_INACTIVE
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }


    /**
     * Lấy danh sách người dùng dưới dạng mảng key-value (id => full_name)
     * @param bool $onlyActive Chỉ lấy người dùng hoạt động
     * @return array
     */
    public static function getList($onlyActive = true)
    {
        $query = self::find();
        
        if ($onlyActive) {
            $query->where(['status' => 10]); // Status 10 thường là status active
        }
        
        $users = $query->orderBy(['full_name' => SORT_ASC])->all();
        
        return ArrayHelper::map($users, 'id', 'full_name');
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Generates new token for email verification
     */
    public function generateEmailVerificationToken()
    {
        $this->verification_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * Get user roles
     * @return string[]
     */
    public function getRoles()
    {
        if ($this->_roles === null) {
            $this->_roles = [];
            if (!$this->isNewRecord) {
                $authManager = Yii::$app->authManager;
                $roles = $authManager->getRolesByUser($this->id);
                foreach ($roles as $role) {
                    $this->_roles[] = $role->name;
                }
            }
        }
        return $this->_roles;
    }
    
    /**
     * Set user roles
     * @param array $roles
     */
    public function setRoles($roles)
    {
        $this->_roles = $roles;
    }
    
    /**
     * Get warehouses assigned to user
     * @return array
     */
    public function getWarehouses()
    {
        if ($this->_warehouses === null) {
            $this->_warehouses = [];
            if (!$this->isNewRecord) {
                $userWarehouses = UserWarehouse::find()->where(['user_id' => $this->id])->all();
                foreach ($userWarehouses as $userWarehouse) {
                    $this->_warehouses[] = $userWarehouse->warehouse_id;
                }
            }
        }
        return $this->_warehouses;
    }
    
    /**
     * Set warehouses assigned to user
     * @param array $warehouses
     */
    public function setWarehouses($warehouses)
    {
        $this->_warehouses = $warehouses;
    }
    
    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        
        // Xử lý phân quyền người dùng
        if ($this->_roles !== null) {
            $authManager = Yii::$app->authManager;
            $authManager->revokeAll($this->id);
            
            if (is_array($this->_roles)) {
                foreach ($this->_roles as $roleName) {
                    $role = $authManager->getRole($roleName);
                    if ($role) {
                        $authManager->assign($role, $this->id);
                    }
                }
            }
        }
        
        // Xử lý kho được phép truy cập
        if ($this->_warehouses !== null) {
            UserWarehouse::deleteAll(['user_id' => $this->id]);
            
            if (is_array($this->_warehouses)) {
                foreach ($this->_warehouses as $warehouseId) {
                    $userWarehouse = new UserWarehouse([
                        'user_id' => $this->id,
                        'warehouse_id' => $warehouseId,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                    $userWarehouse->save();
                }
            }
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLoginHistories()
    {
        return $this->hasMany(LoginHistory::class, ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::class, ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReturns()
    {
        return $this->hasMany(ReturnModel::class, ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShifts()
    {
        return $this->hasMany(Shift::class, ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCashierShifts()
    {
        return $this->hasMany(Shift::class, ['cashier_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStockChecks()
    {
        return $this->hasMany(StockCheck::class, ['created_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStockIns()
    {
        return $this->hasMany(StockIn::class, ['created_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStockOuts()
    {
        return $this->hasMany(StockOut::class, ['created_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStockTransfers()
    {
        return $this->hasMany(StockTransfer::class, ['created_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserProfile()
    {
        return $this->hasOne(UserProfile::class, ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserWarehouses()
    {
        return $this->hasMany(UserWarehouse::class, ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWarehousesRelation()
    {
        return $this->hasMany(Warehouse::class, ['id' => 'warehouse_id'])->via('userWarehouses');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWarehouse()
    {
        return $this->hasOne(Warehouse::class, ['id' => 'warehouse_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManagedWarehouses()
    {
        return $this->hasMany(Warehouse::class, ['manager_id' => 'id']);
    }
}