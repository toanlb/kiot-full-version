<?php
use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap4\ActiveForm */
/* @var $model \common\models\LoginForm */

$this->title = 'Đăng nhập';
?>

<div class="login-wrapper">
    <div class="login-container">
        <!-- Phần bên trái - Logo và thông tin -->
        <div class="login-left">
            <div class="brand-content">
                <div class="logo-container">
                    <img src="<?= Yii::$app->request->baseUrl ?>/images/logo.png" alt="Logo" class="brand-logo">
                </div>
                <h1 class="brand-title">POS Bán Hàng</h1>
                <p class="brand-description">Hệ thống quản lý bán hàng toàn diện</p>
                
                <div class="feature-boxes">
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="feature-content">
                            <h3>Bảo mật cao</h3>
                            <p>Hệ thống được bảo mật và mã hóa dữ liệu</p>
                        </div>
                    </div>
                    
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="fas fa-sync"></i>
                        </div>
                        <div class="feature-content">
                            <h3>Đồng bộ dữ liệu</h3>
                            <p>Đồng bộ dữ liệu thời gian thực</p>
                        </div>
                    </div>
                    
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="feature-content">
                            <h3>Báo cáo chi tiết</h3>
                            <p>Báo cáo đa dạng, xuất excel, pdf</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Phần bên phải - Form đăng nhập -->
        <div class="login-right">
            <div class="login-panel">
                <h2 class="login-title">Đăng nhập hệ thống</h2>
                
                <?php if(Yii::$app->session->hasFlash('error')): ?>
                <div class="alert alert-danger">
                    <i class="icon fas fa-ban"></i> <?= Yii::$app->session->getFlash('error') ?>
                </div>
                <?php endif; ?>
                
                <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
                
                <div class="form-group">
                    <label>Tên đăng nhập</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                        </div>
                        <?= $form->field($model, 'username', [
                            'options' => ['class' => ''],
                            'template' => '{input}{error}',
                            'inputOptions' => ['class' => 'form-control', 'placeholder' => 'Nhập tên đăng nhập']
                        ])->textInput(['autofocus' => true]) ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Mật khẩu</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        </div>
                        <?= $form->field($model, 'password', [
                            'options' => ['class' => ''],
                            'template' => '{input}{error}',
                            'inputOptions' => ['class' => 'form-control', 'placeholder' => 'Nhập mật khẩu']
                        ])->passwordInput() ?>
                        <div class="input-group-append">
                            <span class="input-group-text toggle-password">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="form-group remember-me">
                    <div class="custom-control custom-checkbox">
                        <?= $form->field($model, 'rememberMe', [
                            'template' => '{input} {label}',
                            'options' => ['class' => 'custom-control custom-checkbox'],
                            'inputOptions' => ['class' => 'custom-control-input'],
                            'labelOptions' => ['class' => 'custom-control-label'],
                        ])->checkbox([], false) ?>
                    </div>
                    <a href="#" class="forgot-password">Quên mật khẩu?</a>
                </div>
                
                <div class="form-group">
                    <?= Html::submitButton('<i class="fas fa-sign-in-alt mr-2"></i> Đăng nhập', [
                        'class' => 'btn btn-gradient btn-block', 
                        'name' => 'login-button'
                    ]) ?>
                </div>
                
                <?php ActiveForm::end(); ?>
                
                <div class="login-footer">
                    <p>Phiên bản: 1.0.0 © <?= date('Y') ?> POS Bán Hàng</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$this->registerCss("
/* Base Styles */
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap');

html, body {
    height: 100%;
    margin: 0;
    padding: 0;
    font-family: 'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
}

body {
    background-color: #f5f7fa;
}

/* Login Wrapper */
.login-wrapper {
    display: flex;
    height: 100vh;
    width: 100vw;
    align-items: stretch;
    background-color: #f5f7fa;
}

/* Login Container */
.login-container {
    display: flex;
    width: 100%;
    height: 100%;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

/* Left Side - Branding */
.login-left {
    flex: 1;
    background: linear-gradient(135deg, #1e88e5, #1565c0);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    position: relative;
}

.login-left::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml;utf8,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 100 100\" preserveAspectRatio=\"none\"><path d=\"M0,0 L100,0 L100,100 Z\" fill=\"rgba(255,255,255,0.08)\"/></svg>');
    background-size: cover;
}

.login-left::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml;utf8,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 100 100\" preserveAspectRatio=\"none\"><path d=\"M0,100 L100,0 L0,0 Z\" fill=\"rgba(255,255,255,0.05)\"/></svg>');
    background-size: cover;
}

.brand-content {
    max-width: 500px;
    padding: 40px;
    text-align: center;
    z-index: 1;
}

.logo-container {
    background-color: white;
    width: 140px;
    height: 140px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.brand-logo {
    width: 90px;
    height: 90px;
}

.brand-title {
    font-size: 42px;
    font-weight: 700;
    margin-bottom: 10px;
    letter-spacing: -0.5px;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.brand-description {
    font-size: 16px;
    opacity: 0.9;
    margin-bottom: 40px;
    font-weight: 300;
}

/* Feature Boxes */
.feature-boxes {
    display: flex;
    flex-direction: column;
    gap: 20px;
    margin-top: 40px;
}

.feature-box {
    background-color: rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    text-align: left;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.feature-box:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.feature-icon {
    width: 60px;
    height: 60px;
    background-color: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 20px;
    flex-shrink: 0;
}

.feature-icon i {
    font-size: 24px;
}

.feature-content h3 {
    font-size: 18px;
    font-weight: 600;
    margin: 0 0 8px 0;
}

.feature-content p {
    font-size: 14px;
    margin: 0;
    opacity: 0.8;
    line-height: 1.5;
}

/* Right Side - Login Form */
.login-right {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #f8fafc, #edf2f7);
    position: relative;
    overflow: hidden;
}

.login-right::before {
    content: '';
    position: absolute;
    width: 300%;
    height: 300%;
    top: -100%;
    left: -100%;
    background: radial-gradient(circle, rgba(30, 136, 229, 0.03) 0%, rgba(30, 136, 229, 0) 70%);
    z-index: 0;
}

.login-panel {
    width: 450px;
    max-width: 90%;
    padding: 40px;
    background-color: white;
    border-radius: 16px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    position: relative;
    z-index: 1;
}

.login-title {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 30px;
    text-align: center;
    color: #1565c0;
    letter-spacing: -0.5px;
}

/* Form Styles */
.form-group {
    margin-bottom: 25px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #3d5170;
    font-weight: 500;
    font-size: 15px;
}

.input-group {
    position: relative;
    box-shadow: 0 2px 8px rgba(30, 136, 229, 0.1);
    border-radius: 8px;
    overflow: hidden;
}

.input-group-text {
    background-color: #eef4ff;
    border: 1px solid #dbe7fb;
    border-right: none;
    color: #1e88e5;
}

.form-control {
    border: 1px solid #dbe7fb;
    border-radius: 0 4px 4px 0 !important;
    height: 48px;
    width: 100%;
    font-size: 15px;
    transition: border-color 0.3s ease;
    background-color: #f8fbff;
    color: #3d5170;
}

.form-control:focus {
    box-shadow: none;
    border-color: #1e88e5;
    background-color: white;
}

.input-group-append .input-group-text {
    border-left: none;
    border-right: 1px solid #dbe7fb;
    cursor: pointer;
}

.remember-me {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.forgot-password {
    color: #1e88e5;
    text-decoration: none;
    font-size: 14px;
    transition: color 0.3s ease;
}

.forgot-password:hover {
    color: #1565c0;
    text-decoration: underline;
}

.btn-gradient {
    background: linear-gradient(45deg, #1e88e5, #1565c0);
    border: none;
    padding: 12px 20px;
    font-size: 16px;
    font-weight: 600;
    height: 50px;
    border-radius: 8px;
    color: white;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 4px 12px rgba(30, 136, 229, 0.3);
}

.btn-gradient:hover {
    background: linear-gradient(45deg, #1976d2, #0d47a1);
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(30, 136, 229, 0.4);
}

.btn-gradient:active {
    transform: translateY(0);
    box-shadow: 0 2px 8px rgba(30, 136, 229, 0.3);
}

.login-footer {
    text-align: center;
    margin-top: 40px;
    color: #6b7c93;
    font-size: 13px;
}

/* Error Messages */
.help-block {
    color: #e53935;
    font-size: 13px;
    margin-top: 5px;
    font-weight: 500;
}

/* Alert Styling */
.alert {
    padding: 15px;
    margin-bottom: 25px;
    border-radius: 8px;
    border: none;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.alert-danger {
    background-color: #ffebee;
    color: #c62828;
    border-left: 4px solid #ef5350;
}

/* Custom Checkbox */
.custom-control-label {
    color: #3d5170;
    font-size: 14px;
}

.custom-control-input:checked~.custom-control-label::before {
    border-color: #1e88e5;
    background-color: #1e88e5;
}

.custom-checkbox .custom-control-input:checked~.custom-control-label::before {
    background-color: #1e88e5;
}

.custom-control-input:focus~.custom-control-label::before {
    box-shadow: 0 0 0 0.2rem rgba(30, 136, 229, 0.25);
}

/* Media Queries */
@media (max-width: 992px) {
    .login-container {
        flex-direction: column;
    }
    
    .login-left, .login-right {
        width: 100%;
        flex: none;
    }
    
    .login-left {
        height: 40%;
        min-height: 350px;
    }
    
    .login-right {
        height: 60%;
    }
    
    .brand-content {
        padding: 20px;
    }
    
    .logo-container {
        width: 100px;
        height: 100px;
    }
    
    .brand-logo {
        width: 60px;
        height: 60px;
    }
    
    .brand-title {
        font-size: 32px;
    }
    
    .feature-boxes {
        display: none;
    }
}

@media (max-width: 480px) {
    .login-panel {
        padding: 30px 20px;
    }
    
    .login-title {
        font-size: 24px;
    }
    
    .form-control, .btn-gradient {
        height: 45px;
    }
}
");
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Focus the first input field
    document.querySelector('#loginform-username').focus();
    
    // Toggle password visibility
    var passwordField = document.querySelector('#loginform-password');
    var togglePassword = document.querySelector('.toggle-password');
    
    if (togglePassword) {
        togglePassword.addEventListener('click', function() {
            // Toggle icon
            var icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
            
            // Toggle password visibility
            var type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
        });
    }
    
    // Add hover effect to feature boxes
    var featureBoxes = document.querySelectorAll('.feature-box');
    featureBoxes.forEach(function(box) {
        box.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 8px 20px rgba(0, 0, 0, 0.15)';
        });
        
        box.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'none';
        });
    });
});
</script>