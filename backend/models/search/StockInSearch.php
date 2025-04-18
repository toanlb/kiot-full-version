<?php

namespace backend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\StockIn;

/**
 * StockInSearch represents the model behind the search form of `common\models\StockIn`.
 */
class StockInSearch extends StockIn
{
    public $warehouse_name;
    public $supplier_name;
    public $created_by_name;
    public $approved_by_name;
    public $date_range;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'warehouse_id', 'supplier_id', 'payment_status', 'status', 'created_by', 'approved_by'], 'integer'],
            [['code', 'stock_in_date', 'reference_number', 'note', 'created_at', 'updated_at', 'approved_at', 'warehouse_name', 'supplier_name', 'created_by_name', 'approved_by_name', 'date_range'], 'safe'],
            [['total_amount', 'discount_amount', 'tax_amount', 'final_amount', 'paid_amount'], 'number'],
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
        $query = StockIn::find()
            ->joinWith(['warehouse', 'supplier', 'createdBy', 'approvedBy']);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
        ]);

        $dataProvider->sort->attributes['warehouse_name'] = [
            'asc' => ['warehouse.name' => SORT_ASC],
            'desc' => ['warehouse.name' => SORT_DESC],
        ];

        $dataProvider->sort->attributes['supplier_name'] = [
            'asc' => ['supplier.name' => SORT_ASC],
            'desc' => ['supplier.name' => SORT_DESC],
        ];

        $dataProvider->sort->attributes['created_by_name'] = [
            'asc' => ['user.full_name' => SORT_ASC],
            'desc' => ['user.full_name' => SORT_DESC],
        ];

        $dataProvider->sort->attributes['approved_by_name'] = [
            'asc' => ['approvedBy.full_name' => SORT_ASC],
            'desc' => ['approvedBy.full_name' => SORT_DESC],
        ];

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'stock_in.id' => $this->id,
            'stock_in.warehouse_id' => $this->warehouse_id,
            'stock_in.supplier_id' => $this->supplier_id,
            'stock_in.payment_status' => $this->payment_status,
            'stock_in.status' => $this->status,
            'stock_in.total_amount' => $this->total_amount,
            'stock_in.discount_amount' => $this->discount_amount,
            'stock_in.tax_amount' => $this->tax_amount,
            'stock_in.final_amount' => $this->final_amount,
            'stock_in.paid_amount' => $this->paid_amount,
            'stock_in.created_by' => $this->created_by,
            'stock_in.approved_by' => $this->approved_by,
        ]);

        if (!empty($this->date_range)) {
            $dates = explode(' - ', $this->date_range);
            $query->andFilterWhere(['>=', 'stock_in_date', $dates[0] . ' 00:00:00'])
                  ->andFilterWhere(['<=', 'stock_in_date', $dates[1] . ' 23:59:59']);
        } else {
            $query->andFilterWhere(['like', 'stock_in_date', $this->stock_in_date]);
        }

        if (!empty($this->created_at)) {
            $dates = explode(' - ', $this->created_at);
            $query->andFilterWhere(['>=', 'stock_in.created_at', $dates[0] . ' 00:00:00'])
                  ->andFilterWhere(['<=', 'stock_in.created_at', $dates[1] . ' 23:59:59']);
        }

        if (!empty($this->approved_at)) {
            $dates = explode(' - ', $this->approved_at);
            $query->andFilterWhere(['>=', 'stock_in.approved_at', $dates[0] . ' 00:00:00'])
                  ->andFilterWhere(['<=', 'stock_in.approved_at', $dates[1] . ' 23:59:59']);
        }

        $query->andFilterWhere(['like', 'stock_in.code', $this->code])
              ->andFilterWhere(['like', 'stock_in.reference_number', $this->reference_number])
              ->andFilterWhere(['like', 'stock_in.note', $this->note])
              ->andFilterWhere(['like', 'warehouse.name', $this->warehouse_name])
              ->andFilterWhere(['like', 'supplier.name', $this->supplier_name])
              ->andFilterWhere(['like', 'user.full_name', $this->created_by_name])
              ->andFilterWhere(['like', 'approvedBy.full_name', $this->approved_by_name]);

        return $dataProvider;
    }
}