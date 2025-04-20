<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\SupplierDebt */

$this->title = 'Chi tiết công nợ #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Quản lý công nợ nhà cung cấp', 'url' => ['index']];
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
                    'value' => function ($model) {
                        return $model->supplier->code . ' - ' . $model->supplier->name;
                    },
                    'format' => 'raw',
                ],
                [
                    'attribute' => 'type',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return $model->getTypeLabel();
                    },
                ],
                [
                    'attribute' => 'amount',
                    'format' => 'currency',
                ],
                [
                    'attribute' => 'balance',
                    'format' => 'currency',
                ],
                'description:ntext',
                'transaction_date:datetime',
                [
                    'attribute' => 'reference_type',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return $model->getReferenceInfo();
                    },
                ],
                'created_at:datetime',
                [
                    'attribute' => 'created_by',
                    'value' => function ($model) {
                        return $model->createdBy ? $model->createdBy->username : 'N/A';
                    },
                ],
            ],
        ]) ?>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title">Thông tin nhà cung cấp</h3>
    </div>
    <div class="card-body">
        <?= DetailView::widget([
            'model' => $model->supplier,
            'attributes' => [
                'code',
                'name',
                'phone',
                'email:email',
                [
                    'attribute' => 'debt_amount',
                    'format' => 'currency',
                ],
                [
                    'attribute' => 'credit_limit',
                    'format' => 'currency',
                ],
                'payment_term',
            ],
        ]) ?>
        
        <div class="mt-3">
            <?= Html::a('<i class="fas fa-user"></i> Xem nhà cung cấp', ['/supplier/view', 'id' => $model->supplier_id], ['class' => 'btn btn-info']) ?>
            <?= Html::a('<i class="fas fa-money-bill-wave"></i> Thanh toán công nợ', ['payment', 'supplier_id' => $model->supplier_id], ['class' => 'btn btn-success']) ?>
        </div>
    </div>
</div>