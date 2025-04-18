<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="<?= Yii::$app->homeUrl ?>" class="brand-link">
        <img src="<?= Yii::$app->request->baseUrl ?>/img/logo.png" alt="ZPlus Kiot Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">ZPlus Kiot</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="<?= Yii::$app->request->baseUrl ?>/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block"><?= Yii::$app->user->identity->full_name ?></a>
            </div>
        </div>

        <!-- SidebarSearch Form -->
        <div class="form-inline">
            <div class="input-group" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar" type="search" placeholder="Tìm kiếm" aria-label="Search">
                <div class="input-group-append">
                    <button class="btn btn-sidebar">
                        <i class="fas fa-search fa-fw"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="<?= Yii::$app->homeUrl ?>" class="nav-link active">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                
                <!-- Quản lý sản phẩm -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-box"></i>
                        <p>
                            Quản lý sản phẩm
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/product']) ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Danh sách sản phẩm</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/product-category']) ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Danh mục sản phẩm</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/product-unit']) ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Đơn vị tính</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/product-attribute']) ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Thuộc tính sản phẩm</p>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Quản lý kho -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-warehouse"></i>
                        <p>
                            Quản lý kho
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/warehouse']) ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Danh sách kho</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/stock']) ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Tồn kho</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/stock-in']) ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Nhập kho</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/stock-out']) ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Xuất kho</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/stock-transfer']) ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Chuyển kho</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/stock-check']) ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Kiểm kê kho</p>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Quản lý bán hàng -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-shopping-cart"></i>
                        <p>
                            Quản lý bán hàng
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/order']) ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Đơn hàng</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/return']) ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Đơn trả hàng</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/discount']) ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Khuyến mãi</p>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Quản lý khách hàng -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            Quản lý khách hàng
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/customer']) ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Danh sách khách hàng</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/customer-group']) ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Nhóm khách hàng</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/customer-debt']) ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Công nợ khách hàng</p>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Quản lý nhà cung cấp -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-truck"></i>
                        <p>
                            Quản lý nhà cung cấp
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/supplier']) ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Danh sách nhà cung cấp</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/supplier-debt']) ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Công nợ nhà cung cấp</p>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Quản lý bảo hành -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-tools"></i>
                        <p>
                            Quản lý bảo hành
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/warranty']) ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Phiếu bảo hành</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/warranty-status']) ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Trạng thái bảo hành</p>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Quản lý tài chính -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-money-bill"></i>
                        <p>
                            Quản lý tài chính
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/receipt']) ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Phiếu thu</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/payment']) ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Phiếu chi</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/cash-book']) ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Sổ quỹ</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/shift']) ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Ca làm việc</p>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Báo cáo thống kê -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-chart-bar"></i>
                        <p>
                            Báo cáo thống kê
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/report/sales']) ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Báo cáo bán hàng</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/report/inventory']) ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Báo cáo kho hàng</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/report/finance']) ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Báo cáo tài chính</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/report/customer']) ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Báo cáo khách hàng</p>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Quản lý người dùng -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-user-cog"></i>
                        <p>
                            Quản lý người dùng
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/user']) ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Danh sách người dùng</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/auth-item']) ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Phân quyền</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/login-history']) ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Lịch sử đăng nhập</p>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Cài đặt hệ thống -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-cogs"></i>
                        <p>
                            Cài đặt hệ thống
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/setting']) ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Tham số hệ thống</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= \yii\helpers\Url::to(['/log']) ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Nhật ký hệ thống</p>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Log out -->
                <li class="nav-item">
                    <a href="<?= \yii\helpers\Url::to(['/site/logout']) ?>" class="nav-link" data-method="post">
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        <p>Đăng xuất</p>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>