<?php
namespace backend\controllers;

use Yii;
use common\models\Stock;
use common\models\Product;
use common\models\Warehouse;
use backend\models\search\StockSearch;
use backend\services\StockService;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\components\AccessControl; 

class StockController extends Controller
{
    private $stockService;

    public function __construct($id, $module, StockService $stockService, $config = [])
    {
        $this->stockService = $stockService;
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
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new StockSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'warehouses' => Warehouse::getList(),
        ]);
    }

    public function actionView($product_id, $warehouse_id)
    {
        return $this->render('view', [
            'model' => $this->findModel($product_id, $warehouse_id),
            'productBatches' => $this->stockService->getProductBatches($product_id, $warehouse_id),
            'stockMovements' => $this->stockService->getStockMovements($product_id, $warehouse_id),
        ]);
    }

    public function actionUpdate($product_id, $warehouse_id)
    {
        $model = $this->findModel($product_id, $warehouse_id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Thông tin tồn kho được cập nhật thành công.');
            return $this->redirect(['view', 'product_id' => $model->product_id, 'warehouse_id' => $model->warehouse_id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionLowStock()
    {
        $stocks = $this->stockService->getLowStockItems();
        
        return $this->render('low-stock', [
            'stocks' => $stocks,
            'warehouses' => Warehouse::getList(),
        ]);
    }

    public function actionReport()
    {
        $searchModel = new StockSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        return $this->render('report', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'warehouses' => Warehouse::getList(),
        ]);
    }

    protected function findModel($product_id, $warehouse_id)
    {
        if (($model = Stock::findOne(['product_id' => $product_id, 'warehouse_id' => $warehouse_id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Thông tin tồn kho không tồn tại.');
    }
}