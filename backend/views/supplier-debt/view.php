<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\SupplierDebt */

$this->title = 'Chi tiết công nợ #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Công nợ nhà cung cấp', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="supplier-debt-view card">
    <div class="card-header">
        <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
        <div class="card-tools">
            <?= Html::a('<i class="fas fa-arrow-left"></i> Quay lại', ['index'], ['class' => 'btn btn-secondary btn-sm']) ?>
        </div>
    </div>
    <div class="card-body">
        <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
                'id',
                [
                    'attribute' => 'supplier_id',
                    'value' => $model->supplier->name . ' (' . $model->supplier->code . ')',
                ],
                [
                    'attribute' => 'type',
                    'value' => $model->getTypeLabel(),
                    'format' => 'raw',
                ],
                [
                    'attribute' => 'amount',
                    'value' => Yii::$app->formatter->asCurrency($model->amount),
                ],
                [
                    'attribute' => 'balance',
                    'value' => Yii::$app->formatter->asCurrency($model->balance),
                ],
                [
                    'attribute' => 'transaction_date',
                    'format' => 'datetime',
                ],
                'description:ntext',
                [
                    'attribute' => 'reference_type',
                    'value' => $model->getReferenceLabel(),
                    'format' => 'raw',
                ],
                [
                    'attribute' => 'created_at',
                    'format' => 'datetime',
                ],
                [
                    'attribute' => 'created_by',
                    'value' => $model->createdBy ? $model->createdBy->username : null,
                ],
            ],
        ]) ?>
    </div>
</div>