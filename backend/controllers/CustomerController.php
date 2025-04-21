<?php

namespace backend\controllers;

use Yii;
use common\models\Customer;
use common\models\CustomerGroup;
use common\models\CustomerPoint;
use common\models\CustomerPointHistory;
use common\models\CustomerDebt;
use common\models\Province;
use common\models\District;
use common\models\Ward;
use common\models\CustomerSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use yii\helpers\Json;

/**
 * CustomerController implements the CRUD actions for Customer model.
 */
class CustomerController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'change-group', 
                                      'get-districts', 'get-wards', 'debt-history', 'point-history', 
                                      'add-points', 'use-points', 'add-debt', 'pay-debt'],
                        'allow' => true,
                        'roles' => ['@'], // Yêu cầu người dùng đã đăng nhập
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'add-points' => ['POST'],
                    'use-points' => ['POST'],
                    'add-debt' => ['POST'],
                    'pay-debt' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Customer models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CustomerSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Customer model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        
        // Lấy 5 lịch sử giao dịch công nợ gần nhất
        $debtProvider = new ActiveDataProvider([
            'query' => CustomerDebt::find()->where(['customer_id' => $id])->orderBy(['transaction_date' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 5,
            ],
        ]);
        
        // Lấy 5 lịch sử điểm thưởng gần nhất
        $pointProvider = new ActiveDataProvider([
            'query' => CustomerPointHistory::find()->where(['customer_id' => $id])->orderBy(['created_at' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 5,
            ],
        ]);

        return $this->render('view', [
            'model' => $model,
            'debtProvider' => $debtProvider,
            'pointProvider' => $pointProvider,
        ]);
    }

    /**
     * Creates a new Customer model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Customer();
        $model->code = Customer::generateCode();
        $model->status = Customer::STATUS_ACTIVE;

        // Xử lý group_id từ URL (khi thêm khách hàng vào nhóm)
        $group_id = Yii::$app->request->get('group_id');
        if ($group_id) {
            $group = CustomerGroup::findOne($group_id);
            if ($group) {
                $model->customer_group_id = $group_id;
            }
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Khách hàng đã được tạo thành công.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Customer model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Khách hàng đã được cập nhật thành công.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Customer model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        // Kiểm tra nếu khách hàng có đơn hàng hoặc giao dịch công nợ
        $hasOrders = $model->getOrders()->count() > 0;
        $hasDebts = $model->getCustomerDebts()->count() > 0;
        $hasReturns = $model->getReturns()->count() > 0;
        $hasWarranties = $model->getWarranties()->count() > 0;
        
        if ($hasOrders || $hasDebts || $hasReturns || $hasWarranties) {
            Yii::$app->session->setFlash('error', 'Không thể xóa khách hàng vì đã có dữ liệu liên quan (đơn hàng, công nợ, bảo hành).');
            return $this->redirect(['index']);
        }
        
        // Nếu khách hàng đang có nợ
        if ($model->debt_amount > 0) {
            Yii::$app->session->setFlash('error', 'Không thể xóa khách hàng vì đang có công nợ.');
            return $this->redirect(['index']);
        }
        
        try {
            // Xóa điểm tích lũy
            CustomerPoint::deleteAll(['customer_id' => $id]);
            CustomerPointHistory::deleteAll(['customer_id' => $id]);
            
            // Xóa khách hàng
            $model->delete();
            Yii::$app->session->setFlash('success', 'Khách hàng đã được xóa thành công.');
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', 'Không thể xóa khách hàng: ' . $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    /**
     * Change customer group
     * @param integer $id
     * @return mixed
     */
    public function actionChangeGroup($id)
    {
        $model = $this->findModel($id);
        
        if (Yii::$app->request->isPost) {
            $newGroupId = Yii::$app->request->post('customer_group_id');
            $model->customer_group_id = $newGroupId;
            
            if ($model->save(false)) {
                Yii::$app->session->setFlash('success', 'Nhóm khách hàng đã được thay đổi thành công.');
            } else {
                Yii::$app->session->setFlash('error', 'Không thể thay đổi nhóm khách hàng.');
            }
            
            return $this->redirect(['view', 'id' => $id]);
        }
        
        return $this->render('change-group', [
            'model' => $model,
        ]);
    }

    /**
     * Display debt history
     * @param integer $id
     * @return mixed
     */
    public function actionDebtHistory($id)
    {
        $model = $this->findModel($id);
        
        $dataProvider = new ActiveDataProvider([
            'query' => CustomerDebt::find()->where(['customer_id' => $id])->orderBy(['transaction_date' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        
        return $this->render('debt-history', [
            'model' => $model,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Display point history
     * @param integer $id
     * @return mixed
     */
    public function actionPointHistory($id)
    {
        $model = $this->findModel($id);
        
        $dataProvider = new ActiveDataProvider([
            'query' => CustomerPointHistory::find()->where(['customer_id' => $id])->orderBy(['created_at' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        
        return $this->render('point-history', [
            'model' => $model,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Add points to customer
     * @param integer $id
     * @return mixed
     */
    public function actionAddPoints($id)
    {
        $model = $this->findModel($id);
        
        if (Yii::$app->request->isPost) {
            $points = (int)Yii::$app->request->post('points');
            $note = Yii::$app->request->post('note', '');
            
            if ($points > 0) {
                if (CustomerPoint::addPoints($id, $points, null, null, $note)) {
                    Yii::$app->session->setFlash('success', 'Đã cộng ' . $points . ' điểm thành công.');
                } else {
                    Yii::$app->session->setFlash('error', 'Không thể cộng điểm.');
                }
            } else {
                Yii::$app->session->setFlash('error', 'Số điểm phải lớn hơn 0.');
            }
            
            return $this->redirect(['view', 'id' => $id]);
        }
        
        Yii::$app->session->setFlash('error', 'Yêu cầu không hợp lệ.');
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Use points from customer
     * @param integer $id
     * @return mixed
     */
    public function actionUsePoints($id)
    {
        $model = $this->findModel($id);
        $customerPoint = $model->customerPoint;
        
        if (!$customerPoint) {
            Yii::$app->session->setFlash('error', 'Khách hàng chưa có điểm tích lũy.');
            return $this->redirect(['view', 'id' => $id]);
        }
        
        if (Yii::$app->request->isPost) {
            $points = (int)Yii::$app->request->post('points');
            $note = Yii::$app->request->post('note', '');
            
            if ($points > 0 && $points <= $customerPoint->points) {
                if (CustomerPoint::usePoints($id, $points, null, null, $note)) {
                    Yii::$app->session->setFlash('success', 'Đã trừ ' . $points . ' điểm thành công.');
                } else {
                    Yii::$app->session->setFlash('error', 'Không thể trừ điểm.');
                }
            } else {
                Yii::$app->session->setFlash('error', 'Số điểm không hợp lệ hoặc không đủ.');
            }
            
            return $this->redirect(['view', 'id' => $id]);
        }
        
        Yii::$app->session->setFlash('error', 'Yêu cầu không hợp lệ.');
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Add debt to customer
     * @param integer $id
     * @return mixed
     */
    public function actionAddDebt($id)
    {
        $model = $this->findModel($id);
        
        if (Yii::$app->request->isPost) {
            $amount = (float)Yii::$app->request->post('amount');
            $description = Yii::$app->request->post('description', '');
            
            if ($amount > 0) {
                if ($model->checkCreditLimit($amount)) {
                    if (CustomerDebt::recordDebt($id, $amount, CustomerDebt::TYPE_DEBT, null, null, $description)) {
                        Yii::$app->session->setFlash('success', 'Đã thêm khoản nợ ' . Yii::$app->formatter->asCurrency($amount) . ' thành công.');
                    } else {
                        Yii::$app->session->setFlash('error', 'Không thể thêm khoản nợ.');
                    }
                } else {
                    Yii::$app->session->setFlash('error', 'Vượt quá hạn mức tín dụng của khách hàng.');
                }
            } else {
                Yii::$app->session->setFlash('error', 'Số tiền phải lớn hơn 0.');
            }
            
            return $this->redirect(['view', 'id' => $id]);
        }
        
        Yii::$app->session->setFlash('error', 'Yêu cầu không hợp lệ.');
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Pay debt for customer
     * @param integer $id
     * @return mixed
     */
    public function actionPayDebt($id)
    {
        $model = $this->findModel($id);
        
        if ($model->debt_amount <= 0) {
            Yii::$app->session->setFlash('error', 'Khách hàng không có công nợ.');
            return $this->redirect(['view', 'id' => $id]);
        }
        
        if (Yii::$app->request->isPost) {
            $amount = (float)Yii::$app->request->post('amount');
            $description = Yii::$app->request->post('description', '');
            
            if ($amount > 0 && $amount <= $model->debt_amount) {
                if (CustomerDebt::recordDebt($id, $amount, CustomerDebt::TYPE_PAYMENT, null, null, $description)) {
                    Yii::$app->session->setFlash('success', 'Đã thanh toán khoản nợ ' . Yii::$app->formatter->asCurrency($amount) . ' thành công.');
                } else {
                    Yii::$app->session->setFlash('error', 'Không thể thanh toán khoản nợ.');
                }
            } else {
                Yii::$app->session->setFlash('error', 'Số tiền không hợp lệ hoặc vượt quá số nợ.');
            }
            
            return $this->redirect(['view', 'id' => $id]);
        }
        
        Yii::$app->session->setFlash('error', 'Yêu cầu không hợp lệ.');
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Get districts by province
     * @return array
     */
    public function actionGetDistricts()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $province_id = Yii::$app->request->post('province_id');
        
        if (!$province_id) {
            return ['output' => '', 'selected' => ''];
        }
        
        $districts = District::find()
            ->where(['province_id' => $province_id])
            ->orderBy('name')
            ->all();
            
        // Chuyển đổi danh sách quận/huyện thành HTML options
        $options = '';
        foreach ($districts as $district) {
            $options .= '<option value="' . $district->id . '">' . $district->name . '</option>';
        }
        
        return ['output' => $options, 'selected' => ''];
    }

    /**
     * Get wards by district
     * @return array
     */
    public function actionGetWards()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $district_id = Yii::$app->request->post('district_id');
        
        if (!$district_id) {
            return ['output' => '', 'selected' => ''];
        }
        
        $wards = Ward::find()
            ->where(['district_id' => $district_id])
            ->orderBy('name')
            ->all();
            
        // Chuyển đổi danh sách phường/xã thành HTML options
        $options = '';
        foreach ($wards as $ward) {
            $options .= '<option value="' . $ward->id . '">' . $ward->name . '</option>';
        }
        
        return ['output' => $options, 'selected' => ''];
    }

    /**
     * Finds the Customer model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Customer the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Customer::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Không tìm thấy khách hàng được yêu cầu.');
    }
}