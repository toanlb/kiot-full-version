<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "user_profile".
 *
 * @property int $user_id
 * @property string|null $address
 * @property string|null $city
 * @property string|null $country
 * @property string|null $birthday
 * @property string|null $position
 * @property string|null $department
 * @property string|null $hire_date
 * @property string|null $identity_card
 * @property string|null $notes
 *
 * @property User $user
 */
class UserProfile extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_profile';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id'], 'integer'],
            [['birthday', 'hire_date'], 'safe'],
            [['notes'], 'string'],
            [['address'], 'string', 'max' => 255],
            [['city', 'country', 'position', 'department'], 'string', 'max' => 100],
            [['identity_card'], 'string', 'max' => 20],
            [['user_id'], 'unique'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'Người dùng',
            'address' => 'Địa chỉ',
            'city' => 'Thành phố',
            'country' => 'Quốc gia',
            'birthday' => 'Ngày sinh',
            'position' => 'Chức vụ',
            'department' => 'Phòng ban',
            'hire_date' => 'Ngày vào làm',
            'identity_card' => 'CMND/CCCD',
            'notes' => 'Ghi chú',
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
}