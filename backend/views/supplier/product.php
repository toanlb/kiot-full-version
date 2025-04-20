<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use kartik\select2\Select2;
use yii\web\JsExpression;
use yii\helpers\ArrayHelper;
use common\models\Product;

/* @var $this yii\web\View */
/* @var $supplier common\models\Supplier */
/* @var $model common\models\SupplierProduct */
/* @var $supplierProducts common\models\SupplierProduct[] */

$this->title = 'Quản lý sản phẩm - ' . $supplier->name;
$this->params['breadcrumbs'][] = ['label' => 'Quản lý nhà cung cấp', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $supplier->name, 'url' => ['view', 'id' => $supplier->id]];
$this->params['breadcrumbs'][] = 'Quản lý sản phẩm';
?>
<div class="supplier-product">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title">Thêm sản phẩm mới</h3>
        </div>
        <div class="card-body">
            <?php $form = ActiveForm::begin(['action' => ['add-product'], 'options' => ['class' => 'form-horizontal']]); ?>
            
            <?= $form->field($model, 'supplier_id')->hiddenInput()->label(false) ?>
            
            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'product_id')->widget(Select2::classname(), [
                        'options' => ['placeholder' => 'Chọn sản phẩm'],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'minimumInputLength' => 2,
                            'ajax' => [
                                'url' => Url::to(['/product/get-list']),
                                'dataType' => 'json',
                                'data' => new JsExpression('function(params) { return {q:params.term}; }')
                            ],
                        ]
                    ]); ?>
                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'supplier_product_code')->textInput(['maxlength' => true, 'placeholder' => 'Mã sản phẩm NCC']) ?>
                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'supplier_product_name')->textInput(['maxlength' => true, 'placeholder' => 'Tên sản phẩm NCC']) ?>
                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'unit_price')->textInput(['type' => 'number', 'step' => '0.01', 'min' => '0']) ?>
                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'min_order_quantity')->textInput(['type' => 'number', 'min' => '1']) ?>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-2">
                    <?= $form->field($model, 'lead_time')->textInput(['type' => 'number', 'min' => '0']) ?>
                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'is_primary_supplier')->checkbox() ?>
                </div>
                <div class="col-md-6">
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <button type="submit" class="btn btn-success btn-block"><i class="fas fa-plus"></i> Thêm sản phẩm</button>
                    </div>
                </div>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header bg-info text-white">
            <h3 class="card-title">Danh sách sản phẩm</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="width: 50px">#</th>
                            <th>Mã sản phẩm</th>
                            <th>Tên sản phẩm</th>
                            <th>Mã NCC</th>
                            <th>Tên NCC</th>
                            <th>Giá nhập</th>
                            <th>SL tối thiểu</th>
                            <th>Thời gian giao</th>
                            <th>NCC chính</th>
                            <th>Ngày nhập cuối</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($supplierProducts as $i => $product): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= $product->product->code ?></td>
                                <td><?= $product->product->name ?></td>
                                <td><?= $product->supplier_product_code ?></td>
                                <td><?= $product->supplier_product_name ?></td>
                                <td class="text-right"><?= Yii::$app->formatter->asCurrency($product->unit_price) ?></td>
                                <td class="text-center"><?= $product->min_order_quantity ?></td>
                                <td class="text-center"><?= $product->lead_time ? $product->lead_time . ' ngày' : '-' ?></td>
                                <td class="text-center">
                                    <?= $product->is_primary_supplier ? '<span class="badge badge-success"><i class="fas fa-check"></i></span>' : '' ?>
                                </td>
                                <td><?= $product->last_purchase_date ? Yii::$app->formatter->asDate($product->last_purchase_date) : '-' ?></td>
                                <td class="text-center">
                                    <a href="<?= Url::to(['/product/view', 'id' => $product->product_id]) ?>" class="btn btn-sm btn-primary" title="Xem sản phẩm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?= Html::a('<i class="fas fa-trash"></i>', ['remove-product', 'id' => $product->id], [
                                        'class' => 'btn btn-sm btn-danger',
                                        'title' => 'Xóa khỏi nhà cung cấp',
                                        'data' => [
                                            'confirm' => 'Bạn có chắc chắn muốn xóa sản phẩm này khỏi nhà cung cấp?',
                                            'method' => 'post',
                                        ],
                                    ]) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($supplierProducts)): ?>
                            <tr>
                                <td colspan="11" class="text-center">Chưa có sản phẩm nào</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="mt-3">
        <?= Html::a('<i class="fas fa-arrow-left"></i> Quay lại', ['view', 'id' => $supplier->id], ['class' => 'btn btn-secondary']) ?>
        <?= Html::a('<i class="fas fa-plus"></i> Tạo đơn nhập hàng', ['/stock-in/create', 'supplier_id' => $supplier->id], ['class' => 'btn btn-success']) ?>
    </div>
</div>