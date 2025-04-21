<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\WarrantyStatusSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Warranty Statuses';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="warranty-status-index card">
    <div class="card-header">
        <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
        <div class="card-tools">
            <?= Html::a('<i class="fas fa-plus"></i> Create Warranty Status', ['create'], ['class' => 'btn btn-success btn-sm']) ?>
        </div>
    </div>

    <div class="card-body">
        <!-- Search Form -->
        <div class="warranty-status-search mb-4">
            <form id="searchForm" action="<?= Url::to(['index']) ?>" method="get">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="WarrantyStatusSearch[name]" class="form-control" value="<?= Html::encode($searchModel->name) ?>">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label>Description</label>
                            <input type="text" name="WarrantyStatusSearch[description]" class="form-control" value="<?= Html::encode($searchModel->description) ?>">
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-group mb-0 w-100">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-secondary">
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
                'name',
                'description:ntext',
                [
                    'attribute' => 'color',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return '<span class="badge" style="background-color: ' . $model->color . 
                               '; width: 100%; display: block; padding: 10px;">' . 
                               $model->color . '</span>';
                    },
                ],
                'sort_order',
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view} {update} {delete}',
                    'buttons' => [
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
                                        'confirm' => 'Are you sure you want to delete this warranty status? This cannot be undone if this status is being used.',
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