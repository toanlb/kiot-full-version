<?php
/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap4\Modal;
use frontend\assets\PosAsset;

// Register the asset bundle
PosAsset::register($this);

// Application name
$appName = Yii::$app->name;

// Get current user
$user = Yii::$app->user->identity;

// Base URL for JS
$baseUrl = Url::base(true);
$this->registerJs("const baseUrl = '{$baseUrl}';", \yii\web\View::POS_HEAD);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?> - <?= Html::encode($appName) ?></title>
    <?php $this->head() ?>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        .pos-wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        .pos-navbar {
            background-color: #343a40;
            padding: 0.5rem 1rem;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-brand {
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
        }
        
        .navbar-brand img {
            height: 32px;
            margin-right: 10px;
        }
        
        .navbar-right {
            display: flex;
            align-items: center;
        }
        
        .navbar-right .user-info {
            margin-right: 15px;
            display: flex;
            align-items: center;
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: #5c6bc0;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 8px;
        }
        
        .dropdown-toggle::after {
            display: none;
        }
        
        .pos-content {
            flex: 1;
            padding: 15px;
        }
        
        .dropdown-menu {
            right: 0;
            left: auto;
        }
        
        .pos-footer {
            background-color: #343a40;
            color: rgba(255, 255, 255, 0.6);
            padding: 10px;
            text-align: center;
            font-size: 0.85rem;
        }
        
        @media (max-width: 768px) {
            .navbar-right .d-none {
                display: none !important;
            }
        }
    </style>
</head>
<body>
<?php $this->beginBody() ?>

<div class="pos-wrapper">
    <!-- Navbar -->
    <nav class="pos-navbar">
        <a class="navbar-brand" href="<?= Url::home() ?>">
            <img src="<?= Yii::getAlias('@web/images/logo.png') ?>" alt="Logo">
            <?= Html::encode($appName) ?> POS
        </a>
        
        <div class="navbar-right">
            <div class="user-info">
                <div class="user-avatar">
                    <?= substr($user->username, 0, 1) ?>
                </div>
                <div class="d-none d-md-block">
                    <?= Html::encode($user->full_name) ?>
                </div>
            </div>
            
            <div class="dropdown">
                <a class="btn btn-dark dropdown-toggle" href="#" role="button" id="userMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-cog"></i>
                </a>
                
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userMenu">
                    <a class="dropdown-item" href="<?= Url::to(['/dashboard']) ?>">
                        <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                    </a>
                    <a class="dropdown-item" href="<?= Url::to(['/user/profile']) ?>">
                        <i class="fas fa-user mr-2"></i> Thông tin cá nhân
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="<?= Url::to(['/site/logout']) ?>" data-method="post">
                        <i class="fas fa-sign-out-alt mr-2"></i> Đăng xuất
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="pos-content">
        <?= $content ?>
    </div>

    <!-- Footer -->
    <footer class="pos-footer">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    &copy; <?= date('Y') ?> <?= Html::encode($appName) ?> - Phiên bản 1.0
                </div>
            </div>
        </div>
    </footer>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>