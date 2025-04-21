<?php
/* @var $this yii\web\View */

$this->title = 'Dashboard';
$this->params['icon'] = 'fas fa-tachometer-alt';
$this->params['description'] = 'Tổng quan về hoạt động kinh doanh của bạn. Cập nhật vào ' . date('H:i, d/m/Y');

// Định dạng tiền tệ
$formatter = Yii::$app->formatter;
?>

<div class="dashboard-container">
    <!-- Thanh công cụ -->
    <div class="row mb-3">
        <div class="col-md-8">
            <div class="btn-group">
                <button type="button" class="btn btn-outline-primary" id="refresh-dashboard">
                    <i class="fas fa-sync-alt mr-1"></i> Làm mới dữ liệu
                </button>
                <button type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="sr-only">Toggle Dropdown</span>
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#timeRangeModal">
                        <i class="fas fa-calendar-alt mr-1"></i> Thay đổi khoảng thời gian
                    </a>
                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#dashboardSettingsModal">
                        <i class="fas fa-cog mr-1"></i> Tùy chỉnh dashboard
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="<?= \yii\helpers\Url::to(['/report/export-dashboard']) ?>">
                        <i class="fas fa-file-export mr-1"></i> Xuất dữ liệu
                    </a>
                </div>
            </div>
            <div class="btn-group ml-2">
                <button type="button" class="btn btn-outline-success">
                    <i class="fas fa-calendar-day mr-1"></i> Hôm nay
                </button>
                <button type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="sr-only">Toggle Dropdown</span>
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="#">Hôm nay</a>
                    <a class="dropdown-item" href="#">Hôm qua</a>
                    <a class="dropdown-item" href="#">7 ngày qua</a>
                    <a class="dropdown-item" href="#">30 ngày qua</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#">Tháng này</a>
                    <a class="dropdown-item" href="#">Tháng trước</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-right">
            <span class="text-muted">Cập nhật lần cuối: <span id="last-update-time"><?= date('d/m/Y H:i:s') ?></span></span>
        </div>
    </div>

    <!-- Thống kê tổng quan -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-primary rounded-lg shadow-sm">
                <div class="inner px-3 py-3">
                    <h3 class="mb-1" id="today-orders-count"><?= $todayOrders ?></h3>
                    <p class="mb-0">Đơn hàng hôm nay</p>
                </div>
                <div class="icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <a href="<?= \yii\helpers\Url::to(['/order']) ?>" class="small-box-footer py-2">
                    Chi tiết <i class="fas fa-arrow-circle-right ml-1"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-success rounded-lg shadow-sm">
                <div class="inner px-3 py-3">
                    <h3 class="mb-1" id="today-revenue"><?= $formatter->asCurrency($todayRevenue) ?></h3>
                    <p class="mb-0">Doanh thu hôm nay</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <a href="<?= \yii\helpers\Url::to(['/report/sales']) ?>" class="small-box-footer py-2">
                    Chi tiết <i class="fas fa-arrow-circle-right ml-1"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-warning rounded-lg shadow-sm">
                <div class="inner px-3 py-3">
                    <h3 class="mb-1 text-white" id="low-stock-count"><?= $lowStockProducts ?></h3>
                    <p class="mb-0 text-white">Sản phẩm sắp hết</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <a href="<?= \yii\helpers\Url::to(['/stock']) ?>" class="small-box-footer py-2">
                    Chi tiết <i class="fas fa-arrow-circle-right ml-1"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-info rounded-lg shadow-sm">
                <div class="inner px-3 py-3">
                    <h3 class="mb-1" id="new-customers-count"><?= $newCustomers ?></h3>
                    <p class="mb-0">Khách hàng mới</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="<?= \yii\helpers\Url::to(['/customer']) ?>" class="small-box-footer py-2">
                    Chi tiết <i class="fas fa-arrow-circle-right ml-1"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Biểu đồ và phân tích -->
    <div class="row">
        <!-- Biểu đồ doanh thu -->
        <div class="col-lg-8">
            <div class="card rounded-lg shadow-sm">
                <div class="card-header border-0 bg-transparent">
                    <div class="d-flex justify-content-between">
                        <h3 class="card-title text-bold">Doanh thu 30 ngày gần đây</h3>
                        <div class="card-tools d-flex">
                            <div class="btn-group btn-group-sm mr-2">
                                <button type="button" class="btn btn-outline-primary chart-type-switch active" data-target="revenue" data-type="line">
                                    <i class="fas fa-chart-line"></i>
                                </button>
                                <button type="button" class="btn btn-outline-primary chart-type-switch" data-target="revenue" data-type="bar">
                                    <i class="fas fa-chart-bar"></i>
                                </button>
                                <button type="button" class="btn btn-outline-primary chart-type-switch" data-target="revenue" data-type="area">
                                    <i class="fas fa-chart-area"></i>
                                </button>
                            </div>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown">
                                    <i class="fas fa-calendar mr-1"></i> 30 ngày
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item time-range-selector" href="#" data-chart="revenue" data-range="7">7 ngày</a>
                                    <a class="dropdown-item time-range-selector" href="#" data-chart="revenue" data-range="14">14 ngày</a>
                                    <a class="dropdown-item time-range-selector active" href="#" data-chart="revenue" data-range="30">30 ngày</a>
                                    <a class="dropdown-item time-range-selector" href="#" data-chart="revenue" data-range="90">90 ngày</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart">
                        <canvas id="revenueChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Top sản phẩm bán chạy -->
        <div class="col-lg-4">
            <div class="card rounded-lg shadow-sm">
                <div class="card-header border-0 bg-transparent">
                    <h3 class="card-title text-bold">Top sản phẩm bán chạy</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <ul class="products-list product-list-in-card pl-2 pr-2">
                        <?php foreach ($topProducts as $product): ?>
                        <li class="item">
                            <div class="product-img">
                                <?php $productImage = \common\models\ProductImage::findOne(['product_id' => $product['id'], 'is_main' => 1]); ?>
                                <img src="<?= $productImage ? Yii::$app->request->baseUrl . '/uploads/products/' . $productImage->image : Yii::$app->request->baseUrl . '/img/products/default.jpg' ?>" alt="Product Image" class="img-size-50">
                            </div>
                            <div class="product-info">
                                <a href="<?= \yii\helpers\Url::to(['/product/view', 'id' => $product['id']]) ?>" class="product-title">
                                    <?= $product['name'] ?>
                                    <span class="badge badge-success float-right"><?= $product['total_quantity'] ?> sản phẩm</span>
                                </a>
                                <span class="product-description">
                                    <?= $product['code'] ?>
                                </span>
                            </div>
                        </li>
                        <?php endforeach; ?>
                        
                        <?php if (empty($topProducts)): ?>
                        <li class="item">
                            <div class="text-center py-3">
                                <i class="fas fa-info-circle text-muted"></i> Chưa có dữ liệu sản phẩm bán chạy
                            </div>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="card-footer text-center">
                    <a href="<?= \yii\helpers\Url::to(['/report/products']) ?>" class="uppercase">Xem tất cả sản phẩm</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Hàng thứ 2 của dashboard -->
    <div class="row">
        <!-- Đơn hàng gần đây -->
        <div class="col-lg-8">
            <div class="card rounded-lg shadow-sm">
                <div class="card-header border-0 bg-transparent">
                    <h3 class="card-title text-bold">Đơn hàng gần đây</h3>
                    <div class="card-tools">
                        <a href="<?= \yii\helpers\Url::to(['/order']) ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye mr-1"></i> Xem tất cả
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Khách hàng</th>
                                    <th>Trạng thái</th>
                                    <th>Tổng tiền</th>
                                    <th>Thanh toán</th>
                                    <th>Thời gian</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td>
                                        <a href="<?= \yii\helpers\Url::to(['/order/view', 'id' => $order->id]) ?>">
                                            <?= $order->code ?>
                                        </a>
                                    </td>
                                    <td><?= $order->customer ? $order->customer->name : 'Khách lẻ' ?></td>
                                    <td>
                                        <?php
                                        switch ($order->status) {
                                            case 0:
                                                echo '<span class="badge badge-secondary">Nháp</span>';
                                                break;
                                            case 1:
                                                echo '<span class="badge badge-info">Xác nhận</span>';
                                                break;
                                            case 2:
                                                echo '<span class="badge badge-primary">Đã thanh toán</span>';
                                                break;
                                            case 3:
                                                echo '<span class="badge badge-warning">Đang giao</span>';
                                                break;
                                            case 4:
                                                echo '<span class="badge badge-success">Hoàn thành</span>';
                                                break;
                                            case 5:
                                                echo '<span class="badge badge-danger">Đã hủy</span>';
                                                break;
                                        }
                                        ?>
                                    </td>
                                    <td><?= $formatter->asCurrency($order->total_amount) ?></td>
                                    <td>
                                        <?php
                                        switch ($order->payment_status) {
                                            case 0:
                                                echo '<span class="badge badge-danger">Chưa thanh toán</span>';
                                                break;
                                            case 1:
                                                echo '<span class="badge badge-warning">Thanh toán một phần</span>';
                                                break;
                                            case 2:
                                                echo '<span class="badge badge-success">Đã thanh toán</span>';
                                                break;
                                        }
                                        ?>
                                    </td>
                                    <td><?= Yii::$app->formatter->asRelativeTime($order->order_date) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                
                                <?php if (empty($recentOrders)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-3">
                                        <i class="fas fa-info-circle text-muted"></i> Chưa có đơn hàng nào
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sản phẩm sắp hết hàng -->
        <div class="col-lg-4">
            <div class="card rounded-lg shadow-sm">
                <div class="card-header border-0 bg-transparent">
                    <h3 class="card-title text-bold">
                        <i class="fas fa-exclamation-triangle text-warning mr-1"></i> 
                        Sản phẩm sắp hết hàng
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <ul class="products-list product-list-in-card pl-2 pr-2">
                        <?php foreach ($lowStockProductsList as $stockItem): ?>
                        <li class="item">
                            <div class="product-img">
                                <?php $productImage = \common\models\ProductImage::findOne(['product_id' => $stockItem->product_id, 'is_main' => 1]); ?>
                                <img src="<?= $productImage ? Yii::$app->request->baseUrl . '/uploads/products/' . $productImage->image : Yii::$app->request->baseUrl . '/img/products/default.jpg' ?>" alt="Product Image" class="img-size-50">
                            </div>
                            <div class="product-info">
                                <a href="<?= \yii\helpers\Url::to(['/product/view', 'id' => $stockItem->product_id]) ?>" class="product-title">
                                    <?= $stockItem->product->name ?>
                                    <span class="badge badge-danger float-right">Còn <?= $stockItem->quantity ?></span>
                                </a>
                                <span class="product-description">
                                    <?= $stockItem->warehouse->name ?>
                                    <div class="progress progress-xs mt-1">
                                        <?php 
                                        $percent = min(100, $stockItem->quantity / $stockItem->product->min_stock * 100);
                                        $bgClass = $percent < 30 ? 'bg-danger' : ($percent < 70 ? 'bg-warning' : 'bg-success');
                                        ?>
                                        <div class="progress-bar <?= $bgClass ?>" style="width: <?= $percent ?>%"></div>
                                    </div>
                                </span>
                            </div>
                        </li>
                        <?php endforeach; ?>
                        
                        <?php if (empty($lowStockProductsList)): ?>
                        <li class="item">
                            <div class="text-center py-3">
                                <i class="fas fa-check-circle text-success"></i> Không có sản phẩm nào sắp hết hàng
                            </div>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="card-footer text-center">
                    <a href="<?= \yii\helpers\Url::to(['/stock/low-stock']) ?>" class="uppercase">Xem tất cả sản phẩm sắp hết hàng</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Hàng cuối cùng của dashboard -->
    <div class="row">
        <!-- Thống kê tài chính -->
        <div class="col-lg-6">
            <div class="card rounded-lg shadow-sm">
                <div class="card-header border-0 bg-transparent">
                    <h3 class="card-title text-bold">Tổng quan tài chính tháng này</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="chart-container">
                                <canvas id="financeChart" height="260"></canvas>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mt-4">
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span>Doanh thu</span>
                                        <span class="text-success"><?= $formatter->asCurrency($monthlyRevenue) ?></span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-success" style="width: 100%"></div>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span>Chi phí</span>
                                        <span class="text-danger"><?= $formatter->asCurrency($monthlyExpenses) ?></span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <?php $expensePercent = $monthlyRevenue > 0 ? min(100, $monthlyExpenses / $monthlyRevenue * 100) : 0; ?>
                                        <div class="progress-bar bg-danger" style="width: <?= $expensePercent ?>%"></div>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span>Lợi nhuận</span>
                                        <span class="text-primary"><?= $formatter->asCurrency($monthlyProfit) ?></span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <?php $profitPercent = $monthlyRevenue > 0 ? min(100, $monthlyProfit / $monthlyRevenue * 100) : 0; ?>
                                        <div class="progress-bar bg-primary" style="width: <?= $profitPercent ?>%"></div>
                                    </div>
                                </div>
                                <div class="text-center mt-4">
                                    <div class="small text-muted">Tỷ suất lợi nhuận</div>
                                    <div class="h3 text-bold"><?= number_format($profitMargin, 2) ?>%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-center bg-transparent">
                    <a href="<?= \yii\helpers\Url::to(['/report/finance']) ?>" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-chart-pie mr-1"></i> Xem báo cáo tài chính chi tiết
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Phiếu bảo hành mới -->
        <div class="col-lg-6">
            <div class="card rounded-lg shadow-sm">
                <div class="card-header border-0 bg-transparent">
                    <h3 class="card-title text-bold">
                        <i class="fas fa-tools mr-1"></i> Phiếu bảo hành mới
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Mã BH</th>
                                    <th>Sản phẩm</th>
                                    <th>Khách hàng</th>
                                    <th>Trạng thái</th>
                                    <th style="width: 110px">Ngày hết hạn</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($newWarranties as $warranty): ?>
                                <tr>
                                    <td><a href="<?= \yii\helpers\Url::to(['/warranty/view', 'id' => $warranty->id]) ?>"><?= $warranty->code ?></a></td>
                                    <td><?= $warranty->product->name ?></td>
                                    <td><?= $warranty->customer ? $warranty->customer->name : 'Khách lẻ' ?></td>
                                    <td>
                                        <span class="badge" style="background-color: <?= $warranty->status->color ?>">
                                            <?= $warranty->status->name ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $endDate = new \DateTime($warranty->end_date);
                                        $now = new \DateTime();
                                        $interval = $now->diff($endDate);
                                        $color = $endDate < $now ? 'danger' : ($interval->days < 30 ? 'warning' : 'success');
                                        ?>
                                        <span class="text-<?= $color ?>"><?= Yii::$app->formatter->asDate($warranty->end_date) ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                
                                <?php if (empty($newWarranties)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-3">
                                        <i class="fas fa-info-circle text-muted"></i> Không có phiếu bảo hành mới
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="<?= \yii\helpers\Url::to(['/warranty']) ?>" class="uppercase">Xem tất cả phiếu bảo hành</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Khoảng thời gian -->
<div class="modal fade" id="timeRangeModal" tabindex="-1" role="dialog" aria-labelledby="timeRangeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="timeRangeModalLabel">Chọn khoảng thời gian</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label>Khoảng thời gian</label>
                        <select class="form-control">
                            <option value="today">Hôm nay</option>
                            <option value="yesterday">Hôm qua</option>
                            <option value="last7days">7 ngày qua</option>
                            <option value="last30days" selected>30 ngày qua</option>
                            <option value="thisMonth">Tháng này</option>
                            <option value="lastMonth">Tháng trước</option>
                            <option value="custom">Tùy chỉnh</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Từ ngày</label>
                        <div class="input-group date" id="fromDatePicker" data-target-input="nearest">
                            <input type="text" class="form-control datepicker" data-target="#fromDatePicker"/>
                            <div class="input-group-append" data-target="#fromDatePicker" data-toggle="datetimepicker">
                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Đến ngày</label>
                        <div class="input-group date" id="toDatePicker" data-target-input="nearest">
                            <input type="text" class="form-control datepicker" data-target="#toDatePicker"/>
                            <div class="input-group-append" data-target="#toDatePicker" data-toggle="datetimepicker">
                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary">Áp dụng</button>
            </div>
        </div>
    </div>
</div>

<?php
// Dữ liệu cho biểu đồ doanh thu
$revenueLabels = json_encode($revenueData['labels']);
$revenueValues = json_encode($revenueData['revenue']);
$profitValues = json_encode($revenueData['profit']);

// Dữ liệu cho biểu đồ tài chính
$financeData = [
    'Doanh thu' => $monthlyRevenue,
    'Chi phí' => $monthlyExpenses,
    'Lợi nhuận' => $monthlyProfit
];
$financeLabels = json_encode(array_keys($financeData));
$financeValues = json_encode(array_values($financeData));

$js = <<<JS
// Khai báo biến toàn cục
var BASE_URL = '/';
var SHOW_NOTIFICATION = false;

// Biểu đồ doanh thu
var revenueCtx = document.getElementById('revenueChart').getContext('2d');
var revenueChart = new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: $revenueLabels,
        datasets: [{
            label: 'Doanh thu',
            data: $revenueValues,
            backgroundColor: 'rgba(60, 141, 188, 0.2)',
            borderColor: 'rgba(60, 141, 188, 1)',
            borderWidth: 2,
            pointRadius: 2,
            pointBackgroundColor: 'rgba(60, 141, 188, 1)',
            pointBorderColor: '#fff',
            pointHoverRadius: 5,
            tension: 0.4
        }, {
            label: 'Lợi nhuận',
            data: $profitValues,
            backgroundColor: 'rgba(40, 167, 69, 0.2)',
            borderColor: 'rgba(40, 167, 69, 1)',
            borderWidth: 2,
            pointRadius: 2,
            pointBackgroundColor: 'rgba(40, 167, 69, 1)',
            pointBorderColor: '#fff',
            pointHoverRadius: 5,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: false,
                grid: {
                    drawBorder: false
                },
                ticks: {
                    callback: function(value) {
                        return value.toLocaleString('vi-VN') + ' đ';
                    }
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        },
        plugins: {
            legend: {
                display: true,
                position: 'top'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ' + context.parsed.y.toLocaleString('vi-VN') + ' đ';
                    }
                }
            }
        }
    }
});

// Biểu đồ tài chính
var financeCtx = document.getElementById('financeChart').getContext('2d');
var financeChart = new Chart(financeCtx, {
    type: 'doughnut',
    data: {
        labels: $financeLabels,
        datasets: [{
            data: $financeValues,
            backgroundColor: [
                'rgba(40, 167, 69, 0.8)',
                'rgba(220, 53, 69, 0.8)',
                'rgba(0, 123, 255, 0.8)'
            ],
            borderColor: [
                'rgba(40, 167, 69, 1)',
                'rgba(220, 53, 69, 1)',
                'rgba(0, 123, 255, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'bottom'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        var value = context.raw;
                        value = value.toLocaleString('vi-VN') + ' đ';
                        return context.label + ': ' + value;
                    }
                }
            }
        }
    }
});
JS;
$this->registerJs($js);
?>