<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Order;

/**
 * OrderSearch represents the model behind the search form of `common\models\Order`.
 */
class OrderSearch extends Order
{
    /**
     * @var string Từ ngày (format Y-m-d)
     */
    public $from_date;

    /**
     * @var string Đến ngày (format Y-m-d)
     */
    public $to_date;
    
    /**
     * @var string Tên khách hàng
     */
    public $customer_name;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'customer_id', 'user_id', 'shift_id', 'warehouse_id', 'total_quantity', 'points_earned', 'points_used', 'payment_method_id', 'payment_status', 'shipping_status', 'status'], 'integer'],
            [['code', 'order_date', 'shipping_address', 'delivery_date', 'note', 'created_at', 'updated_at', 'from_date', 'to_date', 'customer_name'], 'safe'],
            [['subtotal', 'discount_amount', 'tax_amount', 'total_amount', 'paid_amount', 'change_amount', 'points_amount', 'shipping_fee'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Order::find();
        $query->joinWith(['customer']);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'order_date' => SORT_DESC,
                ]
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'order.id' => $this->id,
            'order.customer_id' => $this->customer_id,
            'order.user_id' => $this->user_id,
            'order.shift_id' => $this->shift_id,
            'order.warehouse_id' => $this->warehouse_id,
            'order.total_quantity' => $this->total_quantity,
            'order.subtotal' => $this->subtotal,
            'order.discount_amount' => $this->discount_amount,
            'order.tax_amount' => $this->tax_amount,
            'order.total_amount' => $this->total_amount,
            'order.paid_amount' => $this->paid_amount,
            'order.change_amount' => $this->change_amount,
            'order.points_earned' => $this->points_earned,
            'order.points_used' => $this->points_used,
            'order.points_amount' => $this->points_amount,
            'order.payment_method_id' => $this->payment_method_id,
            'order.payment_status' => $this->payment_status,
            'order.shipping_fee' => $this->shipping_fee,
            'order.shipping_status' => $this->shipping_status,
            'order.delivery_date' => $this->delivery_date,
            'order.status' => $this->status,
        ]);

        // Lọc theo ngày đặt hàng
        if (!empty($this->from_date)) {
            $query->andFilterWhere(['>=', 'DATE(order.order_date)', $this->from_date]);
        }

        if (!empty($this->to_date)) {
            $query->andFilterWhere(['<=', 'DATE(order.order_date)', $this->to_date]);
        }
        
        // Lọc theo mã đơn hàng với LIKE
        $query->andFilterWhere(['like', 'order.code', $this->code]);
        
        // Lọc theo tên khách hàng
        if (!empty($this->customer_name)) {
            $query->andFilterWhere(['like', 'customer.name', $this->customer_name]);
        }

        $query->andFilterWhere(['like', 'order.shipping_address', $this->shipping_address])
            ->andFilterWhere(['like', 'order.note', $this->note]);

        return $dataProvider;
    }
}