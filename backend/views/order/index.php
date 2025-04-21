<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\OrderSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $statusList array */
/* @var $paymentStatusList array */
/* @var $shippingStatusList array */
/* @var $paymentMethods array */
/* @var $warehouses array */

$this->title = 'Quản lý đơn hàng';
$this->params['breadcrumbs'][] = $this->title;

// Lấy các tham số tìm kiếm
$params = Yii::$app->request->queryParams;
$searchCode = $params['OrderSearch']['code'] ?? '';
$searchCustomerName = $params['OrderSearch']['customer_name'] ?? '';
$searchFromDate = $params['OrderSearch']['from_date'] ?? '';
$searchToDate = $params['OrderSearch']['to_date'] ?? '';
$searchStatus = $params['OrderSearch']['status'] ?? '';
$searchPaymentStatus = $params['OrderSearch']['payment_status'] ?? '';
$searchPaymentMethod = $params['OrderSearch']['payment_method_id'] ?? '';
$searchWarehouse = $params['OrderSearch']['warehouse_id'] ?? '';
?>
<div class="order-index">

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
            <div class="card-tools">
                <?= Html::a('<i class="fas fa-sync"></i> Làm mới', ['index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
            </div>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <!-- SEARCH FORM -->
            <form method="get" action="<?= Url::to(['order/index']) ?>" id="order-search-form">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="ordersearch-code">Mã đơn hàng</label>
                            <input type="text" id="ordersearch-code" class="form-control" name="OrderSearch[code]" value="<?= Html::encode($searchCode) ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="ordersearch-customer_name">Khách hàng</label>
                            <input type="text" id="ordersearch-customer_name" class="form-control" name="OrderSearch[customer_name]" value="<?= Html::encode($searchCustomerName) ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="ordersearch-from_date">Từ ngày</label>
                            <input type="date" id="ordersearch-from_date" class="form-control" name="OrderSearch[from_date]" value="<?= Html::encode($searchFromDate) ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="ordersearch-to_date">Đến ngày</label>
                            <input type="date" id="ordersearch-to_date" class="form-control" name="OrderSearch[to_date]" value="<?= Html::encode($searchToDate) ?>">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="ordersearch-status">Trạng thái</label>
                            <select id="ordersearch-status" class="form-control" name="OrderSearch[status]">
                                <option value="">-- Tất cả --</option>
                                <?php foreach ($statusList as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= $searchStatus !== '' && $searchStatus == $value ? 'selected' : '' ?>>
                                        <?= Html::encode($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="ordersearch-payment_status">Trạng thái thanh toán</label>
                            <select id="ordersearch-payment_status" class="form-control" name="OrderSearch[payment_status]">
                                <option value="">-- Tất cả --</option>
                                <?php foreach ($paymentStatusList as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= $searchPaymentStatus !== '' && $searchPaymentStatus == $value ? 'selected' : '' ?>>
                                        <?= Html::encode($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="ordersearch-payment_method_id">Phương thức thanh toán</label>
                            <select id="ordersearch-payment_method_id" class="form-control" name="OrderSearch[payment_method_id]">
                                <option value="">-- Tất cả --</option>
                                <?php foreach ($paymentMethods as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= $searchPaymentMethod !== '' && $searchPaymentMethod == $value ? 'selected' : '' ?>>
                                        <?= Html::encode($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="ordersearch-warehouse_id">Kho hàng</label>
                            <select id="ordersearch-warehouse_id" class="form-control" name="OrderSearch[warehouse_id]">
                                <option value="">-- Tất cả --</option>
                                <?php foreach ($warehouses as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= $searchWarehouse !== '' && $searchWarehouse == $value ? 'selected' : '' ?>>
                                        <?= Html::encode($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                            <button type="reset" class="btn btn-default" id="reset-search">Xóa tìm kiếm</button>
                        </div>
                    </div>
                </div>
            </form>
            
            <!-- ORDERS TABLE -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="width: 50px;">STT</th>
                            <th>Mã đơn hàng</th>
                            <th>Khách hàng</th>
                            <th>Ngày đặt</th>
                            <th class="text-right">Tổng tiền</th>
                            <th>TT Thanh toán</th>
                            <th>Trạng thái</th>
                            <th style="width: 150px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $models = $dataProvider->getModels();
                        $pagination = $dataProvider->getPagination();
                        $start = $pagination->getOffset();
                        
                        if (count($models) > 0):
                            foreach ($models as $index => $model): 
                                $badgePaymentStatus = '';
                                if ($model->payment_status == 0) {
                                    $badgePaymentStatus = 'badge-secondary';
                                } elseif ($model->payment_status == 1) {
                                    $badgePaymentStatus = 'badge-warning';
                                } elseif ($model->payment_status == 2) {
                                    $badgePaymentStatus = 'badge-success';
                                }
                                
                                $badgeStatus = '';
                                if ($model->status == 0) {
                                    $badgeStatus = 'badge-secondary';
                                } elseif ($model->status == 1) {
                                    $badgeStatus = 'badge-primary';
                                } elseif ($model->status == 2) {
                                    $badgeStatus = 'badge-info';
                                } elseif ($model->status == 3) {
                                    $badgeStatus = 'badge-warning';
                                } elseif ($model->status == 4) {
                                    $badgeStatus = 'badge-success';
                                } elseif ($model->status == 5) {
                                    $badgeStatus = 'badge-danger';
                                }
                            ?>
                            <tr>
                                <td><?= $start + $index + 1 ?></td>
                                <td>
                                    <a href="<?= Url::to(['order/view', 'id' => $model->id]) ?>">
                                        <?= Html::encode($model->code) ?>
                                    </a>
                                </td>
                                <td><?= $model->customer ? Html::encode($model->customer->name) : 'Khách lẻ' ?></td>
                                <td><?= Yii::$app->formatter->asDatetime($model->order_date, 'php:d/m/Y H:i') ?></td>
                                <td class="text-right"><?= Yii::$app->formatter->asDecimal($model->total_amount) ?></td>
                                <td>
                                    <span class="badge <?= $badgePaymentStatus ?>">
                                        <?= $paymentStatusList[$model->payment_status] ?? 'Unknown' ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?= $badgeStatus ?>">
                                        <?= $statusList[$model->status] ?? 'Unknown' ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= Url::to(['order/view', 'id' => $model->id]) ?>" class="btn btn-sm btn-info" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= Url::to(['order/print-invoice', 'id' => $model->id]) ?>" class="btn btn-sm btn-secondary" title="In hóa đơn" target="_blank">
                                        <i class="fas fa-print"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">Không tìm thấy đơn hàng nào</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- PAGINATION -->
            <div class="row">
                <div class="col-md-6">
                    <div class="summary">
                        Hiển thị <b><?= $dataProvider->getCount() ?></b> trên <b><?= $dataProvider->getTotalCount() ?></b> đơn hàng.
                    </div>
                </div>
                <div class="col-md-6">
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-end">
                            <?php
                            $pages = $dataProvider->getPagination();
                            $pageCount = $pages->getPageCount();
                            $currentPage = $pages->getPage() + 1;
                            
                            // Previous button
                            if ($currentPage > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= Url::to(['order/index'] + $params + ['page' => $currentPage - 1]) ?>">
                                        &laquo;
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link">&laquo;</span>
                                </li>
                            <?php endif;
                            
                            // Page numbers
                            $startPage = max(1, $currentPage - 2);
                            $endPage = min($pageCount, $currentPage + 2);
                            
                            for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                    <?php if ($i == $currentPage): ?>
                                        <span class="page-link"><?= $i ?></span>
                                    <?php else: ?>
                                        <a class="page-link" href="<?= Url::to(['order/index'] + $params + ['page' => $i]) ?>">
                                            <?= $i ?>
                                        </a>
                                    <?php endif; ?>
                                </li>
                            <?php endfor;
                            
                            // Next button
                            if ($currentPage < $pageCount): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= Url::to(['order/index'] + $params + ['page' => $currentPage + 1]) ?>">
                                        &raquo;
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link">&raquo;</span>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
        <!-- /.card-body -->
    </div>
</div>

<?php
$script = <<<JS
// Reset search form
$('#reset-search').on('click', function(e) {
    e.preventDefault();
    $('#order-search-form input, #order-search-form select').val('');
    $('#order-search-form').submit();
});
JS;
$this->registerJs($script);
?>