<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\Supplier */
/* @var $supplierProducts common\models\SupplierProduct[] */
/* @var $stockInHistory common\models\StockIn[] */
/* @var $debtHistory common\models\SupplierDebt[] */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Quản lý nhà cung cấp', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="supplier-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('<i class="fas fa-edit"></i> Cập nhật', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('<i class="fas fa-trash"></i> Xóa', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Bạn có chắc chắn muốn xóa nhà cung cấp này?',
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a('<i class="fas fa-boxes"></i> Quản lý sản phẩm', ['product', 'id' => $model->id], ['class' => 'btn btn-info']) ?>
        <?= Html::a('<i class="fas fa-hand-holding-usd"></i> Thanh toán công nợ', ['/supplier-debt/payment', 'supplier_id' => $model->id], ['class' => 'btn btn-success']) ?>
        <?= Html::a('<i class="fas fa-file-invoice"></i> Lịch sử công nợ', ['/supplier-debt/index', 'SupplierDebtSearch[supplier_id]' => $model->id], ['class' => 'btn btn-warning']) ?>
        <?= Html::a('<i class="fas fa-arrow-left"></i> Quay lại', ['index'], ['class' => 'btn btn-secondary']) ?>
    </p>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">Thông tin cơ bản</h3>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'code',
                            'name',
                            'phone',
                            'email:email',
                            'website',
                            'tax_code',
                            [
                                'attribute' => 'status',
                                'format' => 'raw',
                                'value' => $model->getStatusLabel(),
                            ],
                            'contact_person',
                            'contact_phone',
                            [
                                'attribute' => 'debt_amount',
                                'format' => 'currency',
                            ],
                            [
                                'attribute' => 'credit_limit',
                                'format' => 'currency',
                            ],
                            'payment_term',
                            'created_at:datetime',
                            'updated_at:datetime',
                            [
                                'attribute' => 'created_by',
                                'value' => $model->createdBy ? $model->createdBy->username : 'N/A',
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h3 class="card-title">Địa chỉ</h3>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'address',
                            [
                                'attribute' => 'province_id',
                                'value' => $model->province ? $model->province->name : null,
                            ],
                            [
                                'attribute' => 'district_id',
                                'value' => $model->district ? $model->district->name : null,
                            ],
                            [
                                'attribute' => 'ward_id',
                                'value' => $model->ward ? $model->ward->name : null,
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header bg-success text-white">
                    <h3 class="card-title">Thông tin ngân hàng</h3>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'bank_name',
                            'bank_account',
                            'bank_account_name',
                        ],
                    ]) ?>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header bg-warning">
                    <h3 class="card-title">Thống kê</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-boxes"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Sản phẩm</span>
                                    <span class="info-box-number"><?= $model->getProductCount() ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-file-invoice"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Đơn nhập hàng</span>
                                    <span class="info-box-number"><?= $model->getStockInCount() ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">Sản phẩm của nhà cung cấp</h3>
                    <div class="card-tools">
                        <?= Html::a('<i class="fas fa-plus"></i> Quản lý sản phẩm', ['product', 'id' => $model->id], ['class' => 'btn btn-success btn-sm']) ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Mã sản phẩm</th>
                                    <th>Tên sản phẩm</th>
                                    <th>Mã NCC</th>
                                    <th>Tên NCC</th>
                                    <th>Giá nhập</th>
                                    <th>SL tối thiểu</th>
                                    <th>NCC chính</th>
                                    <th>Ngày nhập cuối</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($supplierProducts as $i => $product): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><?= $product->product->code ?></td>
                                        <td><?= $product->product->name ?></td>
                                        <td><?= $product->supplier_product_code ?></td>
                                        <td><?= $product->supplier_product_name ?></td>
                                        <td class="text-right"><?= Yii::$app->formatter->asCurrency($product->unit_price) ?></td>
                                        <td class="text-center"><?= $product->min_order_quantity ?></td>
                                        <td class="text-center">
                                            <?= $product->is_primary_supplier ? '<span class="badge badge-success"><i class="fas fa-check"></i></span>' : '' ?>
                                        </td>
                                        <td><?= $product->last_purchase_date ? Yii::$app->formatter->asDate($product->last_purchase_date) : '-' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($supplierProducts)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center">Chưa có sản phẩm nào</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h3 class="card-title">Lịch sử nhập hàng gần đây</h3>
                    <div class="card-tools">
                        <?= Html::a('Xem tất cả', ['/stock-in/index', 'StockInSearch[supplier_id]' => $model->id], ['class' => 'btn btn-outline-light btn-sm']) ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Mã phiếu</th>
                                    <th>Ngày nhập</th>
                                    <th>Kho</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stockInHistory as $stockIn): ?>
                                    <tr>
                                        <td>
                                            <a href="<?= Url::to(['/stock-in/view', 'id' => $stockIn->id]) ?>">
                                                <?= $stockIn->code ?>
                                            </a>
                                        </td>
                                        <td><?= Yii::$app->formatter->asDate($stockIn->stock_in_date) ?></td>
                                        <td><?= $stockIn->warehouse->name ?></td>
                                        <td class="text-right"><?= Yii::$app->formatter->asCurrency($stockIn->final_amount) ?></td>
                                        <td>
                                            <?php
                                            $statusLabels = [
                                                0 => '<span class="badge badge-secondary">Nháp</span>',
                                                1 => '<span class="badge badge-info">Đã xác nhận</span>',
                                                2 => '<span class="badge badge-success">Hoàn thành</span>',
                                                3 => '<span class="badge badge-danger">Đã hủy</span>',
                                            ];
                                            echo $statusLabels[$stockIn->status] ?? '';
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($stockInHistory)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Chưa có lịch sử nhập hàng</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-warning">
                    <h3 class="card-title">Lịch sử công nợ gần đây</h3>
                    <div class="card-tools">
                        <?= Html::a('Xem tất cả', ['/supplier-debt/index', 'SupplierDebtSearch[supplier_id]' => $model->id], ['class' => 'btn btn-outline-dark btn-sm']) ?>
                        </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Ngày</th>
                                    <th>Loại</th>
                                    <th>Số tiền</th>
                                    <th>Số dư</th>
                                    <th>Mô tả</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($debtHistory as $debt): ?>
                                    <tr>
                                        <td><?= Yii::$app->formatter->asDate($debt->transaction_date) ?></td>
                                        <td>
                                            <?php
                                            $typeLabels = [
                                                1 => '<span class="badge badge-danger">Nợ</span>',
                                                2 => '<span class="badge badge-success">Thanh toán</span>',
                                            ];
                                            echo $typeLabels[$debt->type] ?? '';
                                            ?>
                                        </td>
                                        <td class="text-right"><?= Yii::$app->formatter->asCurrency($debt->amount) ?></td>
                                        <td class="text-right"><?= Yii::$app->formatter->asCurrency($debt->balance) ?></td>
                                        <td><?= $debt->description ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($debtHistory)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Chưa có lịch sử công nợ</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>