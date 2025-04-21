<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Warranty */
/* @var $warrantyDetails array */

$this->title = 'Print Warranty: ' . $model->code;
$this->params['breadcrumbs'][] = ['label' => 'Warranties', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->code, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Print';

// Calculate remaining warranty days
$today = new \DateTime();
$endDate = new \DateTime($model->end_date);
$interval = $today->diff($endDate);
$remainingDays = $interval->format('%R%a');
$isExpired = $remainingDays < 0;

// Get company information from settings
$companyName = Yii::$app->params['companyName'] ?? 'Your Company Name';
$companyAddress = Yii::$app->params['companyAddress'] ?? 'Your Company Address';
$companyPhone = Yii::$app->params['companyPhone'] ?? 'Your Company Phone';
$companyEmail = Yii::$app->params['companyEmail'] ?? 'your@email.com';
?>

<div class="warranty-print">
    <div class="invoice p-3 mb-3">
        <!-- title row -->
        <div class="row">
            <div class="col-12">
                <h4>
                    <i class="fas fa-shield-alt"></i> <?= Html::encode($companyName) ?>
                    <small class="float-right">Date: <?= date('d/m/Y') ?></small>
                </h4>
            </div>
            <!-- /.col -->
        </div>
        
        <!-- info row -->
        <div class="row invoice-info">
            <div class="col-sm-4 invoice-col">
                <address>
                    <strong><?= Html::encode($companyName) ?></strong><br>
                    <?= Html::encode($companyAddress) ?><br>
                    Phone: <?= Html::encode($companyPhone) ?><br>
                    Email: <?= Html::encode($companyEmail) ?>
                </address>
            </div>
            <!-- /.col -->
            <div class="col-sm-4 invoice-col">
                <address>
                    <strong>Customer:</strong><br>
                    <?= Html::encode($model->customer->name ?? 'N/A') ?><br>
                    <?= Html::encode($model->customer->address ?? 'N/A') ?><br>
                    Phone: <?= Html::encode($model->customer->phone ?? 'N/A') ?><br>
                    Email: <?= Html::encode($model->customer->email ?? 'N/A') ?>
                </address>
            </div>
            <!-- /.col -->
            <div class="col-sm-4 invoice-col">
                <b>Warranty #<?= Html::encode($model->code) ?></b><br>
                <br>
                <b>Order ID:</b> <?= $model->order_id ? Html::encode($model->order->code ?? $model->order_id) : 'N/A' ?><br>
                <b>Issue Date:</b> <?= Yii::$app->formatter->asDate($model->start_date) ?><br>
                <b>Expiry Date:</b> <?= Yii::$app->formatter->asDate($model->end_date) ?><br>
                <b>Status:</b> 
                <span class="badge" style="background-color: <?= $model->status->color ?? '#777' ?>">
                    <?= Html::encode($model->status->name ?? 'Unknown') ?>
                </span>
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
        
        <!-- title -->
        <div class="row mt-4">
            <div class="col-12 text-center">
                <h2>WARRANTY CERTIFICATE</h2>
            </div>
        </div>
        
        <!-- Product row -->
        <div class="row mt-4">
            <div class="col-12 table-responsive">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th style="width: 30%">Product</th>
                            <td><?= Html::encode($model->product->name ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <th>Product Code</th>
                            <td><?= Html::encode($model->product->code ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <th>Serial Number</th>
                            <td><?= Html::encode($model->serial_number) ?></td>
                        </tr>
                        <tr>
                            <th>Warranty Period</th>
                            <td>
                                From <?= Yii::$app->formatter->asDate($model->start_date) ?> 
                                to <?= Yii::$app->formatter->asDate($model->end_date) ?>
                                (<?= abs((new \DateTime($model->end_date))->diff(new \DateTime($model->start_date))->days) ?> days)
                            </td>
                        </tr>
                        <tr>
                            <th>Current Status</th>
                            <td>
                                <?php if ($model->active): ?>
                                    <?php if ($isExpired): ?>
                                        <span class="text-danger">Expired <?= abs($remainingDays) ?> day(s) ago</span>
                                    <?php else: ?>
                                        <span class="text-success">Valid (<?= $remainingDays ?> day(s) remaining)</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-warning">Warranty Inactive</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- /.col -->
        </div>
        
        <?php if (!empty($warrantyDetails)): ?>
        <!-- Service History row -->
        <div class="row mt-4">
            <div class="col-12 table-responsive">
                <h5>Service History</h5>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Description</th>
                            <th>Solution</th>
                            <th>Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($warrantyDetails as $index => $detail): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= Yii::$app->formatter->asDatetime($detail->service_date) ?></td>
                                <td>
                                    <span style="background-color: <?= $detail->status->color ?? '#777' ?>; color: white; padding: 3px 6px; border-radius: 3px;">
                                        <?= Html::encode($detail->status->name ?? 'Unknown') ?>
                                    </span>
                                </td>
                                <td>
                                    <strong>Issue:</strong> <?= Html::encode($detail->description) ?><br>
                                    <strong>Diagnosis:</strong> <?= Html::encode($detail->diagnosis) ?>
                                </td>
                                <td><?= Html::encode($detail->solution) ?></td>
                                <td>
                                    <?php if ($detail->is_charged): ?>
                                        Replacement: <?= Yii::$app->formatter->asCurrency($detail->replacement_cost) ?><br>
                                        Service: <?= Yii::$app->formatter->asCurrency($detail->service_cost) ?><br>
                                        <strong>Total: <?= Yii::$app->formatter->asCurrency($detail->total_cost) ?></strong>
                                    <?php else: ?>
                                        <span class="text-success">Under Warranty</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- /.col -->
        </div>
        <?php endif; ?>
        
        <!-- Warranty Terms row -->
        <div class="row mt-4">
            <div class="col-12">
                <h5>Warranty Terms & Conditions</h5>
                <div class="callout callout-info">
                    <ol>
                        <li>This warranty is valid only for the product mentioned above with the specified serial number.</li>
                        <li>The warranty period is valid from the start date to the end date mentioned above.</li>
                        <li>This warranty covers defects in materials and workmanship under normal use during the warranty period.</li>
                        <li>This warranty does not cover damage caused by misuse, abuse, accidents, or unauthorized modifications.</li>
                        <li>For warranty service, please contact us at <?= Html::encode($companyPhone) ?> or visit our service center with this warranty certificate.</li>
                        <li>All warranty services must be performed by authorized service personnel.</li>
                        <li>This warranty is non-transferable and applies only to the original purchaser.</li>
                    </ol>
                </div>
            </div>
            <!-- /.col -->
        </div>
        
        <!-- Signature row -->
        <div class="row mt-4">
            <div class="col-6">
                <p class="lead">Customer Signature:</p>
                <div style="border-top: 1px solid #000; margin-top: 50px; width: 75%;"></div>
                <p class="mt-2"><?= Html::encode($model->customer->name ?? 'Customer') ?></p>
            </div>
            <div class="col-6 text-right">
                <p class="lead">Authorized Signature:</p>
                <div style="border-top: 1px solid #000; margin-top: 50px; width: 75%; float: right;"></div>
                <p class="mt-2"><?= Html::encode($model->creator->full_name ?? 'Authorized Person') ?></p>
            </div>
        </div>
        
        <!-- this row will not appear when printing -->
        <div class="row no-print mt-4">
            <div class="col-12">
                <button type="button" class="btn btn-primary float-right" onclick="window.print()">
                    <i class="fas fa-print"></i> Print
                </button>
                <a href="<?= \yii\helpers\Url::to(['view', 'id' => $model->id]) ?>" class="btn btn-default">
                    <i class="fas fa-arrow-left"></i> Back to Warranty
                </a>
            </div>
        </div>
    </div>
</div>

<?php
$css = <<<CSS
    @media print {
        .no-print {
            display: none !important;
        }
        a[href]:after {
            content: none !important;
        }
        .main-footer, .btn {
            display: none !important;
        }
    }
    .invoice {
        margin: 0;
        padding: 20px;
        border: none;
    }
    body {
        background-color: white !important;
    }
CSS;
$this->registerCss($css);
?>