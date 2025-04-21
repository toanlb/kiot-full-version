<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\SupplierDebt;

/**
 * SupplierDebtSearch represents the model behind the search form of `common\models\SupplierDebt`.
 */
class SupplierDebtSearch extends Model
{
    public $id;
    public $supplier_id;
    public $reference_id;
    public $reference_type;
    public $type;
    public $description;
    public $transaction_date_from;
    public $transaction_date_to;
    public $created_by;
    public $amount_from;
    public $amount_to;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'supplier_id', 'reference_id', 'type', 'created_by'], 'integer'],
            [['reference_type', 'description', 'transaction_date_from', 'transaction_date_to'], 'safe'],
            [['amount_from', 'amount_to'], 'number'],
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
            'supplier_id' => 'Nhà cung cấp',
            'reference_id' => 'ID tham chiếu',
            'reference_type' => 'Loại tham chiếu',
            'type' => 'Loại',
            'description' => 'Mô tả',
            'transaction_date_from' => 'Từ ngày',
            'transaction_date_to' => 'Đến ngày',
            'created_by' => 'Người tạo',
            'amount_from' => 'Số tiền từ',
            'amount_to' => 'Số tiền đến',
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
        $query = SupplierDebt::find();

        // add conditions that should always apply here
        $query->joinWith(['supplier', 'createdBy']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'transaction_date' => SORT_DESC,
                    'id' => SORT_DESC,
                ],
                'attributes' => [
                    'id',
                    'transaction_date',
                    'amount',
                    'balance',
                    'type',
                    'supplier_id' => [
                        'asc' => ['supplier.name' => SORT_ASC],
                        'desc' => ['supplier.name' => SORT_DESC],
                    ],
                    'created_by' => [
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
            'supplier_debt.id' => $this->id,
            'supplier_debt.supplier_id' => $this->supplier_id,
            'supplier_debt.reference_id' => $this->reference_id,
            'supplier_debt.type' => $this->type,
            'supplier_debt.created_by' => $this->created_by,
        ]);

        // Filter by amount range
        if (!empty($this->amount_from)) {
            $query->andFilterWhere(['>=', 'supplier_debt.amount', $this->amount_from]);
        }

        if (!empty($this->amount_to)) {
            $query->andFilterWhere(['<=', 'supplier_debt.amount', $this->amount_to]);
        }

        // Filter by transaction date range
        if (!empty($this->transaction_date_from)) {
            $query->andFilterWhere(['>=', 'DATE(supplier_debt.transaction_date)', date('Y-m-d', strtotime($this->transaction_date_from))]);
        }

        if (!empty($this->transaction_date_to)) {
            $query->andFilterWhere(['<=', 'DATE(supplier_debt.transaction_date)', date('Y-m-d', strtotime($this->transaction_date_to))]);
        }

        $query->andFilterWhere(['like', 'supplier_debt.reference_type', $this->reference_type])
            ->andFilterWhere(['like', 'supplier_debt.description', $this->description]);

        return $dataProvider;
    }
}