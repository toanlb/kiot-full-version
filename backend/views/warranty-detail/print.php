<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\WarrantyDetail */

$this->title = 'Print Service Record: #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Warranty Service Records', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => '#' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Print';

// Get company information from settings
$companyName = Yii::$app->params['companyName'] ?? 'Your Company Name';
$companyAddress = Yii::$app->params['companyAddress'] ?? 'Your Company Address';
$companyPhone = Yii::$app->params['companyPhone'] ?? 'Your Company Phone';
$companyEmail = Yii::$app->params['companyEmail'] ?? 'your@email.com';
$companyLogo = Yii::$app->params['companyLogo'] ?? null;
?>

<div class="warranty-detail-print">
    <div class="invoice p-3 mb-3">
        <!-- title row -->
        <div class="row">
            <div class="col-12">
                <h4>
                    <?php if ($companyLogo): ?>
                        <img src="<?= Html::encode($companyLogo) ?>" alt="Company Logo" style="max-height: 50px;">
                    <?php else: ?>
                        <i class="fas fa-tools"></i>
                    <?php endif; ?>
                    <?= Html::encode($companyName) ?>
                    <small class="float-right">Date: <?= date('d/m/Y') ?></small>
                </h4>
            </div>
            <!-- /.col -->
        </div>
        
        <!-- title -->
        <div class="row mt-4">
            <div class="col-12 text-center">
                <h2>SERVICE RECORD</h2>
                <h4>Service ID: <?= Html::encode($model->id) ?></h4>
            </div>
        </div>
        
        <!-- info row -->
        <div class="row invoice-info mt-3">
            <div class="col-sm-4 invoice-col">
                <address>
                    <strong>Service Provider:</strong><br>
                    <?= Html::encode($companyName) ?><br>
                    <?= Html::encode($companyAddress) ?><br>
                    Phone: <?= Html::encode($companyPhone) ?><br>
                    Email: <?= Html::encode($companyEmail) ?>
                </address>
            </div>
            <!-- /.col -->
            <div class="col-sm-4 invoice-col">
                <address>
                    <strong>Customer:</strong><br>
                    <?= Html::encode($model->warranty->customer->name ?? 'N/A') ?><br>
                    <?= Html::encode($model->warranty->customer->address ?? 'N/A') ?><br>
                    Phone: <?= Html::encode($model->warranty->customer->phone ?? 'N/A') ?><br>
                    Email: <?= Html::encode($model->warranty->customer->email ?? 'N/A') ?>
                </address>
            </div>
            <!-- /.col -->
            <div class="col-sm-4 invoice-col">
                <b>Service Date:</b> <?= Yii::$app->formatter->asDatetime($model->service_date) ?><br>
                <b>Warranty #:</b> <?= Html::encode($model->warranty->code ?? 'N/A') ?><br>
                <b>Status:</b> 
                <span class="badge" style="background-color: <?= $model->status->color ?? '#777' ?>">
                    <?= Html::encode($model->status->name ?? 'Unknown') ?>
                </span><br>
                <b>Technician:</b> <?= Html::encode($model->handler->full_name ?? 'N/A') ?><br>
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
        
        <!-- Product Info -->
        <div class="row mt-4">
            <div class="col-12">
                <h5>Product Information</h5>
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 30%">Product</th>
                        <td><?= Html::encode($model->warranty->product->name ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <th>Product Code</th>
                        <td><?= Html::encode($model->warranty->product->code ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <th>Serial Number</th>
                        <td><?= Html::encode($model->warranty->serial_number ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <th>Warranty Period</th>
                        <td>
                            <?php if ($model->warranty): ?>
                                From <?= Yii::$app->formatter->asDate($model->warranty->start_date) ?> 
                                to <?= Yii::$app->formatter->asDate($model->warranty->end_date) ?>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php if ($model->replacement_product_id): ?>
                    <tr>
                        <th>Replacement Product</th>
                        <td><?= Html::encode($model->replacementProduct->name ?? 'N/A') ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
        
        <!-- Service Details -->
        <div class="row mt-4">
            <div class="col-12">
                <h5>Service Details</h5>
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 20%">Description</th>
                        <td><?= nl2br(Html::encode($model->description)) ?></td>
                    </tr>
                    <tr>
                        <th>Diagnosis</th>
                        <td><?= nl2br(Html::encode($model->diagnosis)) ?></td>
                    </tr>
                    <tr>
                        <th>Solution</th>
                        <td><?= nl2br(Html::encode($model->solution)) ?></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- Service Costs -->
        <?php if ($model->is_charged || $model->total_cost > 0): ?>
        <div class="row mt-4">
            <div class="col-8 offset-4">
                <table class="table table-bordered">
                    <?php if ($model->replacement_cost > 0): ?>
                    <tr>
                        <th style="width: 50%">Replacement Cost</th>
                        <td><?= Yii::$app->formatter->asCurrency($model->replacement_cost) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($model->service_cost > 0): ?>
                    <tr>
                        <th>Service Cost</th>
                        <td><?= Yii::$app->formatter->asCurrency($model->service_cost) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <th>Total Cost</th>
                        <td class="font-weight-bold"><?= Yii::$app->formatter->asCurrency($model->total_cost) ?></td>
                    </tr>
                    <tr>
                        <th>Charged to Customer</th>
                        <td>
                            <?php if ($model->is_charged): ?>
                                <span class="text-primary">Yes</span>
                            <?php else: ?>
                                <span class="text-success">No - Under Warranty</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Terms & Signature -->
        <div class="row mt-5">
            <div class="col-8">
                <p>
                    <strong>Notes:</strong><br>
                    1. All replacement parts are warranted for 30 days from service date.<br>
                    2. Labor charges are warranted for 7 days from service date.<br>
                    3. This service record must be presented for any warranty claims related to this repair.
                </p>
            </div>
            <div class="col-4">
                <p class="text-center">Service completed by:</p>
                <div style="border-top: 1px solid #000; margin-top: 50px;"></div>
                <p class="text-center mt-1"><?= Html::encode($model->handler->full_name ?? 'Technician') ?></p>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-6">
                <p class="lead">Customer Signature:</p>
                <div style="border-top: 1px solid #000; margin-top: 50px; width: 75%;"></div>
                <p class="mt-2"><?= Html::encode($model->warranty->customer->name ?? 'Customer') ?></p>
            </div>
            <div class="col-6 text-right">
                <p class="lead">Date:</p>
                <div style="border-top: 1px solid #000; margin-top: 50px; width: 50%; float: right;"></div>
            </div>
        </div>
        
        <!-- this row will not appear when printing -->
        <div class="row no-print mt-4">
            <div class="col-12">
                <button type="button" class="btn btn-primary float-right" onclick="window.print()">
                    <i class="fas fa-print"></i> Print
                </button>
                <a href="<?= Url::to(['view', 'id' => $model->id]) ?>" class="btn btn-default">
                    <i class="fas fa-arrow-left"></i> Back to Service Record
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