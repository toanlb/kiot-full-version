<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>

<!-- Navbar -->
<nav class="main-header navbar navbar-expand-md navbar-primary navbar-dark">
    <div class="container-fluid">
        <!-- Brand Logo -->
        <a href="<?= Url::home() ?>" class="navbar-brand">
            <img src="<?= Yii::$app->request->baseUrl ?>/images/logo.png" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
            <span class="brand-text font-weight-light">POS Bán Hàng</span>
        </a>
        
        <button class="navbar-toggler order-1" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Left navbar links -->
        <div class="collapse navbar-collapse order-3" id="navbarCollapse">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a href="<?= Url::home() ?>" class="nav-link <?= (Yii::$app->controller->id == 'site' && Yii::$app->controller->action->id == 'index') ? 'active' : '' ?>">
                        <i class="fas fa-home"></i> Trang chủ
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= Url::to(['/pos/index']) ?>" class="nav-link <?= (Yii::$app->controller->id == 'pos') ? 'active' : '' ?>">
                        <i class="fas fa-shopping-cart"></i> Bán hàng (POS)
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Right navbar links -->
        <ul class="order-1 order-md-3 navbar-nav navbar-no-expand ml-auto">
            <!-- User dropdown -->
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <img src="<?= Yii::$app->request->baseUrl ?>/images/avatar/default.png" class="img-circle elevation-2" alt="User Image" style="width: 30px; height: 30px; margin-right: 5px;">
                    admin
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a href="<?= Url::to(['/user/profile']) ?>" class="dropdown-item">
                        <i class="fas fa-user mr-2"></i> Hồ sơ
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="<?= Url::to(['/site/logout']) ?>" class="dropdown-item" data-method="post">
                        <i class="fas fa-sign-out-alt mr-2"></i> Đăng xuất
                    </a>
                </div>
            </li>
            
            <!-- Toggle fullscreen -->
            <li class="nav-item">
                <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                    <i class="fas fa-expand-arrows-alt"></i>
                </a>
            </li>
        </ul>
    </div>
</nav>
<!-- /.navbar -->