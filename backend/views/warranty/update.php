<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Warranty */
/* @var $products array */
/* @var $customers array */
/* @var $statuses array */

$this->title = 'Update Warranty: ' . $model->code;
$this->params['breadcrumbs'][] = ['label' => 'Warranties', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->code, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="warranty-update">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
        </div>
        <div class="card-body">
            <?= $this->render('_form', [
                'model' => $model,
                'products' => $products,
                'customers' => $customers,
                'statuses' => $statuses,
            ]) ?>
        </div>
    </div>
</div>