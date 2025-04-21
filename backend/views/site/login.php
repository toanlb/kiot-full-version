<?php
use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap4\ActiveForm */
/* @var $model \common\models\LoginForm */

$this->title = 'Đăng nhập';
?>

<div class="login-box">
    <div class="login-logo">
        <a href="<?= Yii::$app->homeUrl ?>" class="text-white">
            <b>ZPlus</b>Kiot
        </a>
    </div>
    
    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg">Đăng nhập để bắt đầu phiên làm việc</p>

            <?php $form = ActiveForm::begin([
                'id' => 'login-form',
                'options' => ['class' => 'login-form'],
                'fieldConfig' => [
                    'options' => ['class' => 'form-group'],
                    'template' => "{input}\n{error}",
                ],
            ]); ?>

            <div class="input-group mb-3">
                <?= $form->field($model, 'username', [
                    'inputOptions' => [
                        'class' => 'form-control',
                        'placeholder' => 'Tên đăng nhập',
                        'autofocus' => true,
                    ]
                ])->label(false) ?>
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-user"></span>
                    </div>
                </div>
            </div>

            <div class="input-group mb-3">
                <?= $form->field($model, 'password', [
                    'inputOptions' => [
                        'class' => 'form-control',
                        'placeholder' => 'Mật khẩu',
                    ]
                ])->passwordInput()->label(false) ?>
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-lock"></span>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-7">
                    <div class="form-group">
                        <?= $form->field($model, 'rememberMe', [
                            'template' => "<div class=\"icheck-primary\">{input} {label}</div>",
                            'labelOptions' => ['class' => ''],
                            'inputOptions' => ['class' => ''],
                        ])->checkbox() ?>
                    </div>
                </div>
                <div class="col-5">
                    <?= Html::submitButton('Đăng nhập', [
                        'class' => 'btn btn-primary btn-block',
                        'id' => 'login-button',
                    ]) ?>
                </div>
            </div>

            <?php ActiveForm::end(); ?>

            <p class="mb-1">
                <a href="#">Quên mật khẩu</a>
            </p>
            <p class="mb-0">
                <a href="#" class="text-center">Liên hệ quản trị viên</a>
            </p>
        </div>
    </div>
    
    <div class="text-center mt-3 text-white">
        <strong>Copyright &copy; <?= date('Y') ?> ZPlus Kiot</strong>
        <div>Phiên bản 1.0.0</div>
    </div>
</div>

<style>
/* Sửa lỗi giao diện form */
.login-box {
    width: 360px;
    margin: 0 auto;
}

.login-card-body {
    padding: 20px;
}

.input-group .form-control {
    border-right: 0;
}

.input-group-text {
    background-color: transparent;
}

.icheck-primary {
    display: flex;
    align-items: center;
}

.icheck-primary input[type="checkbox"] {
    margin-right: 5px;
}

#login-button {
    padding: 8px 12px;
    height: 38px;
}

/* Đảm bảo checkbox nằm trong form */
.login-form .form-group {
    margin-bottom: 0;
}

/* Cải thiện màu sắc và hiệu ứng */
.btn-primary {
    background-color: #007bff;
    border-color: #007bff;
    transition: all 0.3s;
}

.btn-primary:hover {
    background-color: #0069d9;
    border-color: #0062cc;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
}

/* Đảm bảo kích thước nút phù hợp */
.col-5 .btn-block {
    width: 100%;
    padding-left: 0;
    padding-right: 0;
}

/* Điều chỉnh checkbox remember me */
.col-7 .icheck-primary {
    white-space: nowrap;
}
</style>

<?php
$js = <<<JS
$(function () {
    // Hiệu ứng khi nhấn nút đăng nhập
    $('#login-form').on('submit', function() {
        $('#login-button').addClass('disabled').html('<i class="fas fa-circle-notch fa-spin"></i> Đang xử lý...');
    });
    
    // Fix lỗi input-group trong Bootstrap 4
    $('.input-group .form-control').on('focus', function() {
        $(this).parent().addClass('focused');
    }).on('blur', function() {
        $(this).parent().removeClass('focused');
    });
});
JS;
$this->registerJs($js);
?>