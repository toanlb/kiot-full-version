<?php

namespace backend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Warehouse;

/**
 * WarehouseSearch represents the model behind the search form of `common\models\Warehouse`.
 */
class WarehouseSearch extends Warehouse
{
    public $manager_name;
    public $created_by_name;
    public $updated_by_name;
    public $date_range;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'manager_id', 'is_default', 'is_active', 'created_by', 'updated_by'], 'integer'],
            [['code', 'name', 'address', 'phone', 'description', 'created_at', 'updated_at', 'manager_name', 'created_by_name', 'updated_by_name', 'date_range'], 'safe'],
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
        $query = Warehouse::find()
            ->joinWith(['manager', 'createdBy', 'updatedBy']);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
        ]);

        $dataProvider->sort->attributes['manager_name'] = [
            'asc' => ['user.full_name' => SORT_ASC],
            'desc' => ['user.full_name' => SORT_DESC],
        ];

        $dataProvider->sort->attributes['created_by_name'] = [
            'asc' => ['createdBy.full_name' => SORT_ASC],
            'desc' => ['createdBy.full_name' => SORT_DESC],
        ];

        $dataProvider->sort->attributes['updated_by_name'] = [
            'asc' => ['updatedBy.full_name' => SORT_ASC],
            'desc' => ['updatedBy.full_name' => SORT_DESC],
        ];

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'warehouse.id' => $this->id,
            'warehouse.manager_id' => $this->manager_id,
            'warehouse.is_default' => $this->is_default,
            'warehouse.is_active' => $this->is_active,
            'warehouse.created_by' => $this->created_by,
            'warehouse.updated_by' => $this->updated_by,
        ]);

        if (!empty($this->date_range)) {
            $dates = explode(' - ', $this->date_range);
            $query->andFilterWhere(['>=', 'warehouse.created_at', $dates[0] . ' 00:00:00'])
                  ->andFilterWhere(['<=', 'warehouse.created_at', $dates[1] . ' 23:59:59']);
        }

        if (!empty($this->created_at)) {
            $dates = explode(' - ', $this->created_at);
            $query->andFilterWhere(['>=', 'warehouse.created_at', $dates[0] . ' 00:00:00'])
                  ->andFilterWhere(['<=', 'warehouse.created_at', $dates[1] . ' 23:59:59']);
        }

        if (!empty($this->updated_at)) {
            $dates = explode(' - ', $this->updated_at);
            $query->andFilterWhere(['>=', 'warehouse.updated_at', $dates[0] . ' 00:00:00'])
                  ->andFilterWhere(['<=', 'warehouse.updated_at', $dates[1] . ' 23:59:59']);
        }

        $query->andFilterWhere(['like', 'warehouse.code', $this->code])
              ->andFilterWhere(['like', 'warehouse.name', $this->name])
              ->andFilterWhere(['like', 'warehouse.address', $this->address])
              ->andFilterWhere(['like', 'warehouse.phone', $this->phone])
              ->andFilterWhere(['like', 'warehouse.description', $this->description])
              ->andFilterWhere(['like', 'user.full_name', $this->manager_name])
              ->andFilterWhere(['like', 'createdBy.full_name', $this->created_by_name])
              ->andFilterWhere(['like', 'updatedBy.full_name', $this->updated_by_name]);

        return $dataProvider;
    }
}