<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\WarrantyDetail */
/* @var $warranties array */
/* @var $statuses array */
/* @var $replacementProducts array */

$this->title = 'Create Service Record';
$this->params['breadcrumbs'][] = ['label' => 'Warranty Service Records', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$warranty_id = Yii::$app->request->get('warranty_id');
if ($warranty_id && isset($warranties[$warranty_id])) {
    $this->title = 'Create Service Record for ' . $warranties[$warranty_id];
}
?>
<div class="warranty-detail-create">
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