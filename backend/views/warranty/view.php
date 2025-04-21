<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\Warranty */
/* @var $warrantyDetail common\models\WarrantyDetail */
/* @var $warrantyDetails array */
/* @var $statuses array */
/* @var $replacementProducts array */

$this->title = 'Warranty: ' . $model->code;
$this->params['breadcrumbs'][] = ['label' => 'Warranties', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Calculate remaining warranty days
$today = new \DateTime();
$endDate = new \DateTime($model->end_date);
$interval = $today->diff($endDate);
$remainingDays = $interval->format('%R%a');
$isExpired = $remainingDays < 0;
?>
<div class="warranty-view">

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Warranty Information</h3>
                    <div class="card-tools">
                        <?= Html::a('<i class="fas fa-print"></i>', ['print', 'id' => $model->id], ['class' => 'btn btn-primary btn-sm', 'target' => '_blank']) ?>
                        <?= Html::a('<i class="fas fa-edit"></i>', ['update', 'id' => $model->id], ['class' => 'btn btn-primary btn-sm']) ?>
                        <?= Html::a('<i class="fas fa-trash"></i>', ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-danger btn-sm',
                            'data' => [
                                'confirm' => 'Are you sure you want to delete this warranty?',
                                'method' => 'post',
                            ],
                        ]) ?>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered detail-view">
                        <tbody>
                            <tr>
                                <th>Code</th>
                                <td><?= Html::encode($model->code) ?></td>
                            </tr>
                            <tr>
                                <th>Product</th>
                                <td><?= Html::encode($model->product->name ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <th>Customer</th>
                                <td><?= Html::encode($model->customer->name ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <th>Serial Number</th>
                                <td><?= Html::encode($model->serial_number) ?></td>
                            </tr>
                            <tr>
                                <th>Start Date</th>
                                <td><?= Yii::$app->formatter->asDate($model->start_date) ?></td>
                            </tr>
                            <tr>
                                <th>End Date</th>
                                <td class="<?= $isExpired ? 'text-danger' : '' ?>">
                                    <?= Yii::$app->formatter->asDate($model->end_date) ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Warranty Status</th>
                                <td>
                                    <span class="badge" style="background-color: <?= $model->status->color ?? '#777' ?>;">
                                        <?= Html::encode($model->status->name ?? 'Unknown') ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Warranty Period</th>
                                <td>
                                    <?php if ($model->active): ?>
                                        <?php if ($isExpired): ?>
                                            <span class="badge badge-danger">Expired <?= abs($remainingDays) ?> day(s) ago</span>
                                        <?php else: ?>
                                            <span class="badge badge-success"><?= $remainingDays ?> day(s) remaining</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Warranty Inactive</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Active</th>
                                <td>
                                    <?php if ($model->active): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Order ID</th>
                                <td>
                                    <?php if ($model->order_id): ?>
                                        <?= Html::a(
                                            $model->order->code ?? 'N/A',
                                            ['/order/view', 'id' => $model->order_id],
                                            ['class' => 'btn btn-sm btn-outline-primary']
                                        ) ?>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Note</th>
                                <td><?= nl2br(Html::encode($model->note)) ?></td>
                            </tr>
                            <tr>
                                <th>Created At</th>
                                <td><?= Yii::$app->formatter->asDatetime($model->created_at) ?></td>
                            </tr>
                            <tr>
                                <th>Created By</th>
                                <td><?= Html::encode($model->creator->username ?? 'N/A') ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Customer & Product Information</h3>
                </div>
                <div class="card-body">
                    <?php if ($model->customer): ?>
                    <div class="row">
                        <div class="col-md-12">
                            <h5>Customer Information</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Name</th>
                                    <td><?= Html::encode($model->customer->name) ?></td>
                                </tr>
                                <tr>
                                    <th>Phone</th>
                                    <td><?= Html::encode($model->customer->phone) ?></td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td><?= Html::encode($model->customer->email) ?></td>
                                </tr>
                                <tr>
                                    <th>Address</th>
                                    <td><?= Html::encode($model->customer->address) ?></td>
                                </tr>
                            </table>
                            
                            <?= Html::a(
                                '<i class="fas fa-user"></i> View Customer',
                                ['/customer/view', 'id' => $model->customer_id],
                                ['class' => 'btn btn-info btn-sm mt-2']
                            ) ?>
                        </div>
                    </div>
                    <hr>
                    <?php endif; ?>
                    
                    <?php if ($model->product): ?>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <h5>Product Information</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Product Name</th>
                                    <td><?= Html::encode($model->product->name) ?></td>
                                </tr>
                                <tr>
                                    <th>Product Code</th>
                                    <td><?= Html::encode($model->product->code) ?></td>
                                </tr>
                                <tr>
                                    <th>Category</th>
                                    <td><?= Html::encode($model->product->category->name ?? 'N/A') ?></td>
                                </tr>
                                <tr>
                                    <th>Warranty Period</th>
                                    <td><?= Html::encode($model->product->warranty_period) ?> days</td>
                                </tr>
                            </table>
                            
                            <?= Html::a(
                                '<i class="fas fa-box"></i> View Product',
                                ['/product/view', 'id' => $model->product_id],
                                ['class' => 'btn btn-info btn-sm mt-2']
                            ) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Warranty Service History</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($warrantyDetails)): ?>
                        <div class="alert alert-info">
                            No service records found for this warranty.
                        </div>
                    <?php else: ?>
                        <div class="timeline">
                            <?php foreach ($warrantyDetails as $index => $detail): ?>
                                <div class="time-label">
                                    <span style="background-color: <?= $detail->status->color ?? '#777' ?>">
                                        <?= date('d M Y', strtotime($detail->service_date)) ?>
                                    </span>
                                </div>
                                <div>
                                    <i class="fas fa-tools bg-blue"></i>
                                    <div class="timeline-item">
                                        <span class="time">
                                            <i class="fas fa-clock"></i> 
                                            <?= date('H:i', strtotime($detail->service_date)) ?>
                                        </span>
                                        <h3 class="timeline-header">
                                            <a href="<?= Url::to(['warranty-detail/view', 'id' => $detail->id]) ?>">
                                                Service #<?= $detail->id ?>
                                            </a> - 
                                            <span class="badge" style="background-color: <?= $detail->status->color ?? '#777' ?>">
                                                <?= $detail->status->name ?? 'Unknown' ?>
                                            </span>
                                        </h3>
                                        <div class="timeline-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <strong>Description:</strong> <?= Html::encode($detail->description) ?>
                                                    <br>
                                                    <strong>Diagnosis:</strong> <?= Html::encode($detail->diagnosis) ?>
                                                    <br>
                                                    <strong>Solution:</strong> <?= Html::encode($detail->solution) ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php if ($detail->replacement_product_id): ?>
                                                        <strong>Replacement Product:</strong> 
                                                        <?= Html::encode($detail->replacementProduct->name ?? 'N/A') ?>
                                                        <br>
                                                    <?php endif; ?>
                                                    <strong>Replacement Cost:</strong> 
                                                    <?= Yii::$app->formatter->asCurrency($detail->replacement_cost) ?>
                                                    <br>
                                                    <strong>Service Cost:</strong> 
                                                    <?= Yii::$app->formatter->asCurrency($detail->service_cost) ?>
                                                    <br>
                                                    <strong>Total Cost:</strong> 
                                                    <?= Yii::$app->formatter->asCurrency($detail->total_cost) ?>
                                                    <br>
                                                    <strong>Charged:</strong> 
                                                    <?= $detail->is_charged ? 'Yes' : 'No' ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="timeline-footer">
                                            <div class="btn-group">
                                                <?= Html::a(
                                                    '<i class="fas fa-eye"></i> View',
                                                    ['warranty-detail/view', 'id' => $detail->id],
                                                    ['class' => 'btn btn-primary btn-sm']
                                                ) ?>
                                                <?= Html::a(
                                                    '<i class="fas fa-edit"></i> Edit',
                                                    ['warranty-detail/update', 'id' => $detail->id],
                                                    ['class' => 'btn btn-info btn-sm']
                                                ) ?>
                                                <?= Html::a(
                                                    '<i class="fas fa-trash"></i> Delete',
                                                    ['warranty-detail/delete', 'id' => $detail->id],
                                                    [
                                                        'class' => 'btn btn-danger btn-sm',
                                                        'data' => [
                                                            'confirm' => 'Are you sure you want to delete this service record?',
                                                            'method' => 'post',
                                                        ],
                                                    ]
                                                ) ?>
                                                <?= Html::a(
                                                    '<i class="fas fa-print"></i> Print',
                                                    ['warranty-detail/print', 'id' => $detail->id],
                                                    ['class' => 'btn btn-secondary btn-sm', 'target' => '_blank']
                                                ) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <div>
                                <i class="fas fa-clock bg-gray"></i>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Add New Service Record</h3>
                </div>
                <div class="card-body">
                    <!-- Service Record Form -->
                    <form id="warranty-detail-form" action="<?= Url::to(['warranty/view', 'id' => $model->id]) ?>" method="post">
                        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                        <?= Html::hiddenInput('WarrantyDetail[warranty_id]', $model->id) ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="warranty-detail-service-date">Service Date</label>
                                    <div class="input-group">
                                        <input type="text" id="warranty-detail-service-date" name="WarrantyDetail[service_date]" class="form-control datetimepicker" value="<?= date('Y-m-d H:i:s') ?>">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                        </div>
                                    </div>
                                    <?php if ($warrantyDetail->getErrors('service_date')): ?>
                                        <div class="invalid-feedback d-block"><?= Html::encode($warrantyDetail->getFirstError('service_date')) ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label for="warranty-detail-status-id">Status</label>
                                    <select id="warranty-detail-status-id" name="WarrantyDetail[status_id]" class="form-control select2" data-placeholder="Select Status">
                                        <option value=""></option>
                                        <?php foreach ($statuses as $id => $name): ?>
                                            <option value="<?= $id ?>" <?= $warrantyDetail->status_id == $id ? 'selected' : '' ?>>
                                                <?= Html::encode($name) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if ($warrantyDetail->getErrors('status_id')): ?>
                                        <div class="invalid-feedback d-block"><?= Html::encode($warrantyDetail->getFirstError('status_id')) ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-group">
                                    <label for="warranty-detail-description">Description</label>
                                    <textarea id="warranty-detail-description" name="WarrantyDetail[description]" class="form-control" rows="3"><?= Html::encode($warrantyDetail->description) ?></textarea>
                                    <?php if ($warrantyDetail->getErrors('description')): ?>
                                        <div class="invalid-feedback d-block"><?= Html::encode($warrantyDetail->getFirstError('description')) ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-group">
                                    <label for="warranty-detail-diagnosis">Diagnosis</label>
                                    <textarea id="warranty-detail-diagnosis" name="WarrantyDetail[diagnosis]" class="form-control" rows="3"><?= Html::encode($warrantyDetail->diagnosis) ?></textarea>
                                    <?php if ($warrantyDetail->getErrors('diagnosis')): ?>
                                        <div class="invalid-feedback d-block"><?= Html::encode($warrantyDetail->getFirstError('diagnosis')) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="warranty-detail-solution">Solution</label>
                                    <textarea id="warranty-detail-solution" name="WarrantyDetail[solution]" class="form-control" rows="3"><?= Html::encode($warrantyDetail->solution) ?></textarea>
                                    <?php if ($warrantyDetail->getErrors('solution')): ?>
                                        <div class="invalid-feedback d-block"><?= Html::encode($warrantyDetail->getFirstError('solution')) ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-group">
                                    <label for="warranty-detail-replacement-product-id">Replacement Product</label>
                                    <select id="warranty-detail-replacement-product-id" name="WarrantyDetail[replacement_product_id]" class="form-control select2" data-placeholder="Select Product (if replacing)">
                                        <option value=""></option>
                                        <?php foreach ($replacementProducts as $id => $name): ?>
                                            <option value="<?= $id ?>" <?= $warrantyDetail->replacement_product_id == $id ? 'selected' : '' ?>>
                                                <?= Html::encode($name) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if ($warrantyDetail->getErrors('replacement_product_id')): ?>
                                        <div class="invalid-feedback d-block"><?= Html::encode($warrantyDetail->getFirstError('replacement_product_id')) ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="warranty-detail-replacement-cost">Replacement Cost</label>
                                            <input type="number" id="warranty-detail-replacement-cost" name="WarrantyDetail[replacement_cost]" class="form-control cost-input" step="0.01" value="<?= $warrantyDetail->replacement_cost ?>">
                                            <?php if ($warrantyDetail->getErrors('replacement_cost')): ?>
                                                <div class="invalid-feedback d-block"><?= Html::encode($warrantyDetail->getFirstError('replacement_cost')) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="warranty-detail-service-cost">Service Cost</label>
                                            <input type="number" id="warranty-detail-service-cost" name="WarrantyDetail[service_cost]" class="form-control cost-input" step="0.01" value="<?= $warrantyDetail->service_cost ?>">
                                            <?php if ($warrantyDetail->getErrors('service_cost')): ?>
                                                <div class="invalid-feedback d-block"><?= Html::encode($warrantyDetail->getFirstError('service_cost')) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="warranty-detail-total-cost">Total Cost</label>
                                            <input type="number" id="warranty-detail-total-cost" name="WarrantyDetail[total_cost]" class="form-control" step="0.01" value="<?= $warrantyDetail->total_cost ?>">
                                            <?php if ($warrantyDetail->getErrors('total_cost')): ?>
                                                <div class="invalid-feedback d-block"><?= Html::encode($warrantyDetail->getFirstError('total_cost')) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="warranty-detail-is-charged" name="WarrantyDetail[is_charged]" value="1" <?= $warrantyDetail->is_charged ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="warranty-detail-is-charged">Charge Customer</label>
                                    </div>
                                    <?php if ($warrantyDetail->getErrors('is_charged')): ?>
                                        <div class="invalid-feedback d-block"><?= Html::encode($warrantyDetail->getFirstError('is_charged')) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-success">Add Service Record</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
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
        
        // Initialize datetime picker
        $('.datetimepicker').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            icons: {
                time: 'fas fa-clock',
                date: 'fas fa-calendar',
                up: 'fas fa-arrow-up',
                down: 'fas fa-arrow-down',
                previous: 'fas fa-chevron-left',
                next: 'fas fa-chevron-right',
                today: 'fas fa-calendar-check-o',
                clear: 'fas fa-trash',
                close: 'fas fa-times'
            }
        });
        
        // Calculate total cost automatically
        $('.cost-input').on('input', function() {
            var replacementCost = parseFloat($('#warranty-detail-replacement-cost').val()) || 0;
            var serviceCost = parseFloat($('#warranty-detail-service-cost').val()) || 0;
            $('#warranty-detail-total-cost').val((replacementCost + serviceCost).toFixed(2));
        });
    });
JS;
$this->registerJs($js);
?>