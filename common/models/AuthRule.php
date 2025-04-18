<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "auth_rule".
 *
 * @property string $name
 * @property resource|null $data
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property AuthItem[] $authItems
 */
class AuthRule extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'auth_rule';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['data'], 'string'],
            [['created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 64],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Tên',
            'data' => 'Dữ liệu',
            'created_at' => 'Ngày tạo',
            'updated_at' => 'Ngày cập nhật',
        ];
    }

    /**
     * Gets query for [[AuthItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAuthItems()
    {
        return $this->hasMany(AuthItem::class, ['rule_name' => 'name']);
    }

    /**
     * Get all rules
     * 
     * @return array
     */
    public static function getAllRules()
    {
        return self::find()->all();
    }

    /**
     * Create a rule
     * 
     * @param string $name
     * @param \yii\rbac\Rule $ruleObject
     * @return bool
     */
    public static function createRule($name, $ruleObject)
    {
        if (!$ruleObject instanceof \yii\rbac\Rule) {
            return false;
        }
        
        $auth = Yii::$app->authManager;
        
        try {
            $auth->add($ruleObject);
            return true;
        } catch (\Exception $e) {
            Yii::error($e->getMessage());
            return false;
        }
    }

    /**
     * Update a rule
     * 
     * @param string $name
     * @param \yii\rbac\Rule $ruleObject
     * @return bool
     */
    public static function updateRule($name, $ruleObject)
    {
        if (!$ruleObject instanceof \yii\rbac\Rule) {
            return false;
        }
        
        $auth = Yii::$app->authManager;
        $rule = $auth->getRule($name);
        
        if (!$rule) {
            return false;
        }
        
        try {
            $auth->update($name, $ruleObject);
            return true;
        } catch (\Exception $e) {
            Yii::error($e->getMessage());
            return false;
        }
    }

    /**
     * Remove a rule
     * 
     * @param string $name
     * @return bool
     */
    public static function removeRule($name)
    {
        $auth = Yii::$app->authManager;
        $rule = $auth->getRule($name);
        
        if (!$rule) {
            return false;
        }
        
        try {
            $auth->remove($rule);
            return true;
        } catch (\Exception $e) {
            Yii::error($e->getMessage());
            return false;
        }
    }
}