<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\models\User;

/* @var $this yii\web\View */
/* @var $model common\models\User */
/* @var $profile common\models\UserProfile */
/* @var $assignedWarehouses array */
/* @var $roles array */
/* @var $warehouses array */

$this->title = $model->full_name;
$this->params['breadcrumbs'][] = ['label' => 'Quản lý người dùng', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="user-view">

    <div class="mb-3">
        <?= Html::a('<i class="fas fa-arrow-left"></i> Quay lại', ['index'], ['class' => 'btn btn-secondary']) ?>
        <?= Html::a('<i class="fas fa-edit"></i> Cập nhật', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('<i class="fas fa-key"></i> Đặt lại mật khẩu', ['reset-password', 'id' => $model->id], [
            'class' => 'btn btn-warning',
            'data' => [
                'confirm' => 'Bạn có chắc muốn đặt lại mật khẩu cho người dùng này?',
                'method' => 'post',
            ],
        ]) ?>
        <?php if ($model->id != Yii::$app->user->id): ?>
            <?= Html::a('<i class="fas fa-trash"></i> Xóa', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Bạn có chắc muốn xóa người dùng này?',
                    'method' => 'post',
                ],
            ]) ?>
        <?php endif; ?>
        <?= Html::a('<i class="fas fa-history"></i> Lịch sử đăng nhập', ['login-history', 'id' => $model->id], ['class' => 'btn btn-info']) ?>
    </div>

    <div class="row">
        <div class="col-md-4">
            <!-- Thông tin cá nhân -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Thông tin cá nhân</h3>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <?php if ($model->avatar): ?>
                            <img class="profile-user-img img-fluid img-circle" 
                                 src="<?= Yii::getAlias('@web/' . $model->avatar) ?>" 
                                 alt="Ảnh đại diện">
                        <?php else: ?>
                            <img class="profile-user-img img-fluid img-circle" 
                                 src="<?= Yii::getAlias('@web/dist/img/avatar5.png') ?>" 
                                 alt="Ảnh đại diện mặc định">
                        <?php endif; ?>
                        <h3 class="profile-username text-center"><?= Html::encode($model->full_name) ?></h3>
                        <p class="text-muted text-center"><?= $profile->position ?: 'Chưa có chức vụ' ?></p>
                    </div>

                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>Tên đăng nhập</b> <a class="float-right"><?= Html::encode($model->username) ?></a>
                        </li>
                        <li class="list-group-item">
                            <b>Email</b> <a class="float-right"><?= Html::encode($model->email) ?></a>
                        </li>
                        <li class="list-group-item">
                            <b>Số điện thoại</b> <a class="float-right"><?= Html::encode($model->phone) ?></a>
                        </li>
                        <li class="list-group-item">
                            <b>Ngày sinh</b> <a class="float-right"><?= $profile->birthday ? Yii::$app->formatter->asDate($profile->birthday, 'php:d/m/Y') : 'Chưa cập nhật' ?></a>
                        </li>
                        <li class="list-group-item">
                            <b>CMND/CCCD</b> <a class="float-right"><?= Html::encode($profile->identity_card) ?: 'Chưa cập nhật' ?></a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Thông tin vai trò -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Vai trò người dùng</h3>
                </div>
                <div class="card-body">
                    <?php if (count($roles) > 0): ?>
                        <ul class="list-group">
                            <?php foreach ($roles as $role): ?>
                                <li class="list-group-item">
                                    <span class="badge badge-primary"><?= $role->name ?></span>
                                    <?= Html::encode($role->description ?: $role->name) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> Người dùng chưa được gán vai trò nào
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <!-- Thông tin công việc -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Thông tin công việc</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Phòng ban</label>
                                <p class="form-control-static"><?= Html::encode($profile->department) ?: 'Chưa cập nhật' ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Chức vụ</label>
                                <p class="form-control-static"><?= Html::encode($profile->position) ?: 'Chưa cập nhật' ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Ngày vào làm</label>
                                <p class="form-control-static"><?= $profile->hire_date ? Yii::$app->formatter->asDate($profile->hire_date, 'php:d/m/Y') : 'Chưa cập nhật' ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Trạng thái</label>
                                <p class="form-control-static">
                                    <?php
                                    switch ($model->status) {
                                        case User::STATUS_ACTIVE:
                                            echo '<span class="badge badge-success">Đang hoạt động</span>';
                                            break;
                                        case User::STATUS_INACTIVE:
                                            echo '<span class="badge badge-warning">Chưa kích hoạt</span>';
                                            break;
                                        case User::STATUS_DELETED:
                                            echo '<span class="badge badge-danger">Đã xóa</span>';
                                            break;
                                        default:
                                            echo '<span class="badge badge-secondary">Không xác định</span>';
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Địa chỉ</label>
                                <p class="form-control-static"><?= Html::encode($profile->address) ?: 'Chưa cập nhật' ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Thành phố</label>
                                <p class="form-control-static"><?= Html::encode($profile->city) ?: 'Chưa cập nhật' ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Quốc gia</label>
                                <p class="form-control-static"><?= Html::encode($profile->country) ?: 'Chưa cập nhật' ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Ghi chú</label>
                                <p class="form-control-static"><?= Html::encode($profile->notes) ?: 'Không có ghi chú' ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thông tin kho -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Thông tin kho hàng</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Kho mặc định</label>
                                <p class="form-control-static">
                                    <?php
                                    if ($model->warehouse_id) {
                                        echo '<span class="badge badge-primary">' . $model->warehouse->name . '</span>';
                                    } else {
                                        echo '<span class="badge badge-secondary">Chưa chọn kho mặc định</span>';
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Kho được phép truy cập</label>
                                <?php if (!empty($assignedWarehouses)): ?>
                                    <div>
                                    <?php 
                                    foreach ($warehouses as $warehouse) {
                                        if (in_array($warehouse->id, $assignedWarehouses)) {
                                            echo '<span class="badge badge-info mr-2">' . $warehouse->name . '</span> ';
                                        }
                                    }
                                    ?>
                                    </div>
                                <?php else: ?>
                                    <p class="form-control-static">
                                        <span class="badge badge-warning">Không có kho nào được phân quyền</span>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($model->managedWarehouses): ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Kho đang quản lý</label>
                                <div>
                                <?php foreach ($model->managedWarehouses as $warehouse): ?>
                                    <span class="badge badge-success mr-2"><?= $warehouse->name ?></span>
                                <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Thông tin hệ thống -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Thông tin hệ thống</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Ngày tạo</label>
                                <p class="form-control-static"><?= Yii::$app->formatter->asDatetime($model->created_at, 'php:d/m/Y H:i:s') ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Cập nhật lần cuối</label>
                                <p class="form-control-static"><?= Yii::$app->formatter->asDatetime($model->updated_at, 'php:d/m/Y H:i:s') ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Đăng nhập lần cuối</label>
                                <p class="form-control-static"><?= $model->last_login_at ? Yii::$app->formatter->asDatetime($model->last_login_at, 'php:d/m/Y H:i:s') : 'Chưa đăng nhập' ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>ID</label>
                                <p class="form-control-static"><?= $model->id ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>