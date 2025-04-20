<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use kartik\date\DatePicker;
use yii\helpers\ArrayHelper;
use common\models\Supplier;

/* @var $this yii\web\View */
/* @var $searchModel common\models\SupplierDebtSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Quản lý công nợ nhà cung cấp';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="supplier-debt-index card">
    <div class="card-header">
        <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
        <div class="card-tools">
            <?= Html::a('<i class="fas fa-plus"></i> Thêm công nợ', ['create'], ['class' => 'btn btn-success btn-sm']) ?>
            <?= Html::a('<i class="fas fa-money-bill-wave"></i> Thanh toán công nợ', ['payment'], ['class' => 'btn btn-primary btn-sm']) ?>
            <?= Html::a('<i class="fas fa-chart-pie"></i> Báo cáo công nợ', ['report'], ['class' => 'btn btn-info btn-sm']) ?>
            <?= Html::a('<i class="fas fa-file-excel"></i> Xuất Excel', ['export'], ['class' => 'btn btn-warning btn-sm']) ?>
        </div>
    </div>

    <div class="card-body">
        <?php Pjax::begin(); ?>
        
        <div class="search-form">
            <?= $this->render('_search', ['model' => $searchModel]); ?>
        </div>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'layout' => "{summary}\n{items}\n<div class='card-footer clearfix'>{pager}</div>",
            'options' => ['class' => 'grid-view table-responsive'],
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],

                [
                    'attribute' => 'transaction_date',
                    'format' => 'datetime',
                ],
                [
                    'attribute' => 'supplier_id',
                    'value' => function ($model) {
                        return $model->supplier->name;
                    },
                    'format' => 'raw',
                    'contentOptions' => function ($model) {
                        return ['class' => 'text-nowrap'];
                    },
                ],
                [
                    'attribute' => 'type',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return $model->getTypeLabel();
                    },
                    'contentOptions' => ['class' => 'text-center'],
                ],
                [
                    'attribute' => 'amount',
                    'format' => 'currency',
                    'contentOptions' => ['class' => 'text-right'],
                ],
                [
                    'attribute' => 'balance',
                    'format' => 'currency',
                    'contentOptions' => ['class' => 'text-right'],
                ],
                [
                    'attribute' => 'description',
                    'format' => 'ntext',
                    'contentOptions' => ['class' => 'text-truncate', 'style' => 'max-width: 200px;'],
                ],
                [
                    'attribute' => 'reference_type',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return $model->getReferenceInfo();
                    },
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view}',
                    'buttons' => [
                        'view' => function ($url, $model, $key) {
                            return Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $model->id], [
                                'title' => 'Xem chi tiết',
                                'data-toggle' => 'tooltip',
                                'class' => 'btn btn-sm btn-primary',
                            ]);
                        },
                    ],
                    'contentOptions' => ['class' => 'text-center'],
                ],
            ],
        ]); ?>

        <?php Pjax::end(); ?>
    </div>
</div>
