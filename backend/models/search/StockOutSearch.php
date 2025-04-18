<?php

namespace backend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\StockOut;

/**
 * StockOutSearch represents the model behind the search form of `common\models\StockOut`.
 */
class StockOutSearch extends StockOut
{
    public $warehouse_name;
    public $recipient_name;
    public $created_by_name;
    public $approved_by_name;
    public $date_range;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'warehouse_id', 'reference_id', 'status', 'created_by', 'approved_by'], 'integer'],
            [['code', 'reference_type', 'recipient', 'stock_out_date', 'note', 'created_at', 'updated_at', 'approved_at', 'warehouse_name', 'recipient_name', 'created_by_name', 'approved_by_name', 'date_range'], 'safe'],
            [['total_amount'], 'number'],
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
        $query = StockOut::find()
            ->joinWith(['warehouse', 'createdBy', 'approvedBy']);

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
            'stock_out.id' => $this->id,
            'stock_out.warehouse_id' => $this->warehouse_id,
            'stock_out.reference_id' => $this->reference_id,
            'stock_out.status' => $this->status,
            'stock_out.total_amount' => $this->total_amount,
            'stock_out.created_by' => $this->created_by,
            'stock_out.approved_by' => $this->approved_by,
        ]);

        if (!empty($this->date_range)) {
            $dates = explode(' - ', $this->date_range);
            $query->andFilterWhere(['>=', 'stock_out_date', $dates[0] . ' 00:00:00'])
                  ->andFilterWhere(['<=', 'stock_out_date', $dates[1] . ' 23:59:59']);
        } else {
            $query->andFilterWhere(['like', 'stock_out_date', $this->stock_out_date]);
        }

        if (!empty($this->created_at)) {
            $dates = explode(' - ', $this->created_at);
            $query->andFilterWhere(['>=', 'stock_out.created_at', $dates[0] . ' 00:00:00'])
                  ->andFilterWhere(['<=', 'stock_out.created_at', $dates[1] . ' 23:59:59']);
        }

        if (!empty($this->approved_at)) {
            $dates = explode(' - ', $this->approved_at);
            $query->andFilterWhere(['>=', 'stock_out.approved_at', $dates[0] . ' 00:00:00'])
                  ->andFilterWhere(['<=', 'stock_out.approved_at', $dates[1] . ' 23:59:59']);
        }

        $query->andFilterWhere(['like', 'stock_out.code', $this->code])
              ->andFilterWhere(['like', 'stock_out.reference_type', $this->reference_type])
              ->andFilterWhere(['like', 'stock_out.recipient', $this->recipient])
              ->andFilterWhere(['like', 'stock_out.note', $this->note])
              ->andFilterWhere(['like', 'warehouse.name', $this->warehouse_name])
              ->andFilterWhere(['like', 'user.full_name', $this->created_by_name])
              ->andFilterWhere(['like', 'approvedBy.full_name', $this->approved_by_name]);

        return $dataProvider;
    }
}