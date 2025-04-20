<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\StockIn */
/* @var $warehouses array */
/* @var $suppliers array */
/* @var $products array */
/* @var $units array */
/* @var $details common\models\StockInDetail[] */

$this->title = 'Cập nhật phiếu nhập kho: ' . $model->code;
$this->params['breadcrumbs'][] = ['label' => 'Quản lý nhập kho', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->code, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Cập nhật';
?>
<div class="stock-in-update">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
        </div>
        <div class="card-body">
            <?= $this->render('_form', [
                'model' => $model,
                'warehouses' => $warehouses,
                'suppliers' => $suppliers,
                'products' => $products,
                'units' => $units,
                'details' => $details,
            ]) ?>
        </div>
    </div>
</div>