<?php

namespace backend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\StockTransfer;

/**
 * StockTransferSearch represents the model behind the search form of `common\models\StockTransfer`.
 */
class StockTransferSearch extends StockTransfer
{
    public $source_warehouse_name;
    public $destination_warehouse_name;
    public $created_by_name;
    public $approved_by_name;
    public $received_by_name;
    public $date_range;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'source_warehouse_id', 'destination_warehouse_id', 'status', 'created_by', 'approved_by', 'received_by'], 'integer'],
            [['code', 'transfer_date', 'note', 'created_at', 'updated_at', 'approved_at', 'received_at', 'source_warehouse_name', 'destination_warehouse_name', 'created_by_name', 'approved_by_name', 'received_by_name', 'date_range'], 'safe'],
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
        $query = StockTransfer::find()
            ->joinWith(['sourceWarehouse', 'destinationWarehouse', 'createdBy', 'approvedBy', 'receivedBy']);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
        ]);

        $dataProvider->sort->attributes['source_warehouse_name'] = [
            'asc' => ['sourceWarehouse.name' => SORT_ASC],
            'desc' => ['sourceWarehouse.name' => SORT_DESC],
        ];

        $dataProvider->sort->attributes['destination_warehouse_name'] = [
            'asc' => ['destinationWarehouse.name' => SORT_ASC],
            'desc' => ['destinationWarehouse.name' => SORT_DESC],
        ];

        $dataProvider->sort->attributes['created_by_name'] = [
            'asc' => ['createdBy.full_name' => SORT_ASC],
            'desc' => ['createdBy.full_name' => SORT_DESC],
        ];

        $dataProvider->sort->attributes['approved_by_name'] = [
            'asc' => ['approvedBy.full_name' => SORT_ASC],
            'desc' => ['approvedBy.full_name' => SORT_DESC],
        ];

        $dataProvider->sort->attributes['received_by_name'] = [
            'asc' => ['receivedBy.full_name' => SORT_ASC],
            'desc' => ['receivedBy.full_name' => SORT_DESC],
        ];

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'stock_transfer.id' => $this->id,
            'stock_transfer.source_warehouse_id' => $this->source_warehouse_id,
            'stock_transfer.destination_warehouse_id' => $this->destination_warehouse_id,
            'stock_transfer.status' => $this->status,
            'stock_transfer.created_by' => $this->created_by,
            'stock_transfer.approved_by' => $this->approved_by,
            'stock_transfer.received_by' => $this->received_by,
        ]);

        if (!empty($this->date_range)) {
            $dates = explode(' - ', $this->date_range);
            $query->andFilterWhere(['>=', 'transfer_date', $dates[0] . ' 00:00:00'])
                  ->andFilterWhere(['<=', 'transfer_date', $dates[1] . ' 23:59:59']);
        } else {
            $query->andFilterWhere(['like', 'transfer_date', $this->transfer_date]);
        }

        if (!empty($this->created_at)) {
            $dates = explode(' - ', $this->created_at);
            $query->andFilterWhere(['>=', 'stock_transfer.created_at', $dates[0] . ' 00:00:00'])
                  ->andFilterWhere(['<=', 'stock_transfer.created_at', $dates[1] . ' 23:59:59']);
        }

        if (!empty($this->approved_at)) {
            $dates = explode(' - ', $this->approved_at);
            $query->andFilterWhere(['>=', 'stock_transfer.approved_at', $dates[0] . ' 00:00:00'])
                  ->andFilterWhere(['<=', 'stock_transfer.approved_at', $dates[1] . ' 23:59:59']);
        }

        if (!empty($this->received_at)) {
            $dates = explode(' - ', $this->received_at);
            $query->andFilterWhere(['>=', 'stock_transfer.received_at', $dates[0] . ' 00:00:00'])
                  ->andFilterWhere(['<=', 'stock_transfer.received_at', $dates[1] . ' 23:59:59']);
        }

        $query->andFilterWhere(['like', 'stock_transfer.code', $this->code])
              ->andFilterWhere(['like', 'stock_transfer.note', $this->note])
              ->andFilterWhere(['like', 'sourceWarehouse.name', $this->source_warehouse_name])
              ->andFilterWhere(['like', 'destinationWarehouse.name', $this->destination_warehouse_name])
              ->andFilterWhere(['like', 'createdBy.full_name', $this->created_by_name])
              ->andFilterWhere(['like', 'approvedBy.full_name', $this->approved_by_name])
              ->andFilterWhere(['like', 'receivedBy.full_name', $this->received_by_name]);

        return $dataProvider;
    }
}