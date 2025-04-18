<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "warranty_detail".
 *
 * @property int $id
 * @property int $warranty_id
 * @property string $service_date
 * @property int $status_id
 * @property string|null $description
 * @property string|null $diagnosis
 * @property string|null $solution
 * @property int|null $replacement_product_id
 * @property float|null $replacement_cost
 * @property float|null $service_cost
 * @property float|null $total_cost
 * @property int $is_charged
 * @property int|null $handled_by
 * @property string $created_at
 *
 * @property User $handledBy
 * @property Product $replacementProduct
 * @property WarrantyStatus $status
 * @property Warranty $warranty
 */
class WarrantyDetail extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'warranty_detail';
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
            [['warranty_id', 'service_date', 'status_id'], 'required'],
            [['warranty_id', 'status_id', 'replacement_product_id', 'is_charged', 'handled_by'], 'integer'],
            [['service_date', 'created_at'], 'safe'],
            [['description', 'diagnosis', 'solution'], 'string'],
            [['replacement_cost', 'service_cost', 'total_cost'], 'number'],
            [['handled_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['handled_by' => 'id']],
            [['replacement_product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['replacement_product_id' => 'id']],
            [['status_id'], 'exist', 'skipOnError' => true, 'targetClass' => WarrantyStatus::class, 'targetAttribute' => ['status_id' => 'id']],
            [['warranty_id'], 'exist', 'skipOnError' => true, 'targetClass' => Warranty::class, 'targetAttribute' => ['warranty_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'warranty_id' => 'Bảo hành',
            'service_date' => 'Ngày bảo hành',
            'status_id' => 'Trạng thái',
            'description' => 'Mô tả',
            'diagnosis' => 'Chẩn đoán',
            'solution' => 'Giải pháp',
            'replacement_product_id' => 'Sản phẩm thay thế',
            'replacement_cost' => 'Chi phí thay thế',
            'service_cost' => 'Chi phí dịch vụ',
            'total_cost' => 'Tổng chi phí',
            'is_charged' => 'Có tính phí',
            'handled_by' => 'Người xử lý',
            'created_at' => 'Ngày tạo',
        ];
    }

    /**
     * Gets query for [[HandledBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHandledBy()
    {
        return $this->hasOne(User::class, ['id' => 'handled_by']);
    }

    /**
     * Gets query for [[ReplacementProduct]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReplacementProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'replacement_product_id']);
    }

    /**
     * Gets query for [[Status]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStatus()
    {
        return $this->hasOne(WarrantyStatus::class, ['id' => 'status_id']);
    }

    /**
     * Gets query for [[Warranty]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWarranty()
    {
        return $this->hasOne(Warranty::class, ['id' => 'warranty_id']);
    }

    /**
     * Calculate total cost
     */
    public function calculateTotalCost()
    {
        $this->total_cost = ($this->replacement_cost ?: 0) + ($this->service_cost ?: 0);
    }

    /**
     * Before save
     * 
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Calculate total cost
            $this->calculateTotalCost();
            
            return true;
        }
        
        return false;
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
        
        // Update warranty status
        $this->updateWarrantyStatus();
    }

    /**
     * Update warranty status
     */
    protected function updateWarrantyStatus()
    {
        $warranty = $this->warranty;
        
        if ($warranty) {
            $warranty->status_id = $this->status_id;
            $warranty->save(false);
        }
    }
}