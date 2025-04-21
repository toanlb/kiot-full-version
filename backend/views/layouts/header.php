<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="<?= Yii::$app->homeUrl ?>" class="nav-link">Trang chủ</a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="#" class="nav-link" data-toggle="modal" data-target="#helpModal">Trợ giúp</a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <!-- Quick Actions Dropdown Menu -->
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="fas fa-bolt"></i>
                <span class="badge badge-primary navbar-badge">+</span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
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
            </div>
        </li>

        <!-- Notifications Dropdown Menu -->
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="far fa-bell"></i>
                <span class="badge badge-warning navbar-badge">15</span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
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

        <!-- User Dropdown Menu -->
        <li class="nav-item dropdown user-menu">
            <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                <img src="<?= Yii::$app->request->baseUrl ?>/img/user2-160x160.jpg" class="user-image img-circle elevation-2" alt="User Image">
                <span class="d-none d-md-inline"><?= Yii::$app->user->identity->full_name ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
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
                            <a href="#" class="btn btn-default btn-flat btn-sm">Hồ sơ</a>
                        </div>
                        <div class="col-4 text-center">
                            <a href="#" class="btn btn-default btn-flat btn-sm">Cài đặt</a>
                        </div>
                        <div class="col-4 text-center">
                            <a href="#" class="btn btn-default btn-flat btn-sm">Hoạt động</a>
                        </div>
                    </div>
                </li>
                <!-- Menu Footer-->
                <li class="user-footer">
                    <a href="#" class="btn btn-default btn-flat">Thông tin cá nhân</a>
                    <a href="<?= \yii\helpers\Url::to(['/site/logout']) ?>" class="btn btn-danger btn-flat float-right" data-method="post">Đăng xuất</a>
                </li>
            </ul>
        </li>
        
        <li class="nav-item">
            <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                <i class="fas fa-expand-arrows-alt"></i>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
                <i class="fas fa-cog"></i>
            </a>
        </li>
    </ul>
</nav>

<!-- Help Modal -->
<div class="modal fade" id="helpModal" tabindex="-1" role="dialog" aria-labelledby="helpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title" id="helpModalLabel">Trợ giúp nhanh</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="fas fa-book fa-3x text-primary mb-3"></i>
                                <h5>Hướng dẫn sử dụng</h5>
                                <p class="text-muted">Tài liệu chi tiết về cách sử dụng hệ thống</p>
                                <a href="#" class="btn btn-sm btn-outline-primary">Xem ngay</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="fas fa-video fa-3x text-success mb-3"></i>
                                <h5>Video hướng dẫn</h5>
                                <p class="text-muted">Các video hướng dẫn thao tác trên hệ thống</p>
                                <a href="#" class="btn btn-sm btn-outline-success">Xem ngay</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="fas fa-headset fa-3x text-danger mb-3"></i>
                                <h5>Hỗ trợ trực tiếp</h5>
                                <p class="text-muted">Liên hệ với đội ngũ hỗ trợ của chúng tôi</p>
                                <a href="#" class="btn btn-sm btn-outline-danger">Liên hệ ngay</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>