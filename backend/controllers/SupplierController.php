<?php

namespace backend\controllers;

use Yii;
use common\models\Supplier;
use common\models\SupplierSearch;
use common\models\SupplierProduct;
use common\models\SupplierDebt;
use common\models\StockIn;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\components\AccessControl;
use yii\web\UploadedFile;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * SupplierController implements the CRUD actions for Supplier model.
 */
class SupplierController extends Controller
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
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'get-list', 'import', 'export', 'product', 'add-product', 'remove-product'],
                        'allow' => true,
                        'roles' => ['manageSuppliers'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'remove-product' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Supplier models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SupplierSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    /**
     * Displays a single Supplier model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        // Lấy danh sách sản phẩm của nhà cung cấp
        $supplierProducts = SupplierProduct::find()
            ->where(['supplier_id' => $id])
            ->orderBy(['is_primary_supplier' => SORT_DESC, 'product_id' => SORT_ASC])
            ->with('product')
            ->all();

        // Lấy lịch sử nhập hàng
        $stockInHistory = StockIn::find()
            ->where(['supplier_id' => $id])
            ->orderBy(['stock_in_date' => SORT_DESC])
            ->limit(10)
            ->all();

        // Lấy công nợ gần đây
        $debtHistory = SupplierDebt::find()
            ->where(['supplier_id' => $id])
            ->orderBy(['transaction_date' => SORT_DESC])
            ->limit(10)
            ->all();

        return $this->render('view', [
            'model' => $model,
            'supplierProducts' => $supplierProducts,
            'stockInHistory' => $stockInHistory,
            'debtHistory' => $debtHistory,
        ]);
    }

    /**
     * Creates a new Supplier model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Supplier();
        $model->status = 1; // Mặc định là active
        $model->created_at = date('Y-m-d H:i:s');
        $model->updated_at = date('Y-m-d H:i:s');
        $model->created_by = Yii::$app->user->id;

        // Tự động tạo mã nhà cung cấp
        $latestSupplier = Supplier::find()->orderBy(['id' => SORT_DESC])->one();
        if ($latestSupplier) {
            $lastCode = $latestSupplier->code;
            $lastNumber = (int)substr($lastCode, 3);
            $newNumber = $lastNumber + 1;
            $model->code = 'SUP' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        } else {
            $model->code = 'SUP0001';
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Đã thêm nhà cung cấp thành công!');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Supplier model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->updated_at = date('Y-m-d H:i:s');

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Đã cập nhật nhà cung cấp thành công!');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Supplier model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        // Kiểm tra xem nhà cung cấp có phiếu nhập kho hoặc công nợ không
        $hasStockIn = StockIn::find()->where(['supplier_id' => $id])->exists();
        $hasDebt = SupplierDebt::find()->where(['supplier_id' => $id])->exists();

        if ($hasStockIn || $hasDebt) {
            Yii::$app->session->setFlash('error', 'Không thể xóa nhà cung cấp này vì đã có phiếu nhập kho hoặc công nợ liên quan!');
        } else {
            // Xóa tất cả sản phẩm liên quan đến nhà cung cấp
            SupplierProduct::deleteAll(['supplier_id' => $id]);

            // Xóa nhà cung cấp
            $this->findModel($id)->delete();
            Yii::$app->session->setFlash('success', 'Đã xóa nhà cung cấp thành công!');
        }

        return $this->redirect(['index']);
    }

    /**
     * Quản lý sản phẩm của nhà cung cấp
     */
    public function actionProduct($id)
    {
        $supplier = $this->findModel($id);
        $model = new SupplierProduct();
        $model->supplier_id = $id;
        $model->created_at = date('Y-m-d H:i:s');
        $model->updated_at = date('Y-m-d H:i:s');

        // Lấy danh sách sản phẩm của nhà cung cấp
        $supplierProducts = SupplierProduct::find()
            ->where(['supplier_id' => $id])
            ->orderBy(['is_primary_supplier' => SORT_DESC, 'product_id' => SORT_ASC])
            ->with('product')
            ->all();

        return $this->render('product', [
            'supplier' => $supplier,
            'model' => $model,
            'supplierProducts' => $supplierProducts,
        ]);
    }

    /**
     * Thêm sản phẩm cho nhà cung cấp
     */
    public function actionAddProduct()
    {
        $model = new SupplierProduct();

        if ($model->load(Yii::$app->request->post())) {
            // Kiểm tra xem sản phẩm đã tồn tại cho nhà cung cấp này chưa
            $exists = SupplierProduct::find()
                ->where(['supplier_id' => $model->supplier_id, 'product_id' => $model->product_id])
                ->exists();

            if ($exists) {
                Yii::$app->session->setFlash('error', 'Sản phẩm này đã được thêm cho nhà cung cấp!');
            } else {
                $model->created_at = date('Y-m-d H:i:s');
                $model->updated_at = date('Y-m-d H:i:s');

                if ($model->save()) {
                    Yii::$app->session->setFlash('success', 'Đã thêm sản phẩm cho nhà cung cấp thành công!');
                } else {
                    Yii::$app->session->setFlash('error', 'Lỗi khi thêm sản phẩm: ' . print_r($model->errors, true));
                }
            }
        }

        return $this->redirect(['product', 'id' => $model->supplier_id]);
    }

    /**
     * Xóa sản phẩm khỏi nhà cung cấp
     */
    public function actionRemoveProduct($id)
    {
        $model = SupplierProduct::findOne($id);

        if (!$model) {
            throw new NotFoundHttpException('Không tìm thấy sản phẩm.');
        }

        $supplierId = $model->supplier_id;
        $model->delete();

        Yii::$app->session->setFlash('success', 'Đã xóa sản phẩm khỏi nhà cung cấp thành công!');
        return $this->redirect(['product', 'id' => $supplierId]);
    }

    /**
     * Lấy danh sách nhà cung cấp dạng JSON cho dropdown/autocomplete
     */
    public function actionGetList($q = null)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $query = Supplier::find()
            ->where(['status' => 1])
            ->andFilterWhere([
                'or',
                ['like', 'name', $q],
                ['like', 'code', $q],
                ['like', 'phone', $q],
            ]);

        $suppliers = $query->limit(20)->all();

        $results = [];
        foreach ($suppliers as $supplier) {
            $results[] = [
                'id' => $supplier->id,
                'text' => $supplier->code . ' - ' . $supplier->name . ($supplier->phone ? ' (' . $supplier->phone . ')' : ''),
                'debt_amount' => $supplier->debt_amount,
            ];
        }

        return $results;
    }

    /**
     * Finds the Supplier model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Supplier the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Supplier::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Không tìm thấy nhà cung cấp.');
    }
}
