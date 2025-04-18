<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "login_history".
 *
 * @property int $id
 * @property int $user_id
 * @property string $login_time
 * @property string|null $logout_time
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property int $success
 * @property string|null $failure_reason
 *
 * @property User $user
 */
class LoginHistory extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'login_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'login_time'], 'required'],
            [['user_id', 'success'], 'integer'],
            [['login_time', 'logout_time'], 'safe'],
            [['failure_reason'], 'string'],
            [['ip_address'], 'string', 'max' => 50],
            [['user_agent'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'Người dùng',
            'login_time' => 'Thời gian đăng nhập',
            'logout_time' => 'Thời gian đăng xuất',
            'ip_address' => 'Địa chỉ IP',
            'user_agent' => 'Trình duyệt',
            'success' => 'Thành công',
            'failure_reason' => 'Lý do thất bại',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Log a successful login
     * 
     * @param int $userId
     * @return bool
     */
    public static function logSuccessfulLogin($userId)
    {
        $model = new self();
        $model->user_id = $userId;
        $model->login_time = new Expression('NOW()');
        $model->ip_address = Yii::$app->request->userIP;
        $model->user_agent = Yii::$app->request->userAgent;
        $model->success = 1;
        
        return $model->save();
    }

    /**
     * Log a failed login
     * 
     * @param int $userId
     * @param string $reason
     * @return bool
     */
    public static function logFailedLogin($userId, $reason)
    {
        $model = new self();
        $model->user_id = $userId;
        $model->login_time = new Expression('NOW()');
        $model->ip_address = Yii::$app->request->userIP;
        $model->user_agent = Yii::$app->request->userAgent;
        $model->success = 0;
        $model->failure_reason = $reason;
        
        return $model->save();
    }

    /**
     * Log logout
     * 
     * @param int $userId
     * @return bool
     */
    public static function logLogout($userId)
    {
        $model = self::find()
            ->where(['user_id' => $userId])
            ->andWhere(['IS', 'logout_time', null])
            ->orderBy(['login_time' => SORT_DESC])
            ->one();
            
        if ($model) {
            $model->logout_time = new Expression('NOW()');
            return $model->save();
        }
        
        return false;
    }
}