<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\WarrantyStatus */

$this->title = 'Create Warranty Status';
$this->params['breadcrumbs'][] = ['label' => 'Warranty Statuses', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="warranty-status-create">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
        </div>
        <div class="card-body">
            <?= $this->render('_form', [
                'model' => $model,
            ]) ?>
        </div>
    </div>
</div>