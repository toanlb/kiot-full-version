<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Product */
/* @var $comboItems common\models\ProductCombo[] */
/* @var $categories array */
/* @var $units array */

$this->title = 'Cập Nhật Sản Phẩm Combo: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Sản phẩm Combo', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Cập nhật';
?>
<div class="product-combo-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'comboItems' => $comboItems,
        'categories' => $categories,
        'units' => $units,
    ]) ?>

</div>