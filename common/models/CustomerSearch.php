<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Customer;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * CustomerSearch represents the model behind the search form of `common\models\Customer`.
 */
class CustomerSearch extends Customer
{
    /**
     * Additional attribute for search
     */
    public $customer_group_name;
    public $date_range;
    public $province_name;
    public $district_name;
    public $created_by_name;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'customer_group_id', 'gender', 'status', 'province_id', 'district_id', 'ward_id', 'created_by'], 'integer'],
            [['code', 'name', 'phone', 'email', 'address', 'tax_code', 'birthday', 'company_name', 'created_at', 'updated_at', 'customer_group_name', 'date_range', 'province_name', 'district_name', 'created_by_name'], 'safe'],
            [['credit_limit', 'debt_amount'], 'number'],
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
        $query = Customer::find();
        $query->joinWith(['customerGroup', 'province', 'district', 'createdBy']);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'name' => SORT_ASC,
                ],
                'attributes' => [
                    'id',
                    'code',
                    'name',
                    'phone',
                    'email',
                    'customer_group_name' => [
                        'asc' => ['customer_group.name' => SORT_ASC],
                        'desc' => ['customer_group.name' => SORT_DESC],
                    ],
                    'gender',
                    'status',
                    'credit_limit',
                    'debt_amount',
                    'province_name' => [
                        'asc' => ['province.name' => SORT_ASC],
                        'desc' => ['province.name' => SORT_DESC],
                    ],
                    'district_name' => [
                        'asc' => ['district.name' => SORT_ASC],
                        'desc' => ['district.name' => SORT_DESC],
                    ],
                    'created_at',
                    'updated_at',
                    'created_by_name' => [
                        'asc' => ['user.username' => SORT_ASC],
                        'desc' => ['user.username' => SORT_DESC],
                    ],
                ],
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
            'customer.id' => $this->id,
            'customer.customer_group_id' => $this->customer_group_id,
            'customer.gender' => $this->gender,
            'customer.status' => $this->status,
            'customer.credit_limit' => $this->credit_limit,
            'customer.debt_amount' => $this->debt_amount,
            'customer.province_id' => $this->province_id,
            'customer.district_id' => $this->district_id,
            'customer.ward_id' => $this->ward_id,
            'customer.created_by' => $this->created_by,
        ]);

        // Xử lý tìm kiếm theo khoảng thời gian
        if (!empty($this->date_range) && strpos($this->date_range, ' - ') !== false) {
            list($start_date, $end_date) = explode(' - ', $this->date_range);
            $start_date = \DateTime::createFromFormat('d-m-Y', $start_date);
            $end_date = \DateTime::createFromFormat('d-m-Y', $end_date);
            
            if ($start_date && $end_date) {
                $start_date = $start_date->format('Y-m-d');
                $end_date = $end_date->format('Y-m-d');
                $query->andWhere(['between', 'DATE(customer.created_at)', $start_date, $end_date]);
            }
        }

        // Xử lý ngày sinh
        if (!empty($this->birthday)) {
            // Chuyển đổi từ định dạng HTML5 date (YYYY-MM-DD) sang định dạng cơ sở dữ liệu
            $date = \DateTime::createFromFormat('Y-m-d', $this->birthday);
            if ($date) {
                $date_str = $date->format('Y-m-d');
                $query->andWhere("DATE(customer.birthday) = :birthday", [':birthday' => $date_str]);
            }
        }

        $query->andFilterWhere(['like', 'customer.code', $this->code])
            ->andFilterWhere(['like', 'customer.name', $this->name])
            ->andFilterWhere(['like', 'customer.phone', $this->phone])
            ->andFilterWhere(['like', 'customer.email', $this->email])
            ->andFilterWhere(['like', 'customer.address', $this->address])
            ->andFilterWhere(['like', 'customer.tax_code', $this->tax_code])
            ->andFilterWhere(['like', 'customer.company_name', $this->company_name])
            ->andFilterWhere(['like', 'customer_group.name', $this->customer_group_name])
            ->andFilterWhere(['like', 'province.name', $this->province_name])
            ->andFilterWhere(['like', 'district.name', $this->district_name])
            ->andFilterWhere(['like', 'user.username', $this->created_by_name]);

        return $dataProvider;
    }
}