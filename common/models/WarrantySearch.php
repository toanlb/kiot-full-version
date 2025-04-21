<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Warranty;
use yii\helpers\ArrayHelper;

/**
 * WarrantySearch represents the model behind the search form of `common\models\Warranty`.
 */
class WarrantySearch extends Warranty
{
    /**
     * @var string
     */
    public $customer_name;
    
    /**
     * @var string
     */
    public $product_name;
    
    /**
     * @var string
     */
    public $status_name;
    
    /**
     * @var string
     */
    public $date_range;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'order_id', 'order_detail_id', 'product_id', 'customer_id', 'status_id', 'created_by'], 'integer'],
            [['code', 'serial_number', 'start_date', 'end_date', 'note', 'created_at', 'updated_at', 'customer_name', 'product_name', 'status_name', 'date_range'], 'safe'],
            [['active'], 'boolean'],
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
        $query = Warranty::find();

        // Add joins for related tables
        $query->joinWith(['customer', 'product', 'status']);

        // Define data provider
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ],
                'attributes' => ArrayHelper::merge(
                    // Add related fields for sorting
                    [
                        'customer_name' => [
                            'asc' => ['customer.name' => SORT_ASC],
                            'desc' => ['customer.name' => SORT_DESC],
                        ],
                        'product_name' => [
                            'asc' => ['product.name' => SORT_ASC],
                            'desc' => ['product.name' => SORT_DESC],
                        ],
                        'status_name' => [
                            'asc' => ['warranty_status.name' => SORT_ASC],
                            'desc' => ['warranty_status.name' => SORT_DESC],
                        ],
                    ],
                    // Add default sort attributes
                    $this->attributes()
                ),
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // If validation fails, still show all data but don't filter
            return $dataProvider;
        }

        // Grid filtering conditions
        $query->andFilterWhere([
            'warranty.id' => $this->id,
            'warranty.order_id' => $this->order_id,
            'warranty.order_detail_id' => $this->order_detail_id,
            'warranty.product_id' => $this->product_id,
            'warranty.customer_id' => $this->customer_id,
            'warranty.status_id' => $this->status_id,
            'warranty.active' => $this->active,
            'warranty.created_by' => $this->created_by,
        ]);

        // Handle date range filtering if provided
        if (!empty($this->date_range) && strpos($this->date_range, ' - ') !== false) {
            list($start_date, $end_date) = explode(' - ', $this->date_range);
            $start_date = date('Y-m-d', strtotime($start_date));
            $end_date = date('Y-m-d', strtotime($end_date));
            $query->andFilterWhere(['>=', 'warranty.start_date', $start_date])
                  ->andFilterWhere(['<=', 'warranty.end_date', $end_date]);
        } else {
            // Individual date filtering if no range is provided
            if (!empty($this->start_date)) {
                $query->andFilterWhere(['>=', 'warranty.start_date', date('Y-m-d', strtotime($this->start_date))]);
            }
            if (!empty($this->end_date)) {
                $query->andFilterWhere(['<=', 'warranty.end_date', date('Y-m-d', strtotime($this->end_date))]);
            }
        }

        // Text filtering conditions
        $query->andFilterWhere(['like', 'warranty.code', $this->code])
            ->andFilterWhere(['like', 'warranty.serial_number', $this->serial_number])
            ->andFilterWhere(['like', 'warranty.note', $this->note])
            ->andFilterWhere(['like', 'customer.name', $this->customer_name])
            ->andFilterWhere(['like', 'product.name', $this->product_name])
            ->andFilterWhere(['like', 'warranty_status.name', $this->status_name]);

        return $dataProvider;
    }
}