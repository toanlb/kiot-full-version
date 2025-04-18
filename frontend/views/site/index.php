<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $activeShift common\models\Shift */
/* @var $todayOrders array */
/* @var $topProducts array */
/* @var $lowStockProducts array */
/* @var $recentOrders array */

$this->title = 'Quản lý POS';
?>

<!-- Chỉ số KPI và Ca làm việc - Thông tin quan trọng nhất đặt ở trên cùng -->
<div class="row">
    <!-- Thông tin ca làm việc - Đặt trước tiên vì đây là điều kiện để bán hàng -->
    <div class="col-md-4">
        <div class="small-box bg-info">
            <div class="inner">
                <h3><?= $activeShift ? Yii::$app->formatter->asDatetime($activeShift->start_time, 'php:H:i') : 'Chưa mở ca' ?></h3>
                <p>Ca làm việc</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
            <?php if (!$activeShift): ?>
                <a href="<?= Url::to(['shift/create']) ?>" class="small-box-footer">
                    Mở ca làm việc <i class="fas fa-arrow-circle-right"></i>
                </a>
            <?php else: ?>
                <a href="<?= Url::to(['shift/view', 'id' => $activeShift->id]) ?>" class="small-box-footer">
                    Chi tiết ca làm việc <i class="fas fa-arrow-circle-right"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Đơn hàng hôm nay -->
    <div class="col-md-4">
        <div class="small-box bg-success">
            <div class="inner">
                <h3><?= $todayOrders['count'] ?? 0 ?></h3>
                <p>Đơn hàng hôm nay</p>
            </div>
            <div class="icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <a href="<?= Url::to(['order/today']) ?>" class="small-box-footer">
                Chi tiết <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <!-- Doanh thu hôm nay -->
    <div class="col-md-4">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3><?= Yii::$app->formatter->asDecimal($todayOrders['revenue'] ?? 0, 0) ?>đ</h3>
                <p>Doanh thu hôm nay</p>
            </div>
            <div class="icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <a href="<?= Url::to(['report/daily']) ?>" class="small-box-footer">
                Chi tiết <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Chức năng chính - Đặt ngay dưới KPI để dễ tiếp cận -->
<div class="card card-primary card-outline card-outline-tabs">
    <div class="card-header p-0 border-bottom-0">
        <ul class="nav nav-tabs" id="custom-tabs-four-tab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="custom-tabs-four-pos-tab" data-toggle="pill" href="#custom-tabs-four-pos" role="tab" aria-controls="custom-tabs-four-pos" aria-selected="true">
                    <i class="fas fa-star mr-1"></i> Chức năng chính
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="custom-tabs-four-advanced-tab" data-toggle="pill" href="#custom-tabs-four-advanced" role="tab" aria-controls="custom-tabs-four-advanced" aria-selected="false">
                    <i class="fas fa-cogs mr-1"></i> Chức năng nâng cao
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="custom-tabs-four-tabContent">
            <!-- Chức năng chính - Tab đầu tiên -->
            <div class="tab-pane fade show active" id="custom-tabs-four-pos" role="tabpanel" aria-labelledby="custom-tabs-four-pos-tab">
                <div class="row">
                    <!-- Màn hình bán hàng - Chức năng quan trọng nhất -->
                    <div class="col-lg-3 col-md-4 col-6 mb-3">
                        <a href="<?= Url::to(['/pos/index']) ?>" class="btn btn-lg btn-primary btn-block d-flex flex-column align-items-center p-4">
                            <i class="fas fa-cash-register fa-3x mb-3"></i>
                            <span class="h5 mb-0">Bán hàng</span>
                        </a>
                    </div>
                    
                    <!-- Đổi/trả hàng - Chức năng thường dùng -->
                    <div class="col-lg-3 col-md-4 col-6 mb-3">
                        <a href="<?= Url::to(['/return/index']) ?>" class="btn btn-lg btn-danger btn-block d-flex flex-column align-items-center p-4">
                            <i class="fas fa-undo-alt fa-3x mb-3"></i>
                            <span class="h5 mb-0">Đổi/trả hàng</span>
                        </a>
                    </div>
                    
                    <!-- Quản lý đơn hàng - Xem lại hóa đơn đã bán -->
                    <div class="col-lg-3 col-md-4 col-6 mb-3">
                        <a href="<?= Url::to(['/order/index']) ?>" class="btn btn-lg btn-success btn-block d-flex flex-column align-items-center p-4">
                            <i class="fas fa-file-invoice-dollar fa-3x mb-3"></i>
                            <span class="h5 mb-0">Hóa đơn</span>
                        </a>
                    </div>
                    
                    <!-- Khách hàng - Quản lý thông tin khách -->
                    <div class="col-lg-3 col-md-4 col-6 mb-3">
                        <a href="<?= Url::to(['/customer/index']) ?>" class="btn btn-lg btn-info btn-block d-flex flex-column align-items-center p-4">
                            <i class="fas fa-users fa-3x mb-3"></i>
                            <span class="h5 mb-0">Khách hàng</span>
                        </a>
                    </div>
                    
                    <!-- Ca làm việc - Quản lý ca -->
                    <div class="col-lg-3 col-md-4 col-6 mb-3">
                        <a href="<?= Url::to(['/shift/index']) ?>" class="btn btn-lg btn-warning btn-block d-flex flex-column align-items-center p-4">
                            <i class="fas fa-clock fa-3x mb-3"></i>
                            <span class="h5 mb-0">Quản lý ca</span>
                        </a>
                    </div>
                    
                    <!-- Bảo hành -->
                    <div class="col-lg-3 col-md-4 col-6 mb-3">
                        <a href="<?= Url::to(['/warranty/index']) ?>" class="btn btn-lg btn-secondary btn-block d-flex flex-column align-items-center p-4">
                            <i class="fas fa-shield-alt fa-3x mb-3"></i>
                            <span class="h5 mb-0">Bảo hành</span>
                        </a>
                    </div>
                    
                    <!-- In hóa đơn -->
                    <div class="col-lg-3 col-md-4 col-6 mb-3">
                        <a href="<?= Url::to(['/print/index']) ?>" class="btn btn-lg btn-dark btn-block d-flex flex-column align-items-center p-4">
                            <i class="fas fa-print fa-3x mb-3"></i>
                            <span class="h5 mb-0">In hóa đơn</span>
                        </a>
                    </div>
                    
                    <!-- Báo cáo -->
                    <div class="col-lg-3 col-md-4 col-6 mb-3">
                        <a href="<?= Url::to(['/report/index']) ?>" class="btn btn-lg btn-light btn-block d-flex flex-column align-items-center p-4">
                            <i class="fas fa-chart-bar fa-3x mb-3"></i>
                            <span class="h5 mb-0">Báo cáo</span>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Chức năng nâng cao - Tab thứ hai -->
            <div class="tab-pane fade" id="custom-tabs-four-advanced" role="tabpanel" aria-labelledby="custom-tabs-four-advanced-tab">
                <div class="row">
                    <!-- Sản phẩm -->
                    <div class="col-lg-3 col-md-4 col-6 mb-3">
                        <a href="<?= Url::to(['/product/index']) ?>" class="btn btn-outline-primary btn-block py-3">
                            <i class="fas fa-box fa-lg mb-2"></i><br>
                            Quản lý sản phẩm
                        </a>
                    </div>
                    
                    <!-- Mẫu in -->
                    <div class="col-lg-3 col-md-4 col-6 mb-3">
                        <a href="<?= Url::to(['/print-template/index']) ?>" class="btn btn-outline-success btn-block py-3">
                            <i class="fas fa-file-invoice fa-lg mb-2"></i><br>
                            Tùy chỉnh mẫu in
                        </a>
                    </div>
                    
                    <!-- Phương thức thanh toán -->
                    <div class="col-lg-3 col-md-4 col-6 mb-3">
                        <a href="<?= Url::to(['/payment-method/index']) ?>" class="btn btn-outline-warning btn-block py-3">
                            <i class="fas fa-credit-card fa-lg mb-2"></i><br>
                            Phương thức thanh toán
                        </a>
                    </div>
                    
                    <!-- Cài đặt hệ thống -->
                    <div class="col-lg-3 col-md-4 col-6 mb-3">
                        <a href="<?= Url::to(['/setting/pos']) ?>" class="btn btn-outline-danger btn-block py-3">
                            <i class="fas fa-cog fa-lg mb-2"></i><br>
                            Cài đặt POS
                        </a>
                    </div>
                    
                    <!-- Quản lý kho -->
                    <div class="col-lg-3 col-md-4 col-6 mb-3">
                        <a href="<?= Url::to(['/warehouse/index']) ?>" class="btn btn-outline-info btn-block py-3">
                            <i class="fas fa-warehouse fa-lg mb-2"></i><br>
                            Quản lý kho
                        </a>
                    </div>
                    
                    <!-- Nhà cung cấp -->
                    <div class="col-lg-3 col-md-4 col-6 mb-3">
                        <a href="<?= Url::to(['/supplier/index']) ?>" class="btn btn-outline-secondary btn-block py-3">
                            <i class="fas fa-truck fa-lg mb-2"></i><br>
                            Nhà cung cấp
                        </a>
                    </div>
                    
                    <!-- Người dùng -->
                    <div class="col-lg-3 col-md-4 col-6 mb-3">
                        <a href="<?= Url::to(['/user/index']) ?>" class="btn btn-outline-dark btn-block py-3">
                            <i class="fas fa-user-cog fa-lg mb-2"></i><br>
                            Quản lý người dùng
                        </a>
                    </div>
                    
                    <!-- Nhật ký hệ thống -->
                    <div class="col-lg-3 col-md-4 col-6 mb-3">
                        <a href="<?= Url::to(['/log/index']) ?>" class="btn btn-outline-light btn-block py-3">
                            <i class="fas fa-history fa-lg mb-2"></i><br>
                            Nhật ký hệ thống
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Đơn hàng gần đây - Thông tin thường cần xem -->
    <div class="col-md-8">
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-shopping-cart mr-2"></i> Đơn hàng gần đây</h3>
                <div class="card-tools">
                    <a href="<?= Url::to(['/order/index']) ?>" class="btn btn-tool">
                        <i class="fas fa-list"></i> Xem tất cả
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Khách hàng</th>
                            <th>Tổng tiền</th>
                            <th>Phương thức</th>
                            <th>Trạng thái</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>ORD000001</td>
                            <td>Khách lẻ</td>
                            <td>100,000đ</td>
                            <td><span class="badge badge-info">Tiền mặt</span></td>
                            <td><span class="badge badge-success">Hoàn thành</span></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="#" class="btn btn-info"><i class="fas fa-eye"></i></a>
                                    <a href="#" class="btn btn-secondary"><i class="fas fa-print"></i></a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>ORD000002</td>
                            <td>Khách lẻ</td>
                            <td>200,000đ</td>
                            <td><span class="badge badge-warning">Chuyển khoản</span></td>
                            <td><span class="badge badge-success">Hoàn thành</span></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="#" class="btn btn-info"><i class="fas fa-eye"></i></a>
                                    <a href="#" class="btn btn-secondary"><i class="fas fa-print"></i></a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>ORD000003</td>
                            <td>Khách lẻ</td>
                            <td>150,000đ</td>
                            <td><span class="badge badge-info">Tiền mặt</span></td>
                            <td><span class="badge badge-success">Hoàn thành</span></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="#" class="btn btn-info"><i class="fas fa-eye"></i></a>
                                    <a href="#" class="btn btn-secondary"><i class="fas fa-print"></i></a>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Sản phẩm sắp hết hàng - Cảnh báo quan trọng -->
    <div class="col-md-4">
        <div class="card card-danger card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-exclamation-triangle mr-2"></i> Sản phẩm sắp hết hàng</h3>
                <div class="card-tools">
                    <a href="<?= Url::to(['/stock/low-stock']) ?>" class="btn btn-tool">
                        <i class="fas fa-list"></i> Xem tất cả
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Tồn kho</th>
                            <th>Tối thiểu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Sản phẩm mẫu 1</td>
                            <td class="text-danger font-weight-bold">2</td>
                            <td>10</td>
                        </tr>
                        <tr>
                            <td>Sản phẩm mẫu 2</td>
                            <td class="text-danger font-weight-bold">3</td>
                            <td>5</td>
                        </tr>
                        <tr>
                            <td>Sản phẩm mẫu 3</td>
                            <td class="text-danger font-weight-bold">1</td>
                            <td>15</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Thông tin về ca làm việc nếu đang mở ca -->
<?php if ($activeShift): ?>
<div class="card card-info card-outline collapsed-card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-clock mr-2"></i> Thông tin ca làm việc hiện tại</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-plus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-striped">
                    <tr>
                        <th>Ca làm việc:</th>
                        <td><?= Yii::$app->formatter->asDatetime($activeShift->start_time) ?></td>
                    </tr>
                    <tr>
                        <th>Thu ngân:</th>
                        <td><?= $activeShift->cashier ? $activeShift->cashier->username : $activeShift->user->username ?></td>
                    </tr>
                    <tr>
                        <th>Kho:</th>
                        <td><?= $activeShift->warehouse->name ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-striped">
                    <tr>
                        <th>Tiền đầu ca:</th>
                        <td><?= Yii::$app->formatter->asDecimal($activeShift->opening_amount, 0) ?>đ</td>
                    </tr>
                    <tr>
                        <th>Doanh thu:</th>
                        <td><?= Yii::$app->formatter->asDecimal($activeShift->total_sales, 0) ?>đ</td>
                    </tr>
                    <tr>
                        <th>Tổng tiền quỹ:</th>
                        <td><?= Yii::$app->formatter->asDecimal($activeShift->expected_amount, 0) ?>đ</td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="text-center mt-3">
            <a href="<?= Url::to(['shift/close', 'id' => $activeShift->id]) ?>" class="btn btn-danger">
                <i class="fas fa-sign-out-alt"></i> Kết ca
            </a>
            <a href="<?= Url::to(['shift/view', 'id' => $activeShift->id]) ?>" class="btn btn-info">
                <i class="fas fa-eye"></i> Xem chi tiết
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Thêm script để xử lý sự kiện -->
<?php
$this->registerJs("
    // Xử lý khi tab được nhấp
    $('#custom-tabs-four-tab a').on('click', function (e) {
        e.preventDefault();
        $(this).tab('show');
    });
");
?>