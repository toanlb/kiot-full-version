<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "warranty_status".
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string|null $color
 * @property int|null $sort_order
 *
 * @property Warranty[] $warranties
 * @property WarrantyDetail[] $warrantyDetails
 */
class WarrantyStatus extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'warranty_status';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['description'], 'string'],
            [['sort_order'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['color'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Tên trạng thái',
            'description' => 'Mô tả',
            'color' => 'Màu sắc',
            'sort_order' => 'Thứ tự',
        ];
    }

    /**
     * Gets query for [[Warranties]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWarranties()
    {
        return $this->hasMany(Warranty::class, ['status_id' => 'id']);
    }

    /**
     * Gets query for [[WarrantyDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWarrantyDetails()
    {
        return $this->hasMany(WarrantyDetail::class, ['status_id' => 'id']);
    }

    /**
     * Get dropdown list
     * 
     * @return array
     */
    public static function getDropdownList()
    {
        return \yii\helpers\ArrayHelper::map(
            self::find()->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])->all(),
            'id',
            'name'
        );
    }

    /**
     * Get color HTML
     * 
     * @return string
     */
    public function getColorHtml()
    {
        if ($this->color) {
            return '<span class="label" style="background-color: ' . $this->color . '">' . $this->name . '</span>';
        }
        
        return $this->name;
    }
}