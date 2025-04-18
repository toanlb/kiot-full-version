<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "log".
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $action
 * @property string|null $model
 * @property int|null $model_id
 * @property string|null $old_data
 * @property string|null $new_data
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string $created_at
 *
 * @property User $user
 */
class Log extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'log';
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
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['action'], 'required'],
            [['user_id', 'model_id'], 'integer'],
            [['old_data', 'new_data'], 'string'],
            [['created_at'], 'safe'],
            [['action', 'model', 'user_agent'], 'string', 'max' => 255],
            [['ip_address'], 'string', 'max' => 50],
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
            'action' => 'Hành động',
            'model' => 'Model',
            'model_id' => 'ID Model',
            'old_data' => 'Dữ liệu cũ',
            'new_data' => 'Dữ liệu mới',
            'ip_address' => 'Địa chỉ IP',
            'user_agent' => 'User Agent',
            'created_at' => 'Ngày tạo',
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
     * Add log
     * 
     * @param string $action
     * @param string|null $model
     * @param int|null $modelId
     * @param mixed|null $oldData
     * @param mixed|null $newData
     * @return bool
     */
    public static function add($action, $model = null, $modelId = null, $oldData = null, $newData = null)
    {
        $log = new self();
        $log->user_id = Yii::$app->user->isGuest ? null : Yii::$app->user->id;
        $log->action = $action;
        $log->model = $model;
        $log->model_id = $modelId;
        $log->old_data = $oldData ? json_encode($oldData) : null;
        $log->new_data = $newData ? json_encode($newData) : null;
        $log->ip_address = Yii::$app->request->userIP;
        $log->user_agent = Yii::$app->request->userAgent;
        
        return $log->save();
    }

    /**
     * Get formatted data
     * 
     * @param string $data
     * @return mixed
     */
    public function getFormattedData($data)
    {
        if (!$data) {
            return null;
        }
        
        return json_decode($data, true);
    }

    /**
     * Get old data formatted
     * 
     * @return mixed
     */
    public function getOldDataFormatted()
    {
        return $this->getFormattedData($this->old_data);
    }

    /**
     * Get new data formatted
     * 
     * @return mixed
     */
    public function getNewDataFormatted()
    {
        return $this->getFormattedData($this->new_data);
    }

    /**
     * Get log history for model
     * 
     * @param string $model
     * @param int $modelId
     * @param int $limit
     * @return array
     */
    public static function getHistory($model, $modelId, $limit = 10)
    {
        return self::find()
            ->where(['model' => $model, 'model_id' => $modelId])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit($limit)
            ->all();
    }
}