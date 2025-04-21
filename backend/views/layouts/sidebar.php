<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="<?= Yii::$app->homeUrl ?>" class="brand-link d-flex align-items-center justify-content-center py-3 border-bottom-0">
        <img src="<?= Yii::$app->request->baseUrl ?>/img/logo.png" alt="ZPlus Kiot Logo" class="brand-image img-circle elevation-3" style="opacity: .9; max-width: 34px;">
        <span class="brand-text font-weight-bold ml-1" style="letter-spacing: 0.5px;">ZPlus<span class="font-weight-light">Kiot</span></span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <div class="position-relative">
                    <img src="<?= Yii::$app->request->baseUrl ?>/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
                    <span class="position-absolute rounded-circle bg-success" style="width: 12px; height: 12px; bottom: 0; right: 0; border: 2px solid #343a40;"></span>
                </div>
            </div>
            <div class="info ml-2">
                <a href="#" class="d-block text-bold text-white">
                    <?= Yii::$app->user->identity->full_name ?>
                    <small class="d-block text-muted font-weight-normal mt-1" style="font-size: 11px;"><i class="fas fa-circle text-success mr-1" style="font-size: 8px;"></i> Online</small>
                </a>
            </div>
            <div class="dropdown ml-auto mr-2 align-self-center">
                <a href="#" class="text-white" data-toggle="dropdown">
                    <i class="fas fa-ellipsis-v"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-user-cog mr-2"></i> Thông tin cá nhân
                    </a>
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-cogs mr-2"></i> Cài đặt
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="<?= \yii\helpers\Url::to(['/site/logout']) ?>" class="dropdown-item text-danger" data-method="post">
                        <i class="fas fa-sign-out-alt mr-2"></i> Đăng xuất
                    </a>
                </div>
            </div>
        </div>

        <!-- SidebarSearch Form -->
        <div class="form-inline mt-2 mx-3">
            <div class="input-group input-group-sm" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar rounded-pill bg-light text-dark" type="search" placeholder="Tìm kiếm menu..." aria-label="Search">
                <div class="input-group-append">
                    <button class="btn btn-sidebar rounded-pill">
                        <i class="fas fa-search fa-fw"></i>
                    </button>
                </div>
            </div>
        </div>

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
            'customer' => ['customer', 'customer-group'],
            'supplier' => ['supplier', 'supplier-debt'],
            'warranty' => ['warranty', 'warranty-status'],
            'finance' => ['receipt', 'payment', 'cash-book', 'shift'],
            'report' => ['report/sales', 'report/inventory', 'report/finance', 'report/customer'],
            'user' => ['user'],
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

        <!-- Sidebar Menu -->
        <nav class="mt-3">
            <ul class="nav nav-pills nav-sidebar nav-child-indent flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="<?= Yii::$app->homeUrl ?>" class="nav-link <?= $currentUrl === 'site' && $currentAction === 'index' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                
                <!-- Quản lý sản phẩm -->
                <li class="nav-item <?= $activeGroup === 'product' ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= $activeGroup === 'product' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-box"></i>
                        <p>
                            Quản lý sản phẩm
                            <i class="fas fa-angle-left right"></i>
                            <span class="badge badge-info right">4</span>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/product']) ?>" class="nav-link <?= $currentUrl === 'product' ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Danh sách sản phẩm</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/product-category']) ?>" class="nav-link <?= $currentUrl === 'product-category' ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Danh mục sản phẩm</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/product-unit']) ?>" class="nav-link <?= $currentUrl === 'product-unit' ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Đơn vị tính</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/product-attribute']) ?>" class="nav-link <?= $currentUrl === 'product-attribute' ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Thuộc tính sản phẩm</p>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Quản lý kho -->
                <li class="nav-item <?= $activeGroup === 'warehouse' ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= $activeGroup === 'warehouse' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-warehouse"></i>
                        <p>
                            Quản lý kho
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/warehouse']) ?>" class="nav-link <?= $currentUrl === 'warehouse' ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Danh sách kho</p>
                            </a>
                        </li>
                        <!-- Các menu con khác... -->
                    </ul>
                </li>
                
                <!-- Các nhóm menu khác tương tự -->
                
                <!-- Phần menu user -->
                <li class="nav-header mt-3 text-uppercase text-muted pl-3">HỆ THỐNG</li>
                
                <li class="nav-item <?= $activeGroup === 'user' ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= $activeGroup === 'user' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-user-cog"></i>
                        <p>
                            Quản lý người dùng
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/user']) ?>" class="nav-link <?= $currentUrl === 'user' ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Danh sách người dùng</p>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <li class="nav-item <?= $activeGroup === 'setting' ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= $activeGroup === 'setting' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-cogs"></i>
                        <p>
                            Cài đặt hệ thống
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/setting']) ?>" class="nav-link <?= $currentUrl === 'setting' ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Tham số hệ thống</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/log']) ?>" class="nav-link <?= $currentUrl === 'log' ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Nhật ký hệ thống</p>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>

<style>
/* Custom styles for modern sidebar */
.main-sidebar {
    box-shadow: 0 0 30px rgba(0,0,0,0.2);
    background: linear-gradient(180deg, #2b3035 0%, #1a1d20 100%);
}

.sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link.active {
    background: linear-gradient(90deg, #007bff 0%, #0056b3 100%);
    box-shadow: 0 3px 8px rgba(0, 123, 255, 0.4);
    border-radius: 5px;
}

.nav-sidebar .nav-link {
    border-radius: 5px;
    margin: 2px 8px;
    transition: all 0.3s;
}

.nav-sidebar .nav-link:hover {
    background-color: rgba(255, 255, 255, 0.1);
    transform: translateX(3px);
}

.nav-treeview > .nav-item > .nav-link.active,
.nav-treeview > .nav-item > .nav-link.active:hover {
    background: rgba(255, 255, 255, 0.15);
    color: #fff;
}

.user-panel {
    position: relative;
}

.user-panel::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 15px;
    right: 15px;
    height: 1px;
    background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.1) 50%, rgba(255,255,255,0) 100%);
}

.nav-header {
    font-size: 0.75rem;
    letter-spacing: 1px;
}

.badge-info {
    background-color: #17a2b8;
    font-weight: 400;
    font-size: 0.65rem;
    padding: 3px 6px;
}

.sidebar-search-results .list-group-item {
    background: #343a40;
    color: #fff;
    border-color: rgba(255,255,255,0.1);
}

.form-control-sidebar {
    padding-left: 15px;
}
</style>