<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\CustomerGroup;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model common\models\Customer */

$this->title = 'Đổi nhóm khách hàng: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Khách hàng', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Đổi nhóm';
?>
<div class="customer-change-group">

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-info card-outline">
                                <div class="card-header">
                                    <h3 class="card-title">Thông tin khách hàng</h3>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 30%">Mã khách hàng</th>
                                            <td><?= Html::encode($model->code) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Tên khách hàng</th>
                                            <td><?= Html::encode($model->name) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Điện thoại</th>
                                            <td><?= Html::encode($model->phone) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Email</th>
                                            <td><?= Html::encode($model->email) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Nhóm hiện tại</th>
                                            <td><?= $model->customerGroup ? Html::encode($model->customerGroup->name) : '<span class="text-muted">Chưa phân nhóm</span>' ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-success card-outline">
                                <div class="card-header">
                                    <h3 class="card-title">Đổi nhóm</h3>
                                </div>
                                <div class="card-body">
                                    <?php $form = ActiveForm::begin(); ?>
                                    
                                    <div class="form-group">
                                        <label for="customer-group-id">Nhóm khách hàng</label>
                                        <?= Html::dropDownList(
                                            'customer_group_id',
                                            $model->customer_group_id,
                                            ArrayHelper::map(CustomerGroup::find()->orderBy('name')->all(), 'id', 'name'),
                                            [
                                                'class' => 'form-control',
                                                'prompt' => 'Chọn nhóm khách hàng',
                                                'required' => true,
                                            ]
                                        ) ?>
                                        <div class="help-block">Chọn nhóm khách hàng mới</div>
                                    </div>
                                    
                                    <div class="form-group text-right">
                                        <?= Html::submitButton('<i class="fas fa-save"></i> Lưu thay đổi', ['class' => 'btn btn-success']) ?>
                                        <?= Html::a('<i class="fas fa-undo"></i> Quay lại', ['view', 'id' => $model->id], ['class' => 'btn btn-default']) ?>
                                    </div>
                                    
                                    <?php ActiveForm::end(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-warning card-outline">
                                <div class="card-header">
                                    <h3 class="card-title">Các nhóm khách hàng</h3>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Tên nhóm</th>
                                                    <th>Tỷ lệ chiết khấu</th>
                                                    <th>Mô tả</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach (CustomerGroup::find()->orderBy('name')->all() as $index => $group): ?>
                                                <tr <?= $model->customer_group_id == $group->id ? 'class="table-success"' : '' ?>>
                                                    <td><?= $index + 1 ?></td>
                                                    <td><?= Html::encode($group->name) ?></td>
                                                    <td><?= $group->discount_rate ?> %</td>
                                                    <td><?= Yii::$app->formatter->asNtext($group->description) ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->

</div>