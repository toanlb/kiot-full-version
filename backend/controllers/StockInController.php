<?php
namespace backend\controllers;

use Yii;
use common\models\StockIn;
use common\models\StockInDetail;
use common\models\Supplier;
use common\models\Warehouse;
use common\models\Product;
use common\models\ProductUnit;
use backend\models\search\StockInSearch;
use backend\services\StockInService;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Json;

class StockInController extends Controller
{
    private $stockInService;

    public function __construct($id, $module, StockInService $stockInService, $config = [])
    {
        $this->stockInService = $stockInService;
        parent::__construct($id, $module, $config);
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['manageProducts'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'approve' => ['POST'],
                    'cancel' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new StockInSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        
        return $this->render('view', [
            'model' => $model,
            'details' => $model->stockInDetails,
        ]);
    }

    public function actionCreate()
    {
        $model = new StockIn();
        $model->code = $this->stockInService->generateStockInCode();
        $model->status = StockIn::STATUS_DRAFT;
        $model->stock_in_date = date('Y-m-d H:i:s');
        $model->created_at = date('Y-m-d H:i:s');
        $model->updated_at = date('Y-m-d H:i:s');
        $model->created_by = Yii::$app->user->id;

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    // Lưu chi tiết nhập kho
                    $details = Yii::$app->request->post('StockInDetail', []);
                    $this->stockInService->saveStockInDetails($model->id, $details);
                    
                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'Phiếu nhập kho được tạo thành công.');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
            }
        }

        return $this->render('create', [
            'model' => $model,
            'warehouses' => Warehouse::getList(),
            'suppliers' => Supplier::getList(),
            'products' => Product::getList(),
            'units' => ProductUnit::getList(),
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        if ($model->status != StockIn::STATUS_DRAFT) {
            Yii::$app->session->setFlash('error', 'Không thể chỉnh sửa phiếu nhập kho đã xác nhận hoặc hoàn thành.');
            return $this->redirect(['view', 'id' => $model->id]);
        }
        
        $model->updated_at = date('Y-m-d H:i:s');

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    // Xóa chi tiết cũ và lưu chi tiết mới
                    StockInDetail::deleteAll(['stock_in_id' => $model->id]);
                    $details = Yii::$app->request->post('StockInDetail', []);
                    $this->stockInService->saveStockInDetails($model->id, $details);
                    
                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'Phiếu nhập kho được cập nhật thành công.');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
            }
        }

        return $this->render('update', [
            'model' => $model,
            'warehouses' => Warehouse::getList(),
            'suppliers' => Supplier::getList(),
            'products' => Product::getList(),
            'units' => ProductUnit::getList(),
            'details' => $model->stockInDetails,
        ]);
    }

    public function actionApprove($id)
    {
        $result = $this->stockInService->approveStockIn($id, Yii::$app->user->id);
        
        if ($result['success']) {
            Yii::$app->session->setFlash('success', $result['message']);
        } else {
            Yii::$app->session->setFlash('error', $result['message']);
        }
        
        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionComplete($id)
    {
        $result = $this->stockInService->completeStockIn($id);
        
        if ($result['success']) {
            Yii::$app->session->setFlash('success', $result['message']);
        } else {
            Yii::$app->session->setFlash('error', $result['message']);
        }
        
        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionCancel($id)
    {
        $result = $this->stockInService->cancelStockIn($id);
        
        if ($result['success']) {
            Yii::$app->session->setFlash('success', $result['message']);
        } else {
            Yii::$app->session->setFlash('error', $result['message']);
        }
        
        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionPrint($id)
    {
        $model = $this->findModel($id);
        
        return $this->renderPartial('print', [
            'model' => $model,
            'details' => $model->stockInDetails,
        ]);
    }

    public function actionGetProduct($id)
    {
        $product = Product::findOne($id);
        if ($product) {
            return Json::encode([
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'cost_price' => $product->cost_price,
                'unit_id' => $product->unit_id,
                'unit_name' => $product->unit->name,
            ]);
        }
        
        return Json::encode(['error' => 'Không tìm thấy sản phẩm']);
    }

    protected function findModel($id)
    {
        if (($model = StockIn::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Phiếu nhập kho không tồn tại.');
    }
}