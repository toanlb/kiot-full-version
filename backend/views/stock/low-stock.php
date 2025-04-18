<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;

$this->title = 'Danh sách hàng sắp hết';
$this->params['breadcrumbs'][] = ['label' => 'Tồn kho', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$js = '
// Lọc kho hàng
$("#warehouse-filter").on("change", function() {
    var warehouseId = $(this).val();
    var url = "' . Url::to(['low-stock']) . '";
    
    if (warehouseId) {
        url += "?warehouse_id=" + warehouseId;
    }
    
    window.location.href = url;
});
';

$this->registerJs($js);
?>

<div class="stock-low-stock">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
            <div class="card-tools">
                <div class="input-group input-group-sm" style="width: 250px;">
                    <?= Html::dropDownList('warehouse_id', Yii::$app->request->get('warehouse_id'), 
                        array_merge(['' => 'Tất cả kho hàng'], $warehouses), 
                        ['id' => 'warehouse-filter', 'class' => 'form-control']
                    ) ?>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php Pjax::begin(); ?>
            <?php if (empty($stocks)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Không có sản phẩm nào sắp hết.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kho hàng</th>
                                <th>Mã sản phẩm</th>
                                <th>Tên sản phẩm</th>
                                <th>Tồn kho</th>
                                <th>Tồn tối thiểu</th>
                                <th>Cần nhập thêm</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stocks as $index => $stock): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= $stock['warehouse_name'] ?></td>
                                    <td><?= $stock['product_code'] ?></td>
                                    <td><?= $stock['product_name'] ?></td>
                                    <td class="text-danger">
                                        <strong><?= $stock['quantity'] ?> <?= $stock['unit_name'] ?></strong>
                                    </td>
                                    <td>
                                        <?= $stock['min_stock'] ?? '-' ?> <?= $stock['unit_name'] ?>
                                    </td>
                                    <td>
                                        <?php
                                        $minStock = $stock['min_stock'] ?: 0;
                                        $needToOrder = $minStock - $stock['quantity'];
                                        echo $needToOrder > 0 ? $needToOrder . ' ' . $stock['unit_name'] : '-';
                                        ?>
                                    </td>
                                    <td>
                                        <?= Html::a('<i class="fas fa-eye"></i>', ['view', 'product_id' => $stock['product_id'], 'warehouse_id' => $stock['warehouse_id']], [
                                            'class' => 'btn btn-primary btn-sm',
                                            'title' => 'Xem chi tiết',
                                        ]) ?>
                                        <?= Html::a('<i class="fas fa-plus"></i>', ['/stock-in/create', 'product_id' => $stock['product_id'], 'warehouse_id' => $stock['warehouse_id']], [
                                            'class' => 'btn btn-success btn-sm',
                                            'title' => 'Tạo phiếu nhập',
                                        ]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            <?php Pjax::end(); ?>
        </div>
    </div>
</div>