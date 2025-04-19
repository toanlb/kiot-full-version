<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Product */
/* @var $comboItems array */
/* @var $categories array */
/* @var $units array */

$this->title = 'Tạo Sản Phẩm Combo Mới';
$this->params['breadcrumbs'][] = ['label' => 'Sản phẩm Combo', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-combo-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'comboItems' => $comboItems,
        'categories' => $categories,
        'units' => $units,
    ]) ?>

</div>