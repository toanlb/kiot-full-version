<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Warranty */
/* @var $products array */
/* @var $customers array */
/* @var $statuses array */

$this->title = 'Create Warranty';
$this->params['breadcrumbs'][] = ['label' => 'Warranties', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="warranty-create">
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