<?php
namespace backend\controllers;

use Yii;
use common\models\SupplierDebt;
use common\models\SupplierDebtSearch;
use common\models\Supplier;
use common\models\Payment;
use common\models\PaymentMethod;
use common\models\CashBook;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\components\AccessControl; 
use yii\helpers\ArrayHelper;
use yii\db\Expression;
use yii\db\Transaction;

/**
 * SupplierDebtController implements the CRUD actions for SupplierDebt model.
 */
class SupplierDebtController extends Controller
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
                        'actions' => ['index', 'view', 'payment', 'create', 'update', 'delete', 'export', 'report'],
                        'allow' => true,
                        'roles' => ['manageSuppliers'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all SupplierDebt models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SupplierDebtSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single SupplierDebt model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Thanh toán công nợ cho nhà cung cấp
     * @param integer $supplier_id
     * @return mixed
     */
    public function actionPayment($supplier_id = null)
    {
        $paymentModel = new Payment();
        $paymentModel->payment_type = 2; // Thanh toán nợ nhà cung cấp
        $paymentModel->payment_date = date('Y-m-d H:i:s');
        $paymentModel->created_at = date('Y-m-d H:i:s');
        $paymentModel->updated_at = date('Y-m-d H:i:s');
        $paymentModel->created_by = Yii::$app->user->id;
        $paymentModel->status = 1; // Đã xác nhận
        
        // Tự động tạo mã phiếu chi
        $latestPayment = Payment::find()->orderBy(['id' => SORT_DESC])->one();
        if ($latestPayment) {
            $lastCode = $latestPayment->code;
            $lastNumber = (int)substr($lastCode, 3);
            $newNumber = $lastNumber + 1;
            $paymentModel->code = 'PAY' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
        } else {
            $paymentModel->code = 'PAY000001';
        }
        
        if ($supplier_id) {
            $supplier = Supplier::findOne($supplier_id);
            if (!$supplier) {
                throw new NotFoundHttpException('Không tìm thấy nhà cung cấp.');
            }
            $paymentModel->supplier_id = $supplier_id;
        }
        
        $debtModel = new SupplierDebt();
        $debtModel->type = 2; // Thanh toán
        $debtModel->transaction_date = date('Y-m-d H:i:s');
        $debtModel->created_at = date('Y-m-d H:i:s');
        $debtModel->created_by = Yii::$app->user->id;
        
        if ($paymentModel->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                // Lưu phiếu chi
                if (!$paymentModel->save()) {
                    throw new \Exception('Lỗi khi lưu phiếu chi: ' . print_r($paymentModel->errors, true));
                }
                
                // Cập nhật model SupplierDebt
                $debtModel->supplier_id = $paymentModel->supplier_id;
                $debtModel->amount = $paymentModel->amount;
                $debtModel->reference_id = $paymentModel->id;
                $debtModel->reference_type = 'payment';
                $debtModel->description = $paymentModel->description;
                
                // Tính toán số dư
                $supplier = Supplier::findOne($paymentModel->supplier_id);
                if (!$supplier) {
                    throw new \Exception('Không tìm thấy nhà cung cấp.');
                }
                
                $debtModel->balance = $supplier->debt_amount - $paymentModel->amount;
                
                // Lưu công nợ
                if (!$debtModel->save()) {
                    throw new \Exception('Lỗi khi lưu công nợ: ' . print_r($debtModel->errors, true));
                }
                
                // Cập nhật số dư công nợ của nhà cung cấp
                $supplier->debt_amount = $debtModel->balance;
                if (!$supplier->save(false)) {
                    throw new \Exception('Lỗi khi cập nhật công nợ nhà cung cấp.');
                }
                
                // Ghi nhận vào sổ quỹ
                $cashBook = new CashBook();
                $cashBook->transaction_date = $paymentModel->payment_date;
                $cashBook->reference_id = $paymentModel->id;
                $cashBook->reference_type = 'payment';
                $cashBook->payment_method_id = $paymentModel->payment_method_id;
                $cashBook->amount = $paymentModel->amount;
                $cashBook->type = 2; // Chi tiền
                
                // Tính số dư quỹ
                $lastCashBook = CashBook::find()
                    ->where(['payment_method_id' => $paymentModel->payment_method_id])
                    ->orderBy(['id' => SORT_DESC])
                    ->one();
                
                if ($lastCashBook) {
                    $cashBook->balance = $lastCashBook->balance - $paymentModel->amount;
                } else {
                    $cashBook->balance = -$paymentModel->amount;
                }
                
                $cashBook->description = 'Thanh toán công nợ nhà cung cấp: ' . $supplier->name;
                $cashBook->created_at = date('Y-m-d H:i:s');
                $cashBook->created_by = Yii::$app->user->id;
                
                if (!$cashBook->save()) {
                    throw new \Exception('Lỗi khi ghi sổ quỹ: ' . print_r($cashBook->errors, true));
                }
                
                $transaction->commit();
                
                Yii::$app->session->setFlash('success', 'Đã thanh toán công nợ nhà cung cấp thành công!');
                return $this->redirect(['view', 'id' => $debtModel->id]);
                
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }
        
        // Lấy danh sách phương thức thanh toán
        $paymentMethods = ArrayHelper::map(PaymentMethod::find()->where(['is_active' => 1])->all(), 'id', 'name');
        
        return $this->render('payment', [
            'paymentModel' => $paymentModel,
            'debtModel' => $debtModel,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    /**
     * Creates a new manual SupplierDebt model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new SupplierDebt();
        $model->transaction_date = date('Y-m-d H:i:s');
        $model->created_at = date('Y-m-d H:i:s');
        $model->created_by = Yii::$app->user->id;
        
        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                // Tính toán số dư
                $supplier = Supplier::findOne($model->supplier_id);
                if (!$supplier) {
                    throw new \Exception('Không tìm thấy nhà cung cấp.');
                }
                
                if ($model->type == 1) { // Nợ
                    $model->balance = $supplier->debt_amount + $model->amount;
                } else { // Thanh toán
                    $model->balance = $supplier->debt_amount - $model->amount;
                }
                
                // Lưu công nợ
                if (!$model->save()) {
                    throw new \Exception('Lỗi khi lưu công nợ: ' . print_r($model->errors, true));
                }
                
                // Cập nhật số dư công nợ của nhà cung cấp
                $supplier->debt_amount = $model->balance;
                if (!$supplier->save(false)) {
                    throw new \Exception('Lỗi khi cập nhật công nợ nhà cung cấp.');
                }
                
                $transaction->commit();
                
                Yii::$app->session->setFlash('success', 'Đã tạo công nợ nhà cung cấp thành công!');
                return $this->redirect(['view', 'id' => $model->id]);
                
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }
        
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Báo cáo công nợ nhà cung cấp
     */
    public function actionReport()
    {
        $suppliers = Supplier::find()
            ->select(['id', 'code', 'name', 'debt_amount'])
            ->where(['>', 'debt_amount', 0])
            ->orderBy(['debt_amount' => SORT_DESC])
            ->all();
        
        $totalDebt = Supplier::find()->sum('debt_amount');
        
        return $this->render('report', [
            'suppliers' => $suppliers,
            'totalDebt' => $totalDebt,
        ]);
    }
    
    /**
     * Xuất báo cáo công nợ Excel
     */
    public function actionExport($supplier_id = null, $from_date = null, $to_date = null, $type = null)
    {
        $searchModel = new SupplierDebtSearch();
        $searchModel->supplier_id = $supplier_id;
        $searchModel->transaction_date_from = $from_date;
        $searchModel->transaction_date_to = $to_date;
        $searchModel->type = $type;
        
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination = false; // Lấy tất cả dữ liệu
        
        $models = $dataProvider->getModels();
        
        // Tạo file Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Thiết lập tiêu đề
        $sheet->setCellValue('A1', 'BÁO CÁO CÔNG NỢ NHÀ CUNG CẤP');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
        // Thời gian báo cáo
        $reportDate = 'Thời gian: ';
        if ($from_date && $to_date) {
            $reportDate .= 'Từ ' . Yii::$app->formatter->asDate($from_date) . ' đến ' . Yii::$app->formatter->asDate($to_date);
        } elseif ($from_date) {
            $reportDate .= 'Từ ' . Yii::$app->formatter->asDate($from_date);
        } elseif ($to_date) {
            $reportDate .= 'Đến ' . Yii::$app->formatter->asDate($to_date);
        } else {
            $reportDate .= 'Tất cả thời gian';
        }
        
        $sheet->setCellValue('A2', $reportDate);
        $sheet->mergeCells('A2:G2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
        // Tiêu đề cột
        $sheet->setCellValue('A4', 'STT');
        $sheet->setCellValue('B4', 'Ngày');
        $sheet->setCellValue('C4', 'Nhà cung cấp');
        $sheet->setCellValue('D4', 'Loại');
        $sheet->setCellValue('E4', 'Số tiền');
        $sheet->setCellValue('F4', 'Số dư');
        $sheet->setCellValue('G4', 'Mô tả');
        
        $sheet->getStyle('A4:G4')->getFont()->setBold(true);
        $sheet->getStyle('A4:G4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A4:G4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCCCCC');
        
        // Đổ dữ liệu
        $row = 5;
        foreach ($models as $i => $model) {
            $sheet->setCellValue('A' . $row, $i + 1);
            $sheet->setCellValue('B' . $row, Yii::$app->formatter->asDate($model->transaction_date));
            $sheet->setCellValue('C' . $row, $model->supplier->name);
            $sheet->setCellValue('D' . $row, $model->type == 1 ? 'Nợ' : 'Thanh toán');
            $sheet->setCellValue('E' . $row, $model->amount);
            $sheet->setCellValue('F' . $row, $model->balance);
            $sheet->setCellValue('G' . $row, $model->description);
            
            $row++;
        }
        
        // Định dạng cột tiền tệ
        $sheet->getStyle('E5:F' . ($row - 1))->getNumberFormat()->setFormatCode('#,##0');
        
        // Tự động điều chỉnh độ rộng cột
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Viền bảng
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A4:G' . ($row - 1))->applyFromArray($styleArray);
        
        // Tạo tên file và header cho download
        $filename = 'bao-cao-cong-no-nha-cung-cap-' . date('Ymd') . '.xlsx';
        
        // Tạo writer
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        // Đặt header
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        // Xuất file
        $writer->save('php://output');
        exit;
    }

    /**
     * Finds the SupplierDebt model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return SupplierDebt the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SupplierDebt::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Không tìm thấy dữ liệu công nợ.');
    }
}