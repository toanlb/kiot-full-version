<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\SupplierDebt */

$this->title = 'Thêm công nợ nhà cung cấp';
$this->params['breadcrumbs'][] = ['label' => 'Công nợ nhà cung cấp', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="supplier-debt-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>