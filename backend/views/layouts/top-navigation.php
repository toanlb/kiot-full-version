<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<!-- jQuery UI -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<?php
// Lấy URL hiện tại để kiểm tra menu được chọn
$currentUrl = Yii::$app->controller->id;
$currentAction = Yii::$app->controller->action->id;
$currentRoute = $currentUrl . '/' . $currentAction;

// Định nghĩa các nhóm menu và các menu con
$menuGroups = [
    'product' => ['product', 'product-category', 'product-unit', 'product-attribute'],
    'warehouse' => ['warehouse', 'stock', 'stock-in', 'stock-out', 'stock-transfer', 'stock-check'],
    'order' => ['order', 'return', 'discount'],
    'customer' => ['customer', 'customer-group', 'customer-debt'],
    'supplier' => ['supplier', 'supplier-product', 'supplier-debt'],
    'warranty' => ['warranty', 'warranty-status'],
    'finance' => ['receipt', 'payment', 'cash-book', 'shift'],
    'report' => ['report', 'report/sales', 'report/inventory', 'report/finance', 'report/customer'],
    'user' => ['user', 'user-profile', 'auth'],
    'setting' => ['setting', 'log']
];

// Kiểm tra menu hiện tại thuộc nhóm nào
$activeGroup = '';
foreach ($menuGroups as $group => $items) {
    if (in_array($currentUrl, $items) || in_array($currentRoute, $items)) {
        $activeGroup = $group;
        break;
    }
}
?>

<nav class="main-header navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <!-- Brand Logo -->
        <a href="<?= Yii::$app->homeUrl ?>" class="navbar-brand">
            <img src="<?= Yii::$app->request->baseUrl ?>/img/logo.png" alt="ZPlus Kiot Logo" class="brand-image img-circle elevation-3" style="opacity: .8; max-height: 33px;">
            <span class="brand-text font-weight-light ml-1">ZPlus Kiot</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link <?= $currentUrl === 'site' && $currentAction === 'index' ? 'active' : '' ?>" href="<?= Yii::$app->homeUrl ?>">
                        <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                    </a>
                </li>
                
                <!-- Quản lý sản phẩm -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= $activeGroup === 'product' ? 'active' : '' ?>" href="#" id="productDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-box mr-1"></i> Sản phẩm
                    </a>
                    <div class="dropdown-menu animate slideIn" aria-labelledby="productDropdown">
                        <a class="dropdown-item <?= $currentUrl === 'product' ? 'active' : '' ?>" href="<?= \yii\helpers\Url::to(['/product']) ?>">
                            <i class="far fa-list-alt mr-2"></i> Danh sách sản phẩm
                        </a>
                        <a class="dropdown-item <?= $currentUrl === 'product-category' ? 'active' : '' ?>" href="<?= \yii\helpers\Url::to(['/product-category']) ?>">
                            <i class="fas fa-tags mr-2"></i> Danh mục sản phẩm
                        </a>
                        <a class="dropdown-item <?= $currentUrl === 'product-unit' ? 'active' : '' ?>" href="<?= \yii\helpers\Url::to(['/product-unit']) ?>">
                            <i class="fas fa-ruler mr-2"></i> Đơn vị tính
                        </a>
                        <a class="dropdown-item <?= $currentUrl === 'product-attribute' ? 'active' : '' ?>" href="<?= \yii\helpers\Url::to(['/product-attribute']) ?>">
                            <i class="fas fa-sliders-h mr-2"></i> Thuộc tính sản phẩm
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="<?= \yii\helpers\Url::to(['/product/create']) ?>">
                            <i class="fas fa-plus-circle mr-2 text-success"></i> Thêm sản phẩm mới
                        </a>
                    </div>
                </li>
                
                <!-- Quản lý kho -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= $activeGroup === 'warehouse' ? 'active' : '' ?>" href="#" id="warehouseDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-warehouse mr-1"></i> Kho hàng
                    </a>
                    <div class="dropdown-menu animate slideIn" aria-labelledby="warehouseDropdown">
                        <a class="dropdown-item <?= $currentUrl === 'warehouse' ? 'active' : '' ?>" href="<?= \yii\helpers\Url::to(['/warehouse']) ?>">
                            <i class="fas fa-building mr-2"></i> Danh sách kho
                        </a>
                        <a class="dropdown-item <?= $currentUrl === 'stock' ? 'active' : '' ?>" href="<?= \yii\helpers\Url::to(['/stock']) ?>">
                            <i class="fas fa-cubes mr-2"></i> Tồn kho
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item <?= $currentUrl === 'stock-in' ? 'active' : '' ?>" href="<?= \yii\helpers\Url::to(['/stock-in']) ?>">
                            <i class="fas fa-truck-loading mr-2"></i> Nhập kho
                        </a>
                        <a class="dropdown-item <?= $currentUrl === 'stock-out' ? 'active' : '' ?>" href="<?= \yii\helpers\Url::to(['/stock-out']) ?>">
                            <i class="fas fa-dolly mr-2"></i> Xuất kho
                        </a>
                        <a class="dropdown-item <?= $currentUrl === 'stock-transfer' ? 'active' : '' ?>" href="<?= \yii\helpers\Url::to(['/stock-transfer']) ?>">
                            <i class="fas fa-exchange-alt mr-2"></i> Chuyển kho
                        </a>
                        <a class="dropdown-item <?= $currentUrl === 'stock-check' ? 'active' : '' ?>" href="<?= \yii\helpers\Url::to(['/stock-check']) ?>">
                            <i class="fas fa-clipboard-check mr-2"></i> Kiểm kê kho
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-warning" href="<?= \yii\helpers\Url::to(['/stock/low-stock']) ?>">
                            <i class="fas fa-exclamation-triangle mr-2"></i> Sản phẩm sắp hết hàng
                        </a>
                    </div>
                </li>
                
                <!-- Nhà cung cấp -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= $activeGroup === 'supplier' ? 'active' : '' ?>" href="#" id="supplierDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-truck mr-1"></i> Nhà cung cấp
                    </a>
                    <div class="dropdown-menu animate slideIn" aria-labelledby="supplierDropdown">
                        <a class="dropdown-item <?= $currentUrl === 'supplier' ? 'active' : '' ?>" href="<?= \yii\helpers\Url::to(['/supplier']) ?>">
                            <i class="fas fa-industry mr-2"></i> Danh sách nhà cung cấp
                        </a>
                        <a class="dropdown-item <?= $currentUrl === 'supplier-product' ? 'active' : '' ?>" href="<?= \yii\helpers\Url::to(['/supplier-product']) ?>">
                            <i class="fas fa-boxes mr-2"></i> Sản phẩm nhà cung cấp
                        </a>
                        <a class="dropdown-item <?= $currentUrl === 'supplier-debt' ? 'active' : '' ?>" href="<?= \yii\helpers\Url::to(['/supplier-debt']) ?>">
                            <i class="fas fa-file-invoice-dollar mr-2"></i> Công nợ nhà cung cấp
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="<?= \yii\helpers\Url::to(['/supplier/create']) ?>">
                            <i class="fas fa-plus-circle mr-2 text-success"></i> Thêm nhà cung cấp mới
                        </a>
                    </div>
                </li>
                
                <!-- Khách hàng -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= $activeGroup === 'customer' ? 'active' : '' ?>" href="#" id="customerDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-users mr-1"></i> Khách hàng
                    </a>
                    <div class="dropdown-menu animate slideIn" aria-labelledby="customerDropdown">
                        <a class="dropdown-item <?= $currentUrl === 'customer' ? 'active' : '' ?>" href="<?= \yii\helpers\Url::to(['/customer']) ?>">
                            <i class="fas fa-address-book mr-2"></i> Danh sách khách hàng
                        </a>
                        <a class="dropdown-item <?= $currentUrl === 'customer-group' ? 'active' : '' ?>" href="<?= \yii\helpers\Url::to(['/customer-group']) ?>">
                            <i class="fas fa-layer-group mr-2"></i> Nhóm khách hàng
                        </a>
                        <a class="dropdown-item <?= $currentUrl === 'customer-debt' ? 'active' : '' ?>" href="<?= \yii\helpers\Url::to(['/customer-debt']) ?>">
                            <i class="fas fa-hand-holding-usd mr-2"></i> Công nợ khách hàng
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="<?= \yii\helpers\Url::to(['/customer/create']) ?>">
                            <i class="fas fa-plus-circle mr-2 text-success"></i> Thêm khách hàng mới
                        </a>
                    </div>
                </li>
                
                <!-- Đơn hàng -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= $activeGroup === 'order' ? 'active' : '' ?>" href="#" id="orderDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-shopping-cart mr-1"></i> Đơn hàng
                    </a>
                    <div class="dropdown-menu animate slideIn" aria-labelledby="orderDropdown">
                        <a class="dropdown-item <?= $currentUrl === 'order' ? 'active' : '' ?>" href="<?= \yii\helpers\Url::to(['/order']) ?>">
                            <i class="fas fa-clipboard-list mr-2"></i> Danh sách đơn hàng
                        </a>
                        <a class="dropdown-item <?= $currentUrl === 'return' ? 'active' : '' ?>" href="<?= \yii\helpers\Url::to(['/return']) ?>">
                            <i class="fas fa-undo-alt mr-2"></i> Đơn trả hàng
                        </a>
                        <a class="dropdown-item <?= $currentUrl === 'discount' ? 'active' : '' ?>" href="<?= \yii\helpers\Url::to(['/discount']) ?>">
                            <i class="fas fa-percent mr-2"></i> Khuyến mãi
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="<?= \yii\helpers\Url::to(['/order/create']) ?>">
                            <i class="fas fa-plus-circle mr-2 text-success"></i> Tạo đơn hàng mới
                        </a>
                        <a class="dropdown-item" href="<?= \yii\helpers\Url::to(['/pos']) ?>">
                            <i class="fas fa-cash-register mr-2 text-primary"></i> Màn hình bán hàng (POS)
                        </a>
                    </div>
                </li>
                
                <!-- Báo cáo -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= $activeGroup === 'report' ? 'active' : '' ?>" href="#" id="reportDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-chart-bar mr-1"></i> Báo cáo
                    </a>
                    <div class="dropdown-menu animate slideIn" aria-labelledby="reportDropdown">
                        <a class="dropdown-item <?= $currentRoute === 'report/sales' ? 'active' : '' ?>" href="<?= \yii\helpers\Url::to(['/report/sales']) ?>">
                            <i class="fas fa-chart-line mr-2"></i> Báo cáo bán hàng
                        </a>
                        <a class="dropdown-item <?= $currentRoute === 'report/inventory' ? 'active' : '' ?>" href="<?= \yii\helpers\Url::to(['/report/inventory']) ?>">
                            <i class="fas fa-chart-area mr-2"></i> Báo cáo kho hàng
                        </a>
                        <a class="dropdown-item <?= $currentRoute === 'report/finance' ? 'active' : '' ?>" href="<?= \yii\helpers\Url::to(['/report/finance']) ?>">
                            <i class="fas fa-chart-pie mr-2"></i> Báo cáo tài chính
                        </a>
                        <a class="dropdown-item <?= $currentRoute === 'report/customer' ? 'active' : '' ?>" href="<?= \yii\helpers\Url::to(['/report/customer']) ?>">
                            <i class="fas fa-user-chart mr-2"></i> Báo cáo khách hàng
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="<?= \yii\helpers\Url::to(['/report/export']) ?>">
                            <i class="fas fa-file-export mr-2"></i> Xuất báo cáo
                        </a>
                    </div>
                </li>
                
                <!-- Người dùng và Cài đặt -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= $activeGroup === 'user' || $activeGroup === 'setting' ? 'active' : '' ?>" href="#" id="settingsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-cogs mr-1"></i> Hệ thống
                    </a>
                    <div class="dropdown-menu animate slideIn" aria-labelledby="settingsDropdown">
                        <h6 class="dropdown-header">Quản lý người dùng</h6>
                        <a class="dropdown-item <?= $currentUrl === 'user' ? 'active' : '' ?>" href="<?= \yii\helpers\Url::to(['/user']) ?>">
                            <i class="fas fa-users-cog mr-2"></i> Danh sách người dùng
                        </a>
                        <a class="dropdown-item <?= $currentUrl === 'user-profile' ? 'active' : '' ?>" href="<?= \yii\helpers\Url::to(['/user-profile']) ?>">
                            <i class="fas fa-id-card mr-2"></i> Hồ sơ người dùng
                        </a>
                        <a class="dropdown-item <?= $currentUrl === 'auth' ? 'active' : '' ?>" href="<?= \yii\helpers\Url::to(['/auth']) ?>">
                            <i class="fas fa-lock mr-2"></i> Phân quyền
                        </a>
                        <div class="dropdown-divider"></div>
                        <h6 class="dropdown-header">Cài đặt hệ thống</h6>
                        <a class="dropdown-item <?= $currentUrl === 'setting' ? 'active' : '' ?>" href="<?= \yii\helpers\Url::to(['/setting']) ?>">
                            <i class="fas fa-sliders-h mr-2"></i> Tham số hệ thống
                        </a>
                        <a class="dropdown-item <?= $currentUrl === 'log' ? 'active' : '' ?>" href="<?= \yii\helpers\Url::to(['/log']) ?>">
                            <i class="fas fa-history mr-2"></i> Nhật ký hệ thống
                        </a>
                    </div>
                </li>
            </ul>
            
            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <!-- Quick Actions Menu -->
                <li class="nav-item dropdown">
                    <a class="nav-link" href="#" id="quickActionsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-bolt"></i>
                        <span class="badge badge-primary navbar-badge">+</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right animate slideIn">
                        <span class="dropdown-item dropdown-header">Thao tác nhanh</span>
                        <div class="dropdown-divider"></div>
                        <a href="<?= \yii\helpers\Url::to(['/product/create']) ?>" class="dropdown-item">
                            <i class="fas fa-plus mr-2 text-success"></i> Thêm sản phẩm mới
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="<?= \yii\helpers\Url::to(['/order/create']) ?>" class="dropdown-item">
                            <i class="fas fa-cart-plus mr-2 text-primary"></i> Tạo đơn hàng mới
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="<?= \yii\helpers\Url::to(['/stock-in/create']) ?>" class="dropdown-item">
                            <i class="fas fa-truck-loading mr-2 text-info"></i> Tạo phiếu nhập kho
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="<?= \yii\helpers\Url::to(['/customer/create']) ?>" class="dropdown-item">
                            <i class="fas fa-user-plus mr-2 text-warning"></i> Thêm khách hàng mới
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="<?= \yii\helpers\Url::to(['/pos']) ?>" class="dropdown-item dropdown-footer bg-primary text-white">
                            <i class="fas fa-cash-register mr-2"></i> Màn hình bán hàng (POS)
                        </a>
                    </div>
                </li>
                
                <!-- Notifications Dropdown Menu -->
                <li class="nav-item dropdown">
                    <a class="nav-link" href="#" id="notificationsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="far fa-bell"></i>
                        <span class="badge badge-warning navbar-badge">15</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right animate slideIn">
                        <span class="dropdown-item dropdown-header">15 Thông báo</span>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-shopping-cart mr-2 text-primary"></i> 4 đơn hàng mới
                            <span class="float-right text-muted text-sm">3 phút</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-tools mr-2 text-warning"></i> 8 yêu cầu bảo hành
                            <span class="float-right text-muted text-sm">12 giờ</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-exclamation-triangle mr-2 text-danger"></i> 3 sản phẩm sắp hết hàng
                            <span class="float-right text-muted text-sm">2 ngày</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item dropdown-footer">Xem tất cả thông báo</a>
                    </div>
                </li>
                
                <!-- User Account Menu -->
                <li class="nav-item dropdown user-menu">
                    <a href="#" class="nav-link dropdown-toggle" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img src="<?= Yii::$app->request->baseUrl ?>/img/user2-160x160.jpg" class="user-image img-circle" alt="User Image">
                        <span class="d-none d-md-inline"><?= Yii::$app->user->identity->full_name ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right animate slideIn">
                        <!-- User image -->
                        <li class="user-header bg-primary">
                            <img src="<?= Yii::$app->request->baseUrl ?>/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
                            <p>
                                <?= Yii::$app->user->identity->full_name ?>
                                <small>Thành viên từ <?= date('m/Y', Yii::$app->user->identity->created_at) ?></small>
                            </p>
                        </li>
                        <!-- Menu Body -->
                        <li class="user-body">
                            <div class="row">
                                <div class="col-4 text-center">
                                    <a href="<?= \yii\helpers\Url::to(['/user/profile']) ?>" class="btn btn-default btn-flat btn-sm">Hồ sơ</a>
                                </div>
                                <div class="col-4 text-center">
                                    <a href="<?= \yii\helpers\Url::to(['/user/settings']) ?>" class="btn btn-default btn-flat btn-sm">Cài đặt</a>
                                </div>
                                <div class="col-4 text-center">
                                    <a href="<?= \yii\helpers\Url::to(['/user/activity']) ?>" class="btn btn-default btn-flat btn-sm">Hoạt động</a>
                                </div>
                            </div>
                        </li>
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            <a href="<?= \yii\helpers\Url::to(['/user/profile']) ?>" class="btn btn-default btn-flat">Thông tin cá nhân</a>
                            <a href="<?= \yii\helpers\Url::to(['/site/logout']) ?>" class="btn btn-danger btn-flat float-right" data-method="post">Đăng xuất</a>
                        </li>
                    </ul>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<style>
/* Fix margin-left cho top-navigation */
.main-header {
    margin-left: 0 !important;
}

/* Đảm bảo wrapper và content không có margin-left */
.wrapper {
    margin-left: 0 !important;
}

.content-wrapper {
    margin-left: 0 !important;
    min-height: calc(100vh - 57px - 58px); /* navbar height - footer height */
}

/* Đảm bảo footer không có margin-left */
.main-footer {
    margin-left: 0 !important;
}

/* Custom styles for top navigation */
.navbar-dark.bg-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.navbar-brand img {
    filter: drop-shadow(0 1px 3px rgba(0, 0, 0, 0.3));
}

/* Menu item styling */
.nav-link {
    position: relative;
    transition: all 0.3s;
    padding: 0.6rem 1rem;
    white-space: nowrap;
}

.nav-link.active {
    background-color: rgba(255, 255, 255, 0.15);
    border-radius: 4px;
}

.nav-link:hover {
    transform: translateY(-2px);
}

/* Dropdown styling - IMPROVED HOVER BEHAVIOR */
.dropdown-menu {
    border: none;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
    border-radius: 6px;
    padding: 8px;
    margin-top: 10px;
    /* Add a small delay for dropdown disappearing */
    transition: visibility 0s linear 200ms, opacity 200ms ease;
}

/* Expanded hover area for dropdown menu */
.dropdown-menu::before {
    content: '';
    position: absolute;
    top: -20px; /* Create invisible area above dropdown */
    left: 0;
    right: 0;
    height: 20px;
}

.dropdown-item {
    border-radius: 4px;
    padding: 8px 12px;
    font-size: 0.95rem;
    transition: all 0.2s;
}

.dropdown-item.active, 
.dropdown-item:active {
    background-color: #007bff;
    color: white;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
    transform: translateX(3px);
}

.dropdown-header {
    color: #6c757d;
    font-weight: 600;
    padding: 8px 12px;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.dropdown-divider {
    margin: 0.5rem 0;
}

/* Badge styling */
.navbar-badge {
    font-size: 0.6rem;
    font-weight: 300;
    padding: 2px 4px;
    position: absolute;
    right: 5px;
    top: 9px;
}

/* Animation effects */
.animate {
    animation-duration: 0.3s;
    animation-fill-mode: both;
}

.slideIn {
    animation-name: slideIn;
}

@keyframes slideIn {
    0% {
        transform: translateY(10px);
        opacity: 0;
    }
    100% {
        transform: translateY(0);
        opacity: 1;
    }
}

/* User menu styling */
.user-image {
    width: 25px;
    height: 25px;
    margin-right: 5px;
    margin-top: -2px;
}

.user-header {
    padding: 1.5rem;
    text-align: center;
    height: auto !important;
}

.user-header img {
    width: 90px;
    height: 90px;
    object-fit: cover;
    border: 3px solid rgba(255, 255, 255, 0.3);
}

.user-header p {
    margin-top: 10px;
    color: #fff;
    font-size: 1.1rem;
}

.user-header small {
    color: #f8f9fa;
}

.user-body {
    padding: 10px;
    background-color: #f8f9fa;
}

.user-footer {
    padding: 15px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background-color: #f8f9fa;
}

.user-footer .btn {
    font-size: 0.9rem;
    padding: 0.375rem 0.75rem;
}

/* Create bridge element for improved hover experience */
.hover-bridge {
    position: absolute;
    height: 20px;
    left: 0;
    right: 0;
    top: -20px;
    background: transparent;
}

/* Responsive adjustments */
@media (max-width: 992px) {
    .navbar-collapse {
        background-color: #0062cc;
        padding: 15px;
        border-radius: 0 0 5px 5px;
        margin-top: 10px;
        max-height: 80vh;
        overflow-y: auto;
    }
    
    .dropdown-menu {
        background-color: rgba(0, 0, 0, 0.1);
        box-shadow: none;
        border: none;
        margin-top: 0;
    }
    
    .dropdown-item {
        color: rgba(255, 255, 255, 0.8);
    }
    
    .dropdown-item:hover {
        background-color: rgba(255, 255, 255, 0.1);
        color: #fff;
    }
    
    .dropdown-item.active {
        background-color: rgba(255, 255, 255, 0.2);
    }
    
    .dropdown-divider {
        border-color: rgba(255, 255, 255, 0.1);
    }
    
    .dropdown-header {
        color: rgba(255, 255, 255, 0.6);
    }
}

/* Ripple effect for buttons and menu items */
.ripple {
    position: relative;
    overflow: hidden;
}

.ripple:after {
    content: "";
    display: block;
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    pointer-events: none;
    background-image: radial-gradient(circle, #fff 10%, transparent 10.01%);
    background-repeat: no-repeat;
    background-position: 50%;
    transform: scale(10, 10);
    opacity: 0;
    transition: transform .5s, opacity 1s;
}

.ripple:active:after {
    transform: scale(0, 0);
    opacity: .3;
    transition: 0s;
}
</style>

<script>
// Ensure jQuery is loaded
$(document).ready(function() {
    // Improved hover behavior for dropdown menus
    // Adding a delay before hiding the dropdown
    let timeout;
    
    $('.dropdown').on({
        mouseenter: function() {
            if ($(window).width() >= 992) {
                clearTimeout(timeout);
                $('.dropdown-menu').removeClass('show');
                $(this).find('.dropdown-menu').addClass('show');
            }
        },
        mouseleave: function() {
            if ($(window).width() >= 992) {
                const $dropdownMenu = $(this).find('.dropdown-menu');
                timeout = setTimeout(function() {
                    $dropdownMenu.removeClass('show');
                }, 300); // 300ms delay before hiding
            }
        }
    });
    
    // Prevent dropdown from closing when hovering over its menu
    $('.dropdown-menu').on({
        mouseenter: function() {
            if ($(window).width() >= 992) {
                clearTimeout(timeout);
            }
        },
        mouseleave: function() {
            if ($(window).width() >= 992) {
                const $this = $(this);
                timeout = setTimeout(function() {
                    $this.removeClass('show');
                }, 300); // 300ms delay before hiding
            }
        },
        click: function(e) {
            // Prevent clicks on dropdown menu from closing it
            e.stopPropagation();
        }
    });
    
    // Add hover-bridge element to each dropdown menu for better hover experience
    $('.dropdown-menu').each(function() {
        $(this).prepend('<div class="hover-bridge"></div>');
    });

    // Add ripple effect to buttons and menu items
    $('.btn, .nav-link, .dropdown-item').addClass('ripple');

    // Auto-collapse navbar on click on mobile
    $('.nav-link').on('click', function() {
        if ($(window).width() < 992 && !$(this).hasClass('dropdown-toggle')) {
            $('.navbar-toggler').click();
        }
    });
    
    // Fix touch devices - convert first click to hover
    if ('ontouchstart' in document.documentElement) {
        $('.dropdown-toggle').on('click', function(e) {
            const $this = $(this);
            const $parent = $this.parent();
            const isShown = $parent.hasClass('show');
            
            $('.dropdown').removeClass('show');
            $('.dropdown-menu').removeClass('show');
            
            if (!isShown) {
                $parent.addClass('show');
                $this.next('.dropdown-menu').addClass('show');
                e.preventDefault();
                e.stopPropagation();
            }
        });
    }
    
    // Dropdown items in mobile view
    $('.dropdown-item').on('click', function() {
        if ($(window).width() < 992) {
            $('.navbar-toggler').click();
        }
    });
    
    // Add accessibility support
    $('.dropdown-toggle').attr('aria-expanded', 'false');
    $('.dropdown-toggle').on('click', function() {
        $(this).attr('aria-expanded', $(this).parent().hasClass('show') ? 'true' : 'false');
    });
    
    // Make dropdown menus keyboard navigable
    $('.dropdown-toggle').on('keydown', function(e) {
        if (e.which === 13 || e.which === 32) { // Enter or Space
            $(this).click();
            e.preventDefault();
        }
    });
    
    $('.dropdown-item').on('keydown', function(e) {
        if (e.which === 13 || e.which === 32) { // Enter or Space
            $(this).click();
            e.preventDefault();
        }
    });
});
</script>