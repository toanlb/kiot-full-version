<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\WarrantyDetail;

/**
 * WarrantyDetailSearch represents the model behind the search form of `common\models\WarrantyDetail`.
 */
class WarrantyDetailSearch extends WarrantyDetail
{
    /**
     * @var string
     */
    public $warranty_code;
    
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
    public $handled_by_name;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'warranty_id', 'status_id', 'replacement_product_id', 'handled_by'], 'integer'],
            [['service_date', 'description', 'diagnosis', 'solution', 'created_at', 'warranty_code', 'product_name', 'status_name', 'handled_by_name'], 'safe'],
            [['replacement_cost', 'service_cost', 'total_cost'], 'number'],
            [['is_charged'], 'boolean'],
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
        $query = WarrantyDetail::find();
        
        // Add joins for related tables
        $query->joinWith(['warranty', 'status', 'handler', 'warranty.product']);

        // Define data provider
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'service_date' => SORT_DESC,
                ],
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
            'warranty_detail.id' => $this->id,
            'warranty_detail.warranty_id' => $this->warranty_id,
            'warranty_detail.status_id' => $this->status_id,
            'warranty_detail.replacement_product_id' => $this->replacement_product_id,
            'warranty_detail.is_charged' => $this->is_charged,
            'warranty_detail.handled_by' => $this->handled_by,
        ]);

        // Handle service date filtering
        if (!empty($this->service_date)) {
            $query->andFilterWhere(['>=', 'warranty_detail.service_date', date('Y-m-d 00:00:00', strtotime($this->service_date))])
                  ->andFilterWhere(['<=', 'warranty_detail.service_date', date('Y-m-d 23:59:59', strtotime($this->service_date))]);
        }

        // Cost filtering
        if (!empty($this->replacement_cost)) {
            $query->andFilterWhere(['=', 'warranty_detail.replacement_cost', $this->replacement_cost]);
        }
        if (!empty($this->service_cost)) {
            $query->andFilterWhere(['=', 'warranty_detail.service_cost', $this->service_cost]);
        }
        if (!empty($this->total_cost)) {
            $query->andFilterWhere(['=', 'warranty_detail.total_cost', $this->total_cost]);
        }

        // Text filtering conditions
        $query->andFilterWhere(['like', 'warranty_detail.description', $this->description])
              ->andFilterWhere(['like', 'warranty_detail.diagnosis', $this->diagnosis])
              ->andFilterWhere(['like', 'warranty_detail.solution', $this->solution])
              ->andFilterWhere(['like', 'warranty.code', $this->warranty_code])
              ->andFilterWhere(['like', 'product.name', $this->product_name])
              ->andFilterWhere(['like', 'warranty_status.name', $this->status_name])
              ->andFilterWhere(['like', 'user.full_name', $this->handled_by_name]);

        return $dataProvider;
    }
}