<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\WarrantyDetail */
/* @var $warranties array */
/* @var $statuses array */
/* @var $replacementProducts array */

$this->title = 'Update Service Record: #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Warranty Service Records', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => '#' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="warranty-detail-update">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
        </div>
        <div class="card-body">
            <?= $this->render('_form', [
                'model' => $model,
                'warranties' => $warranties,
                'statuses' => $statuses,
                'replacementProducts' => $replacementProducts,
            ]) ?>
        </div>
    </div>
</div>