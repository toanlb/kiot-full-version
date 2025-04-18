<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "setting".
 *
 * @property int $id
 * @property string $category
 * @property string $key
 * @property string|null $value
 * @property string|null $description
 * @property int $is_public
 * @property string $updated_at
 * @property int|null $updated_by
 *
 * @property User $updatedBy
 */
class Setting extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'setting';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => false,
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => false,
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
            [['category', 'key'], 'required'],
            [['value', 'description'], 'string'],
            [['is_public', 'updated_by'], 'integer'],
            [['updated_at'], 'safe'],
            [['category'], 'string', 'max' => 64],
            [['key'], 'string', 'max' => 255],
            [['category', 'key'], 'unique', 'targetAttribute' => ['category', 'key']],
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
            'category' => 'Danh mục',
            'key' => 'Khóa',
            'value' => 'Giá trị',
            'description' => 'Mô tả',
            'is_public' => 'Công khai',
            'updated_at' => 'Ngày cập nhật',
            'updated_by' => 'Người cập nhật',
        ];
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
     * Get setting
     * 
     * @param string $category
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($category, $key, $default = null)
    {
        $setting = self::findOne(['category' => $category, 'key' => $key]);
        
        return $setting ? $setting->value : $default;
    }

    /**
     * Set setting
     * 
     * @param string $category
     * @param string $key
     * @param mixed $value
     * @param string|null $description
     * @param bool $isPublic
     * @return bool
     */
    public static function set($category, $key, $value, $description = null, $isPublic = true)
    {
        $setting = self::findOne(['category' => $category, 'key' => $key]);
        
        if (!$setting) {
            $setting = new self();
            $setting->category = $category;
            $setting->key = $key;
            $setting->is_public = $isPublic ? 1 : 0;
        }
        
        $setting->value = $value;
        
        if ($description !== null) {
            $setting->description = $description;
        }
        
        return $setting->save();
    }

    /**
     * Get settings by category
     * 
     * @param string $category
     * @param bool $asArray
     * @return array
     */
    public static function getByCategory($category, $asArray = true)
    {
        $query = self::find()->where(['category' => $category]);
        
        if ($asArray) {
            $settings = [];
            $records = $query->all();
            
            foreach ($records as $record) {
                $settings[$record->key] = $record->value;
            }
            
            return $settings;
        }
        
        return $query->all();
    }

    /**
     * Get public settings
     * 
     * @return array
     */
    public static function getPublicSettings()
    {
        $settings = [];
        $records = self::find()->where(['is_public' => 1])->all();
        
        foreach ($records as $record) {
            if (!isset($settings[$record->category])) {
                $settings[$record->category] = [];
            }
            
            $settings[$record->category][$record->key] = $record->value;
        }
        
        return $settings;
    }

    /**
     * Delete setting
     * 
     * @param string $category
     * @param string $key
     * @return bool
     */
    public static function remove($category, $key)
    {
        $setting = self::findOne(['category' => $category, 'key' => $key]);
        
        if ($setting) {
            return $setting->delete();
        }
        
        return false;
    }
}