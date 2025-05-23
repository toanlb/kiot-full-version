<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\StockIn */
/* @var $warehouses array */
/* @var $suppliers array */
/* @var $products array */
/* @var $units array */

$this->title = 'Tạo phiếu nhập kho';
$this->params['breadcrumbs'][] = ['label' => 'Quản lý nhập kho', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stock-in-create">
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
            ]) ?>
        </div>
    </div>
</div>