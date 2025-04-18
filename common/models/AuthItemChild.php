<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "auth_item_child".
 *
 * @property string $parent
 * @property string $child
 *
 * @property AuthItem $child0
 * @property AuthItem $parent0
 */
class AuthItemChild extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'auth_item_child';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['parent', 'child'], 'required'],
            [['parent', 'child'], 'string', 'max' => 64],
            [['parent', 'child'], 'unique', 'targetAttribute' => ['parent', 'child']],
            [['parent'], 'exist', 'skipOnError' => true, 'targetClass' => AuthItem::class, 'targetAttribute' => ['parent' => 'name']],
            [['child'], 'exist', 'skipOnError' => true, 'targetClass' => AuthItem::class, 'targetAttribute' => ['child' => 'name']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'parent' => 'Quyền cha',
            'child' => 'Quyền con',
        ];
    }

    /**
     * Gets query for [[Child0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChild0()
    {
        return $this->hasOne(AuthItem::class, ['name' => 'child']);
    }

    /**
     * Gets query for [[Parent0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getParent0()
    {
        return $this->hasOne(AuthItem::class, ['name' => 'parent']);
    }

    /**
     * Get child permissions by parent
     * 
     * @param string $parentName
     * @return array
     */
    public static function getChildrenByParent($parentName)
    {
        return self::find()
            ->where(['parent' => $parentName])
            ->all();
    }

    /**
     * Check if child exists
     * 
     * @param string $parentName
     * @param string $childName
     * @return bool
     */
    public static function hasChild($parentName, $childName)
    {
        return self::find()
            ->where(['parent' => $parentName, 'child' => $childName])
            ->exists();
    }

    /**
     * Add child
     * 
     * @param string $parentName
     * @param string $childName
     * @return bool
     */
    public static function addChild($parentName, $childName)
    {
        if (self::hasChild($parentName, $childName)) {
            return true;
        }
        
        $model = new self();
        $model->parent = $parentName;
        $model->child = $childName;
        
        return $model->save();
    }

    /**
     * Remove child
     * 
     * @param string $parentName
     * @param string $childName
     * @return bool
     */
    public static function removeChild($parentName, $childName)
    {
        $model = self::findOne(['parent' => $parentName, 'child' => $childName]);
        
        if ($model) {
            return $model->delete();
        }
        
        return true;
    }
}