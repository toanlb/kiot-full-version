<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\CustomerGroup;

/**
 * CustomerGroupSearch represents the model behind the search form of `common\models\CustomerGroup`.
 */
class CustomerGroupSearch extends CustomerGroup
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name', 'description', 'created_at', 'updated_at'], 'safe'],
            [['discount_rate'], 'number'],
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
        $query = CustomerGroup::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'name' => SORT_ASC,
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
            'id' => $this->id,
            'discount_rate' => $this->discount_rate,
        ]);

        if (!empty($this->created_at)) {
            $date = \DateTime::createFromFormat('d-m-Y', $this->created_at);
            if ($date) {
                $date_str = $date->format('Y-m-d');
                $query->andWhere("DATE(created_at) = '$date_str'");
            }
        }

        if (!empty($this->updated_at)) {
            $date = \DateTime::createFromFormat('d-m-Y', $this->updated_at);
            if ($date) {
                $date_str = $date->format('Y-m-d');
                $query->andWhere("DATE(updated_at) = '$date_str'");
            }
        }

        $query->andFilterWhere(['like', 'name', $this->name])
              ->andFilterWhere(['like', 'description', $this->description]);

        return $dataProvider;
    }
}