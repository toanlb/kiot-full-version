<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\User */
/* @var $groupedPermissions array */
/* @var $userPermissions array */

$this->title = 'Quản lý quyền hạn: ' . $model->full_name;
$this->params['breadcrumbs'][] = ['label' => 'Quản lý người dùng', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->full_name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Quản lý quyền hạn';

// Lấy authManager
$auth = Yii::$app->authManager;

// Lấy các quyền được gán trực tiếp cho user
$directPermissions = [];
foreach ($auth->getAssignments($model->id) as $name => $assignment) {
    $item = $auth->getPermission($name);
    if ($item) {
        $directPermissions[$name] = $item;
    }
}

// Danh sách tên hiển thị cho các nhóm quyền
$categoryLabels = [
    'view' => 'Xem',
    'create' => 'Tạo mới',
    'update' => 'Cập nhật',
    'delete' => 'Xóa',
    'manage' => 'Quản lý',
    'admin' => 'Quản trị',
    'report' => 'Báo cáo',
    'import' => 'Nhập dữ liệu',
    'export' => 'Xuất dữ liệu',
    'user' => 'Người dùng',
    'product' => 'Sản phẩm',
    'customer' => 'Khách hàng',
    'supplier' => 'Nhà cung cấp',
    'order' => 'Đơn hàng',
    'inventory' => 'Kho hàng',
    'finance' => 'Tài chính',
    'setting' => 'Cài đặt',
    'other' => 'Khác',
];
?>
<div class="user-manage-permissions">

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
        </div>
        <div class="card-body">
            <?php $form = ActiveForm::begin(); ?>

            <p class="alert alert-info">
                <i class="fas fa-info-circle"></i> Các quyền hạn được thừa kế từ vai trò sẽ hiển thị nhưng không thể thay đổi. 
                Bạn chỉ có thể thêm quyền hạn trực tiếp cho người dùng.
            </p>

            <div class="form-group">
                <div class="row">
                    <div class="col-md-12">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" class="form-control" id="permission-search" placeholder="Tìm kiếm quyền...">
                        </div>
                    </div>
                </div>
            </div>

            <div class="permissions-container">
                <?php foreach ($groupedPermissions as $category => $permissions): ?>
                    <div class="card mb-3 permission-group">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse-<?= $category ?>">
                                    <?= $categoryLabels[$category] ?? ucfirst($category) ?> (<?= count($permissions) ?>)
                                </button>
                                <div class="float-right">
                                    <label class="m-0">
                                        <input type="checkbox" class="select-all-category" data-category="<?= $category ?>"> Chọn tất cả
                                    </label>
                                </div>
                            </h5>
                        </div>
                        <div id="collapse-<?= $category ?>" class="collapse">
                            <div class="card-body">
                                <div class="row">
                                    <?php foreach ($permissions as $permission): ?>
                                        <div class="col-md-6 permission-item">
                                            <div class="form-check mb-2">
                                                <?php 
                                                $isDirectAssigned = isset($directPermissions[$permission->name]);
                                                $isInherited = isset($userPermissions[$permission->name]) && !$isDirectAssigned;
                                                ?>
                                                <input type="checkbox" 
                                                       class="form-check-input permission-checkbox" 
                                                       id="permission-<?= $permission->name ?>" 
                                                       name="permissions[]" 
                                                       value="<?= $permission->name ?>"
                                                       <?= $isDirectAssigned ? 'checked' : '' ?>
                                                       <?= $isInherited ? 'checked disabled' : '' ?>
                                                       data-category="<?= $category ?>">
                                                <label class="form-check-label" for="permission-<?= $permission->name ?>">
                                                    <?= $permission->name ?>
                                                    <?php if ($isInherited): ?>
                                                        <span class="badge badge-info">Thừa kế từ vai trò</span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($permission->description)): ?>
                                                        <br><small class="text-muted"><?= $permission->description ?></small>
                                                    <?php endif; ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="form-group">
                <?= Html::submitButton('<i class="fas fa-save"></i> Lưu thay đổi', ['class' => 'btn btn-success']) ?>
                <?= Html::a('<i class="fas fa-times"></i> Hủy', ['view', 'id' => $model->id], ['class' => 'btn btn-secondary']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

</div>

<?php
$js = <<<JS
// Tìm kiếm quyền
$('#permission-search').on('keyup', function() {
    var value = $(this).val().toLowerCase();
    $('.permission-item').filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
    });
    
    // Hiển thị/ẩn các nhóm quyền dựa trên kết quả tìm kiếm
    $('.permission-group').each(function() {
        var groupId = $(this).find('.collapse').attr('id');
        var visibleItems = $(this).find('.permission-item:visible').length;
        
        if (visibleItems > 0) {
            $(this).show();
            if (value) {
                $('#' + groupId).collapse('show');
            }
        } else {
            $(this).hide();
        }
    });
});

// Chọn tất cả quyền trong một nhóm
$('.select-all-category').on('change', function() {
    var category = $(this).data('category');
    var checked = $(this).prop('checked');
    
    $('.permission-checkbox[data-category="' + category + '"]:not(:disabled)').prop('checked', checked);
});
JS;

$this->registerJs($js);
?>