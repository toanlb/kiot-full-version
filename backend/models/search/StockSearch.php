<?php

namespace backend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Stock;

/**
 * StockSearch represents the model behind the search form of `common\models\Stock`.
 */
class StockSearch extends Stock
{
    public $product_name;
    public $product_code;
    public $product_category;
    public $warehouse_name;
    public $min_quantity;
    public $max_quantity;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'product_id', 'warehouse_id', 'min_stock'], 'integer'],
            [['quantity', 'min_quantity', 'max_quantity'], 'number'],
            [['updated_at', 'product_name', 'product_code', 'product_category', 'warehouse_name'], 'safe'],
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
        $query = Stock::find()
            ->joinWith(['product', 'warehouse', 'product.category']);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
        ]);

        $dataProvider->sort->attributes['product_name'] = [
            'asc' => ['product.name' => SORT_ASC],
            'desc' => ['product.name' => SORT_DESC],
        ];

        $dataProvider->sort->attributes['product_code'] = [
            'asc' => ['product.code' => SORT_ASC],
            'desc' => ['product.code' => SORT_DESC],
        ];

        $dataProvider->sort->attributes['product_category'] = [
            'asc' => ['product_category.name' => SORT_ASC],
            'desc' => ['product_category.name' => SORT_DESC],
        ];

        $dataProvider->sort->attributes['warehouse_name'] = [
            'asc' => ['warehouse.name' => SORT_ASC],
            'desc' => ['warehouse.name' => SORT_DESC],
        ];

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'stock.id' => $this->id,
            'stock.product_id' => $this->product_id,
            'stock.warehouse_id' => $this->warehouse_id,
            'stock.min_stock' => $this->min_stock,
        ]);

        if (!empty($this->min_quantity)) {
            $query->andWhere(['>=', 'stock.quantity', $this->min_quantity]);
        }

        if (!empty($this->max_quantity)) {
            $query->andWhere(['<=', 'stock.quantity', $this->max_quantity]);
        } else {
            $query->andFilterWhere(['=', 'stock.quantity', $this->quantity]);
        }

        if (!empty($this->updated_at)) {
            $dates = explode(' - ', $this->updated_at);
            $query->andFilterWhere(['>=', 'stock.updated_at', $dates[0] . ' 00:00:00'])
                  ->andFilterWhere(['<=', 'stock.updated_at', $dates[1] . ' 23:59:59']);
        }

        $query->andFilterWhere(['like', 'product.name', $this->product_name])
              ->andFilterWhere(['like', 'product.code', $this->product_code])
              ->andFilterWhere(['like', 'product_category.name', $this->product_category])
              ->andFilterWhere(['like', 'warehouse.name', $this->warehouse_name]);

        return $dataProvider;
    }
}