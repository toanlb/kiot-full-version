<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\WarrantySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $statuses array */

$this->title = 'Warranty Management';
$this->params['breadcrumbs'][] = $this->title;

$activeStatusColors = [];
foreach ($statuses as $id => $name) {
    $statusModel = \common\models\WarrantyStatus::findOne($id);
    if ($statusModel) {
        $activeStatusColors[$id] = $statusModel->color;
    }
}
?>
<div class="warranty-index card">
    <div class="card-header">
        <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
        <div class="card-tools">
            <?= Html::a('<i class="fas fa-plus"></i> Create Warranty', ['create'], ['class' => 'btn btn-success btn-sm']) ?>
            <?= Html::a('<i class="fas fa-chart-bar"></i> Warranty Report', ['report'], ['class' => 'btn btn-info btn-sm']) ?>
        </div>
    </div>

    <div class="card-body">
        <!-- Search Form -->
        <div class="warranty-search mb-4">
            <form id="searchForm" action="<?= Url::to(['index']) ?>" method="get">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Warranty Code</label>
                            <input type="text" name="WarrantySearch[code]" class="form-control" value="<?= Html::encode($searchModel->code) ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Product</label>
                            <input type="text" name="WarrantySearch[product_name]" class="form-control" value="<?= Html::encode($searchModel->product_name) ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Customer</label>
                            <input type="text" name="WarrantySearch[customer_name]" class="form-control" value="<?= Html::encode($searchModel->customer_name) ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Serial Number</label>
                            <input type="text" name="WarrantySearch[serial_number]" class="form-control" value="<?= Html::encode($searchModel->serial_number) ?>">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Date Range</label>
                            <div class="input-group">
                                <input type="text" name="WarrantySearch[date_range]" id="warranty-date-range" class="form-control" value="<?= Html::encode($searchModel->date_range) ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="WarrantySearch[status_id]" class="form-control select2">
                                <option value="">All</option>
                                <?php foreach ($statuses as $id => $name): ?>
                                    <option value="<?= $id ?>" <?= $searchModel->status_id == $id ? 'selected' : '' ?>>
                                        <?= Html::encode($name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Active</label>
                            <select name="WarrantySearch[active]" class="form-control select2">
                                <option value="">All</option>
                                <option value="1" <?= $searchModel->active === '1' ? 'selected' : '' ?>>Active</option>
                                <option value="0" <?= $searchModel->active === '0' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-group mb-0 w-100">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                </div>
            </form>
            
            <div class="mt-2">
                <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </div>
        </div>
        
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => null, // Disable default filters since we're using custom form
            'layout' => "{summary}\n{items}\n<div class='clearfix'></div>\n<div class='row'><div class='col-sm-12 col-md-5'><div class='dataTables_info'>{summary}</div></div><div class='col-sm-12 col-md-7'><div class='dataTables_paginate paging_simple_numbers'>{pager}</div></div></div>",
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                'code',
                [
                    'attribute' => 'product_name',
                    'value' => 'product.name',
                    'label' => 'Product',
                ],
                [
                    'attribute' => 'customer_name',
                    'value' => 'customer.name',
                    'label' => 'Customer',
                ],
                'serial_number',
                [
                    'attribute' => 'start_date',
                    'format' => ['date', 'php:d/m/Y'],
                    'label' => 'Start Date',
                ],
                [
                    'attribute' => 'end_date',
                    'format' => ['date', 'php:d/m/Y'],
                    'label' => 'End Date',
                ],
                [
                    'attribute' => 'status_name',
                    'value' => function ($model) use ($activeStatusColors) {
                        return '<span class="badge" style="background-color: ' . 
                               ($activeStatusColors[$model->status_id] ?? '#777') . 
                               ';">' . $model->status->name . '</span>';
                    },
                    'format' => 'raw',
                    'label' => 'Status',
                ],
                [
                    'attribute' => 'active',
                    'value' => function ($model) {
                        return $model->active ? 
                            '<span class="badge badge-success">Active</span>' : 
                            '<span class="badge badge-danger">Inactive</span>';
                    },
                    'format' => 'raw',
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
                                        'confirm' => 'Are you sure you want to delete this warranty?',
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
        
        // Initialize date range picker
        $('#warranty-date-range').daterangepicker({
            locale: {
                format: 'DD/MM/YYYY',
                separator: ' - '
            },
            autoUpdateInput: false
        });
        
        $('#warranty-date-range').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
        });
        
        $('#warranty-date-range').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });
    });
JS;
$this->registerJs($js);
?>