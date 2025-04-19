<?php
namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Product;

/**
 * ProductSearch represents the model behind the search form of `common\models\Product`.
 */
class ProductSearch extends Product
{
    public $category_name;
    public $unit_name;
    public $price_range;
    public $has_stock;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'category_id', 'unit_id', 'min_stock', 'status', 'is_combo', 'warranty_period', 'created_by', 'updated_by', 'has_stock'], 'integer'],
            [['code', 'barcode', 'name', 'slug', 'description', 'short_description', 'dimension', 'created_at', 'updated_at', 'category_name', 'unit_name', 'price_range'], 'safe'],
            [['cost_price', 'selling_price', 'weight'], 'number'],
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
        $query = Product::find()
            ->joinWith(['category', 'unit']);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ],
                'attributes' => [
                    'id',
                    'code',
                    'name',
                    'category_name' => [
                        'asc' => ['product_category.name' => SORT_ASC],
                        'desc' => ['product_category.name' => SORT_DESC],
                    ],
                    'unit_name' => [
                        'asc' => ['product_unit.name' => SORT_ASC],
                        'desc' => ['product_unit.name' => SORT_DESC],
                    ],
                    'cost_price',
                    'selling_price',
                    'status',
                    'is_combo',
                    'created_at',
                    'updated_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 15,
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
            'product.id' => $this->id,
            'product.category_id' => $this->category_id,
            'product.unit_id' => $this->unit_id,
            'product.min_stock' => $this->min_stock,
            'product.status' => $this->status,
            'product.is_combo' => $this->is_combo,
            'product.warranty_period' => $this->warranty_period,
        ]);

        // Price range filtering
        if (!empty($this->price_range)) {
            list($min, $max) = explode('-', $this->price_range);
            $query->andFilterWhere(['>=', 'product.selling_price', $min]);
            $query->andFilterWhere(['<=', 'product.selling_price', $max]);
        }

        // Has stock filtering
        if ($this->has_stock !== null && $this->has_stock !== '') {
            $stockSubQuery = Stock::find()
                ->select('product_id')
                ->groupBy('product_id')
                ->having('SUM(quantity) > 0');

            if ($this->has_stock == 1) {
                $query->andWhere(['in', 'product.id', $stockSubQuery]);
            } else {
                $query->andWhere(['not in', 'product.id', $stockSubQuery]);
            }
        }

        $query->andFilterWhere(['like', 'product.code', $this->code])
            ->andFilterWhere(['like', 'product.barcode', $this->barcode])
            ->andFilterWhere(['like', 'product.name', $this->name])
            ->andFilterWhere(['like', 'product.slug', $this->slug])
            ->andFilterWhere(['like', 'product.description', $this->description])
            ->andFilterWhere(['like', 'product.short_description', $this->short_description])
            ->andFilterWhere(['like', 'product.dimension', $this->dimension])
            ->andFilterWhere(['like', 'product_category.name', $this->category_name])
            ->andFilterWhere(['like', 'product_unit.name', $this->unit_name]);

        if (!empty($this->created_at)) {
            $dates = explode(' - ', $this->created_at);
            $start_date = $dates[0];
            $end_date = isset($dates[1]) ? $dates[1] : $dates[0];
            $query->andFilterWhere(['between', 'product.created_at', $start_date . ' 00:00:00', $end_date . ' 23:59:59']);
        }

        return $dataProvider;
    }
}