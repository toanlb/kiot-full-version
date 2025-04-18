<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Warehouse */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Quản lý Kho hàng', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="warehouse-view">

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Chi tiết kho hàng: <?= Html::encode($this->title) ?></h3>
            <div class="card-tools">
                <?= Html::a('<i class="fas fa-edit"></i> Cập nhật', ['update', 'id' => $model->id], ['class' => 'btn btn-primary btn-sm']) ?>
                <?= Html::a('<i class="fas fa-trash"></i> Xóa', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger btn-sm',
                    'data' => [
                        'confirm' => 'Bạn có chắc chắn muốn xóa kho hàng này?',
                        'method' => 'post',
                    ],
                ]) ?>
                <?= Html::a('<i class="fas fa-arrow-left"></i> Quay lại', ['index'], ['class' => 'btn btn-secondary btn-sm']) ?>
            </div>
        </div>
        <div class="card-body">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    'code',
                    'name',
                    'address:ntext',
                    'phone',
                    [
                        'attribute' => 'manager_id',
                        'value' => $model->manager ? $model->manager->full_name : 'Chưa phân công',
                    ],
                    [
                        'attribute' => 'is_default',
                        'format' => 'raw',
                        'value' => $model->is_default ? '<span class="badge badge-success">Có</span>' : '<span class="badge badge-secondary">Không</span>',
                    ],
                    [
                        'attribute' => 'is_active',
                        'format' => 'raw',
                        'value' => $model->is_active ? '<span class="badge badge-success">Kích hoạt</span>' : '<span class="badge badge-danger">Tạm khóa</span>',
                    ],
                    'description:ntext',
                    [
                        'attribute' => 'created_at',
                        'format' => ['date', 'php:d/m/Y H:i:s'],
                    ],
                    [
                        'attribute' => 'updated_at',
                        'format' => ['date', 'php:d/m/Y H:i:s'],
                    ],
                    [
                        'attribute' => 'created_by',
                        'value' => $model->createdBy ? $model->createdBy->full_name : null,
                    ],
                    [
                        'attribute' => 'updated_by',
                        'value' => $model->updatedBy ? $model->updatedBy->full_name : null,
                    ],
                ],
            ]) ?>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">Thông tin tồn kho hiện tại</h3>
        </div>
        <div class="card-body">
            <?php
            $stockItems = $model->getStocks()->with('product')->all();
            if (count($stockItems) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Mã sản phẩm</th>
                                <th>Tên sản phẩm</th>
                                <th>Số lượng tồn</th>
                                <th>Mức tồn tối thiểu</th>
                                <th>Trạng thái</th>
                                <th>Cập nhật lần cuối</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stockItems as $index => $stock): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= $stock->product->code ?></td>
                                    <td><?= $stock->product->name ?></td>
                                    <td><?= $stock->quantity ?></td>
                                    <td><?= $stock->min_stock ?? $stock->product->min_stock ?></td>
                                    <td>
                                        <?php
                                        $minStock = $stock->min_stock ?? $stock->product->min_stock;
                                        if ($stock->quantity <= 0) {
                                            echo '<span class="badge badge-danger">Hết hàng</span>';
                                        } elseif ($minStock && $stock->quantity <= $minStock) {
                                            echo '<span class="badge badge-warning">Sắp hết</span>';
                                        } else {
                                            echo '<span class="badge badge-success">Bình thường</span>';
                                        }
                                        ?>
                                    </td>
                                    <td><?= Yii::$app->formatter->asDatetime($stock->updated_at) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Chưa có dữ liệu tồn kho cho kho hàng này.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>