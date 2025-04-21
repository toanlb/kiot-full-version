<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Supplier;

/**
 * SupplierSearch represents the model behind the search form of `common\models\Supplier`.
 */
class SupplierSearch extends Model
{
    public $id;
    public $code;
    public $name;
    public $phone;
    public $email;
    public $tax_code;
    public $contact_person;
    public $status;
    public $province_id;
    public $district_id;
    public $ward_id;
    public $created_by;
    public $created_date_from;
    public $created_date_to;
    public $debt_from;
    public $debt_to;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'status', 'province_id', 'district_id', 'ward_id', 'created_by'], 'integer'],
            [['code', 'name', 'phone', 'email', 'tax_code', 'contact_person', 'created_date_from', 'created_date_to'], 'safe'],
            [['debt_from', 'debt_to'], 'number'],
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
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Mã nhà cung cấp',
            'name' => 'Tên nhà cung cấp',
            'phone' => 'Điện thoại',
            'email' => 'Email',
            'tax_code' => 'Mã số thuế',
            'contact_person' => 'Người liên hệ',
            'status' => 'Trạng thái',
            'province_id' => 'Tỉnh/Thành phố',
            'district_id' => 'Quận/Huyện',
            'ward_id' => 'Phường/Xã',
            'created_by' => 'Người tạo',
            'created_date_from' => 'Ngày tạo từ',
            'created_date_to' => 'Ngày tạo đến',
            'debt_from' => 'Công nợ từ',
            'debt_to' => 'Công nợ đến',
        ];
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
        $query = Supplier::find();

        // add conditions that should always apply here
        $query->joinWith(['province', 'district', 'ward', 'createdBy']);

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
                    'phone',
                    'email',
                    'status',
                    'debt_amount',
                    'created_at',
                    'province_id' => [
                        'asc' => ['province.name' => SORT_ASC],
                        'desc' => ['province.name' => SORT_DESC],
                        'default' => SORT_ASC
                    ],
                    'district_id' => [
                        'asc' => ['district.name' => SORT_ASC],
                        'desc' => ['district.name' => SORT_DESC],
                        'default' => SORT_ASC
                    ],
                    'ward_id' => [
                        'asc' => ['ward.name' => SORT_ASC],
                        'desc' => ['ward.name' => SORT_DESC],
                        'default' => SORT_ASC
                    ],
                    'created_by' => [
                        'asc' => ['user.username' => SORT_ASC],
                        'desc' => ['user.username' => SORT_DESC],
                        'default' => SORT_ASC
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
            'supplier.id' => $this->id,
            'supplier.status' => $this->status,
            'supplier.province_id' => $this->province_id,
            'supplier.district_id' => $this->district_id,
            'supplier.ward_id' => $this->ward_id,
            'supplier.created_by' => $this->created_by,
        ]);

        $query->andFilterWhere(['like', 'supplier.code', $this->code])
            ->andFilterWhere(['like', 'supplier.name', $this->name])
            ->andFilterWhere(['like', 'supplier.phone', $this->phone])
            ->andFilterWhere(['like', 'supplier.email', $this->email])
            ->andFilterWhere(['like', 'supplier.tax_code', $this->tax_code])
            ->andFilterWhere(['like', 'supplier.contact_person', $this->contact_person]);

        // Filter by debt range
        if (!empty($this->debt_from)) {
            $query->andFilterWhere(['>=', 'supplier.debt_amount', $this->debt_from]);
        }

        if (!empty($this->debt_to)) {
            $query->andFilterWhere(['<=', 'supplier.debt_amount', $this->debt_to]);
        }

        // Filter by date range
        if (!empty($this->created_date_from)) {
            $query->andFilterWhere(['>=', 'DATE(supplier.created_at)', date('Y-m-d', strtotime($this->created_date_from))]);
        }

        if (!empty($this->created_date_to)) {
            $query->andFilterWhere(['<=', 'DATE(supplier.created_at)', date('Y-m-d', strtotime($this->created_date_to))]);
        }

        return $dataProvider;
    }
}