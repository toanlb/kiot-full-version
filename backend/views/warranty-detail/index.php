<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\WarrantyDetailSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $statuses array */

$this->title = 'Warranty Service Records';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="warranty-detail-index card">
    <div class="card-header">
        <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
        <div class="card-tools">
            <?= Html::a('<i class="fas fa-plus"></i> Create New Service Record', ['create'], ['class' => 'btn btn-success btn-sm']) ?>
        </div>
    </div>

    <div class="card-body">
        <!-- Search Form -->
        <div class="warranty-detail-search mb-4">
            <form id="searchForm" action="<?= Url::to(['index']) ?>" method="get">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Warranty Code</label>
                            <input type="text" name="WarrantyDetailSearch[warranty_code]" class="form-control" value="<?= Html::encode($searchModel->warranty_code) ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Product</label>
                            <input type="text" name="WarrantyDetailSearch[product_name]" class="form-control" value="<?= Html::encode($searchModel->product_name) ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Service Date</label>
                            <div class="input-group">
                                <input type="text" name="WarrantyDetailSearch[service_date]" id="service-date-picker" class="form-control datepicker" value="<?= Html::encode($searchModel->service_date) ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="WarrantyDetailSearch[status_id]" class="form-control select2">
                                <option value="">All</option>
                                <?php foreach ($statuses as $id => $name): ?>
                                    <option value="<?= $id ?>" <?= $searchModel->status_id == $id ? 'selected' : '' ?>>
                                        <?= Html::encode($name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Technician</label>
                            <select name="WarrantyDetailSearch[handled_by]" class="form-control select2">
                                <option value="">All</option>
                                <?php 
                                $technicians = \common\models\User::find()->all();
                                foreach ($technicians as $technician): 
                                ?>
                                    <option value="<?= $technician->id ?>" <?= $searchModel->handled_by == $technician->id ? 'selected' : '' ?>>
                                        <?= Html::encode($technician->full_name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Charged</label>
                            <select name="WarrantyDetailSearch[is_charged]" class="form-control select2">
                                <option value="">All</option>
                                <option value="1" <?= $searchModel->is_charged === '1' ? 'selected' : '' ?>>Yes</option>
                                <option value="0" <?= $searchModel->is_charged === '0' ? 'selected' : '' ?>>No</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Description/Diagnosis</label>
                            <input type="text" name="WarrantyDetailSearch[description]" class="form-control" value="<?= Html::encode($searchModel->description) ?>">
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-group mb-0 w-100">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-secondary ml-2">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => null, // Disable default filters since we're using custom form
            'layout' => "{summary}\n{items}\n<div class='clearfix'></div>\n<div class='row'><div class='col-sm-12 col-md-5'><div class='dataTables_info'>{summary}</div></div><div class='col-sm-12 col-md-7'><div class='dataTables_paginate paging_simple_numbers'>{pager}</div></div></div>",
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                [
                    'attribute' => 'warranty_code',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return Html::a(
                            Html::encode($model->warranty->code ?? 'N/A'),
                            ['/warranty/view', 'id' => $model->warranty_id],
                            ['title' => 'View Warranty']
                        );
                    },
                    'label' => 'Warranty',
                ],
                [
                    'attribute' => 'product_name',
                    'value' => function ($model) {
                        return $model->warranty->product->name ?? 'N/A';
                    },
                    'label' => 'Product',
                ],
                [
                    'attribute' => 'service_date',
                    'format' => ['datetime', 'php:d/m/Y H:i'],
                ],
                [
                    'attribute' => 'status_name',
                    'value' => function ($model) {
                        return '<span class="badge" style="background-color: ' . 
                               ($model->status->color ?? '#777') . 
                               ';">' . ($model->status->name ?? 'Unknown') . '</span>';
                    },
                    'format' => 'raw',
                    'label' => 'Status',
                ],
                [
                    'attribute' => 'handled_by_name',
                    'value' => function ($model) {
                        return $model->handler->full_name ?? 'N/A';
                    },
                    'label' => 'Technician',
                ],
                [
                    'attribute' => 'total_cost',
                    'value' => function ($model) {
                        return Yii::$app->formatter->asCurrency($model->total_cost);
                    },
                ],
                [
                    'attribute' => 'is_charged',
                    'value' => function ($model) {
                        return $model->is_charged ? 
                            '<span class="badge badge-info">Yes</span>' : 
                            '<span class="badge badge-success">No (Under Warranty)</span>';
                    },
                    'format' => 'raw',
                    'label' => 'Charged',
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view} {update} {delete} {print}',
                    'buttons' => [
                        'print' => function ($url, $model, $key) {
                            return Html::a(
                                '<i class="fas fa-print"></i>', 
                                ['print', 'id' => $model->id], 
                                [
                                    'title' => 'Print', 
                                    'class' => 'btn btn-sm btn-outline-info',
                                    'target' => '_blank'
                                ]
                            );
                        },
                        'view' => function ($url, $model, $key) {
                            return Html::a(
                                '<i class="fas fa-eye"></i>', 
                                $url, 
                                ['title' => 'View', 'class' => 'btn btn-sm btn-outline-primary']
                            );
                        },
                        'update' => function ($url, $model, $key) {
                            return Html::a(
                                '<i class="fas fa-edit"></i>', 
                                $url, 
                                ['title' => 'Update', 'class' => 'btn btn-sm btn-outline-success']
                            );
                        },
                        'delete' => function ($url, $model, $key) {
                            return Html::a(
                                '<i class="fas fa-trash"></i>', 
                                $url, 
                                [
                                    'title' => 'Delete', 
                                    'class' => 'btn btn-sm btn-outline-danger',
                                    'data' => [
                                        'confirm' => 'Are you sure you want to delete this service record?',
                                        'method' => 'post',
                                    ],
                                ]
                            );
                        },
                    ],
                    'contentOptions' => ['class' => 'text-center'],
                ],
            ],
            'tableOptions' => ['class' => 'table table-striped table-bordered'],
            'options' => ['class' => 'grid-view table-responsive'],
            'pager' => [
                'options' => ['class' => 'pagination justify-content-center'],
                'linkContainerOptions' => ['class' => 'page-item'],
                'linkOptions' => ['class' => 'page-link'],
                'disabledListItemSubTagOptions' => ['tag' => 'a', 'class' => 'page-link'],
            ],
        ]); ?>
    </div>
</div>

<?php
$js = <<<JS
    // Initialize select2 for dropdown fields
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
        
        // Initialize datepicker
        $('#service-date-picker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true
        });
    });
JS;
$this->registerJs($js);
?>