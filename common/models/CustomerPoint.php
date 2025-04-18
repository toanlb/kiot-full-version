<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "customer_point".
 *
 * @property int $id
 * @property int $customer_id
 * @property int $points
 * @property int $total_points_earned
 * @property int $total_points_used
 * @property string $updated_at
 *
 * @property Customer $customer
 */
class CustomerPoint extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'customer_point';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['customer_id'], 'required'],
            [['customer_id', 'points', 'total_points_earned', 'total_points_used'], 'integer'],
            [['updated_at'], 'safe'],
            [['customer_id'], 'unique'],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customer::class, 'targetAttribute' => ['customer_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'customer_id' => 'Khách hàng',
            'points' => 'Điểm',
            'total_points_earned' => 'Tổng điểm đã tích lũy',
            'total_points_used' => 'Tổng điểm đã sử dụng',
            'updated_at' => 'Ngày cập nhật',
        ];
    }

    /**
     * Gets query for [[Customer]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['id' => 'customer_id']);
    }

    /**
     * Add points
     * 
     * @param int $customerId
     * @param int $points
     * @param int $referenceId
     * @param string $referenceType
     * @param string $note
     * @return bool
     */
    public static function addPoints($customerId, $points, $referenceId = null, $referenceType = null, $note = '')
    {
        if ($points <= 0) {
            return false;
        }
        
        $point = self::findOne(['customer_id' => $customerId]);
        
        if (!$point) {
            $point = new self();
            $point->customer_id = $customerId;
            $point->points = 0;
            $point->total_points_earned = 0;
            $point->total_points_used = 0;
        }
        
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Update points
            $point->points += $points;
            $point->total_points_earned += $points;
            $point->updated_at = date('Y-m-d H:i:s');
            
            if (!$point->save()) {
                throw new \Exception('Không thể cập nhật điểm thưởng');
            }
            
            // Record history
            $history = new CustomerPointHistory();
            $history->customer_id = $customerId;
            $history->reference_id = $referenceId;
            $history->reference_type = $referenceType;
            $history->points = $points;
            $history->balance = $point->points;
            $history->type = CustomerPointHistory::TYPE_ADD;
            $history->note = $note;
            
            if (!$history->save()) {
                throw new \Exception('Không thể lưu lịch sử điểm thưởng');
            }
            
            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage());
            return false;
        }
    }

    /**
     * Use points
     * 
     * @param int $customerId
     * @param int $points
     * @param int $referenceId
     * @param string $referenceType
     * @param string $note
     * @return bool
     */
    public static function usePoints($customerId, $points, $referenceId = null, $referenceType = null, $note = '')
    {
        if ($points <= 0) {
            return false;
        }
        
        $point = self::findOne(['customer_id' => $customerId]);
        
        if (!$point || $point->points < $points) {
            return false;
        }
        
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Update points
            $point->points -= $points;
            $point->total_points_used += $points;
            $point->updated_at = date('Y-m-d H:i:s');
            
            if (!$point->save()) {
                throw new \Exception('Không thể cập nhật điểm thưởng');
            }
            
            // Record history
            $history = new CustomerPointHistory();
            $history->customer_id = $customerId;
            $history->reference_id = $referenceId;
            $history->reference_type = $referenceType;
            $history->points = $points;
            $history->balance = $point->points;
            $history->type = CustomerPointHistory::TYPE_DEDUCT;
            $history->note = $note;
            
            if (!$history->save()) {
                throw new \Exception('Không thể lưu lịch sử điểm thưởng');
            }
            
            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage());
            return false;
        }
    }

    /**
     * Calculate point value
     * 
     * @param int $points
     * @return float
     */
    public static function calculatePointValue($points)
    {
        // Get exchange rate from settings
        $exchangeRate = 1000; // Default 1 point = 1000 VND
        
        $setting = Setting::findOne(['category' => 'point', 'key' => 'exchange_rate']);
        if ($setting) {
            $exchangeRate = (float)$setting->value;
        }
        
        return $points * $exchangeRate;
    }
}