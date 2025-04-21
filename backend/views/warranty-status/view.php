<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\WarrantyStatus */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Warranty Statuses', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="warranty-status-view card">
    <div class="card-header">
        <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
        <div class="card-tools">
            <?= Html::a('<i class="fas fa-edit"></i> Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary btn-sm']) ?>
            <?= Html::a('<i class="fas fa-trash"></i> Delete', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger btn-sm',
                'data' => [
                    'confirm' => 'Are you sure you want to delete this item?',
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>

    <div class="card-body">
        <table class="table table-bordered detail-view">
            <tbody>
                <tr>
                    <th>ID</th>
                    <td><?= $model->id ?></td>
                </tr>
                <tr>
                    <th>Name</th>
                    <td><?= Html::encode($model->name) ?></td>
                </tr>
                <tr>
                    <th>Description</th>
                    <td><?= nl2br(Html::encode($model->description)) ?></td>
                </tr>
                <tr>
                    <th>Color</th>
                    <td>
                        <span class="badge" style="background-color: <?= $model->color ?>; width: 100px; display: inline-block; padding: 10px;">
                            <?= Html::encode($model->color) ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Sort Order</th>
                    <td><?= $model->sort_order ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="card-footer">
        <?= Html::a('<i class="fas fa-arrow-left"></i> Back to List', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>
</div>

<!-- Usage Statistics -->
<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title">Usage Statistics</h3>
    </div>
    <div class="card-body">
        <?php
        $warrantyCount = Yii::$app->db->createCommand("
            SELECT COUNT(*) FROM warranty WHERE status_id = :status_id
        ", [':status_id' => $model->id])->queryScalar();
        
        $detailCount = Yii::$app->db->createCommand("
            SELECT COUNT(*) FROM warranty_detail WHERE status_id = :status_id
        ", [':status_id' => $model->id])->queryScalar();
        ?>
        
        <div class="row">
            <div class="col-md-6">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-shield-alt"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Warranties with this status</span>
                        <span class="info-box-number"><?= $warrantyCount ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-tools"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Service Records with this status</span>
                        <span class="info-box-number"><?= $detailCount ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if ($warrantyCount > 0): ?>
            <div class="mt-3">
                <?= Html::a(
                    '<i class="fas fa-search"></i> View Warranties with this Status',
                    ['/warranty/index', 'WarrantySearch[status_id]' => $model->id],
                    ['class' => 'btn btn-info']
                ) ?>
            </div>
        <?php endif; ?>
    </div>
</div>