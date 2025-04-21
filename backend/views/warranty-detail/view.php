<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\WarrantyDetail */

$this->title = 'Service Record #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Warranty Service Records', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="warranty-detail-view">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
                    <div class="card-tools">
                        <?= Html::a('<i class="fas fa-print"></i>', ['print', 'id' => $model->id], ['class' => 'btn btn-primary btn-sm', 'target' => '_blank']) ?>
                        <?= Html::a('<i class="fas fa-edit"></i>', ['update', 'id' => $model->id], ['class' => 'btn btn-primary btn-sm']) ?>
                        <?= Html::a('<i class="fas fa-trash"></i>', ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-danger btn-sm',
                            'data' => [
                                'confirm' => 'Are you sure you want to delete this service record?',
                                'method' => 'post',
                            ],
                        ]) ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered detail-view">
                                <tr>
                                    <th>Warranty</th>
                                    <td>
                                        <?= Html::a(
                                            Html::encode($model->warranty->code ?? 'N/A'),
                                            ['/warranty/view', 'id' => $model->warranty_id],
                                            ['class' => 'btn btn-sm btn-outline-primary']
                                        ) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Product</th>
                                    <td><?= Html::encode($model->warranty->product->name ?? 'N/A') ?></td>
                                </tr>
                                <tr>
                                    <th>Serial Number</th>
                                    <td><?= Html::encode($model->warranty->serial_number ?? 'N/A') ?></td>
                                </tr>
                                <tr>
                                    <th>Customer</th>
                                    <td><?= Html::encode($model->warranty->customer->name ?? 'N/A') ?></td>
                                </tr>
                                <tr>
                                    <th>Service Date</th>
                                    <td><?= Yii::$app->formatter->asDatetime($model->service_date) ?></td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <span class="badge" style="background-color: <?= $model->status->color ?? '#777' ?>">
                                            <?= Html::encode($model->status->name ?? 'Unknown') ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered detail-view">
                                <tr>
                                    <th>Replacement Product</th>
                                    <td>
                                        <?php if ($model->replacement_product_id): ?>
                                            <?= Html::a(
                                                Html::encode($model->replacementProduct->name ?? 'N/A'),
                                                ['/product/view', 'id' => $model->replacement_product_id],
                                                ['class' => 'btn btn-sm btn-outline-info']
                                            ) ?>
                                        <?php else: ?>
                                            None
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Replacement Cost</th>
                                    <td><?= Yii::$app->formatter->asCurrency($model->replacement_cost) ?></td>
                                </tr>
                                <tr>
                                    <th>Service Cost</th>
                                    <td><?= Yii::$app->formatter->asCurrency($model->service_cost) ?></td>
                                </tr>
                                <tr>
                                    <th>Total Cost</th>
                                    <td><strong><?= Yii::$app->formatter->asCurrency($model->total_cost) ?></strong></td>
                                </tr>
                                <tr>
                                    <th>Charged</th>
                                    <td>
                                        <?php if ($model->is_charged): ?>
                                            <span class="badge badge-info">Yes</span>
                                        <?php else: ?>
                                            <span class="badge badge-success">No (Under Warranty)</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Technician</th>
                                    <td><?= Html::encode($model->handler->full_name ?? 'N/A') ?></td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td><?= Yii::$app->formatter->asDatetime($model->created_at) ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="card-title">Service Details</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><strong>Description</strong></label>
                                                <div class="p-2 bg-light rounded">
                                                    <?= nl2br(Html::encode($model->description)) ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><strong>Diagnosis</strong></label>
                                                <div class="p-2 bg-light rounded">
                                                    <?= nl2br(Html::encode($model->diagnosis)) ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><strong>Solution</strong></label>
                                                <div class="p-2 bg-light rounded">
                                                    <?= nl2br(Html::encode($model->solution)) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <?= Html::a('<i class="fas fa-arrow-left"></i> Back to List', ['index'], ['class' => 'btn btn-secondary']) ?>
                    <?= Html::a('<i class="fas fa-shield-alt"></i> View Warranty', ['/warranty/view', 'id' => $model->warranty_id], ['class' => 'btn btn-info']) ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info">
                    <h3 class="card-title">Customer Information</h3>
                </div>
                <div class="card-body">
                    <?php if ($model->warranty && $model->warranty->customer): $customer = $model->warranty->customer; ?>
                        <div class="text-center mb-3">
                            <?php if ($customer->avatar): ?>
                                <img src="<?= Html::encode($customer->avatar) ?>" class="profile-user-img img-fluid img-circle" style="width: 100px; height: 100px;">
                            <?php else: ?>
                                <i class="fas fa-user-circle fa-5x text-muted"></i>
                            <?php endif; ?>
                        </div>
                        
                        <h5 class="profile-username text-center"><?= Html::encode($customer->name) ?></h5>
                        
                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b>Phone</b> <a class="float-right"><?= Html::encode($customer->phone) ?></a>
                            </li>
                            <li class="list-group-item">
                                <b>Email</b> <a class="float-right"><?= Html::encode($customer->email) ?></a>
                            </li>
                            <li class="list-group-item">
                                <b>Address</b> <a class="float-right"><?= Html::encode($customer->address) ?></a>
                            </li>
                        </ul>
                        
                        <?= Html::a('View Customer', ['/customer/view', 'id' => $customer->id], ['class' => 'btn btn-primary btn-block']) ?>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            Customer information not available
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header bg-success">
                    <h3 class="card-title">Service History</h3>
                </div>
                <div class="card-body p-0">
                    <?php if ($model->warranty): 
                        $otherServices = \common\models\WarrantyDetail::find()
                            ->where(['warranty_id' => $model->warranty_id])
                            ->andWhere(['<>', 'id', $model->id])
                            ->orderBy(['service_date' => SORT_DESC])
                            ->limit(5)
                            ->all();
                            
                        if (!empty($otherServices)):
                    ?>
                        <ul class="timeline timeline-inverse p-3">
                            <?php foreach ($otherServices as $service): ?>
                                <li class="time-label">
                                    <span class="bg-info">
                                        <?= Yii::$app->formatter->asDate($service->service_date) ?>
                                    </span>
                                </li>
                                <li>
                                    <i class="fas fa-tools bg-blue"></i>
                                    <div class="timeline-item">
                                        <h3 class="timeline-header">
                                            <?= Html::a("Service #".$service->id, ['view', 'id' => $service->id]) ?>
                                            -
                                            <span class="badge" style="background-color: <?= $service->status->color ?? '#777' ?>">
                                                <?= Html::encode($service->status->name ?? 'Unknown') ?>
                                            </span>
                                        </h3>
                                        <div class="timeline-body">
                                            <?= Html::encode($service->diagnosis) ?>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                            <li>
                                <i class="fas fa-clock bg-gray"></i>
                            </li>
                        </ul>
                        
                        <?php if (count($otherServices) == 5): ?>
                            <div class="text-center p-2">
                                <?= Html::a('View All Service Records', ['/warranty/view', 'id' => $model->warranty_id], ['class' => 'btn btn-sm btn-default']) ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-info m-3">
                            No other service records found
                        </div>
                    <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-warning m-3">
                            Warranty information not available
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>