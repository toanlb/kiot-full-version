<?php
/* @var $this yii\web\View */

$this->title = 'Dashboard';
?>

<!-- Info boxes -->
<div class="row">
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-shopping-cart"></i></span>

            <div class="info-box-content">
                <span class="info-box-text">Đơn hàng hôm nay</span>
                <span class="info-box-number">
                    15
                </span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <!-- /.col -->
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box mb-3">
            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-money-bill"></i></span>

            <div class="info-box-content">
                <span class="info-box-text">Doanh thu hôm nay</span>
                <span class="info-box-number">5,200,000 đ</span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <!-- /.col -->

    <!-- fix for small devices only -->
    <div class="clearfix hidden-md-up"></div>

    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box mb-3">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-users"></i></span>

            <div class="info-box-content">
                <span class="info-box-text">Khách hàng mới</span>
                <span class="info-box-number">7</span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <!-- /.col -->
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box mb-3">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-exclamation-triangle"></i></span>

            <div class="info-box-content">
                <span class="info-box-text">Sản phẩm sắp hết hàng</span>
                <span class="info-box-number">12</span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <!-- /.col -->
</div>
<!-- /.row -->

<div class="row">
    <div class="col-md-8">
        <!-- Biểu đồ doanh thu -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Báo cáo doanh thu</h5>

                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <p class="text-center">
                            <strong>Doanh thu: 1 Tháng 5, 2025 - 30 Tháng 5, 2025</strong>
                        </p>

                        <div class="chart">
                            <!-- Biểu đồ doanh thu sẽ hiển thị ở đây -->
                            <canvas id="salesChart" height="220" style="height: 220px;"></canvas>
                        </div>
                        <!-- /.chart-responsive -->
                    </div>
                    <!-- /.col -->
                    <div class="col-md-4">
                        <p class="text-center">
                            <strong>Chỉ tiêu hoàn thành</strong>
                        </p>

                        <div class="progress-group">
                            Đơn hàng hoàn thành
                            <span class="float-right"><b>160</b>/200</span>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-primary" style="width: 80%"></div>
                            </div>
                        </div>
                        <!-- /.progress-group -->

                        <div class="progress-group">
                            Doanh thu
                            <span class="float-right"><b>85</b>/100</span>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-danger" style="width: 85%"></div>
                            </div>
                        </div>

                        <!-- /.progress-group -->
                        <div class="progress-group">
                            <span class="progress-text">Khách hàng mới</span>
                            <span class="float-right"><b>30</b>/50</span>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-success" style="width: 60%"></div>
                            </div>
                        </div>

                        <!-- /.progress-group -->
                        <div class="progress-group">
                            Tỷ lệ chuyển đổi
                            <span class="float-right"><b>35</b>/50</span>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-warning" style="width: 70%"></div>
                            </div>
                        </div>
                        <!-- /.progress-group -->
                    </div>
                    <!-- /.col -->
                </div>
                <!-- /.row -->
            </div>
            <!-- ./card-body -->
            <div class="card-footer">
                <div class="row">
                    <div class="col-sm-3 col-6">
                        <div class="description-block border-right">
                            <span class="description-percentage text-success"><i class="fas fa-caret-up"></i> 17%</span>
                            <h5 class="description-header">35,210,000 đ</h5>
                            <span class="description-text">TỔNG DOANH THU</span>
                        </div>
                        <!-- /.description-block -->
                    </div>
                    <!-- /.col -->
                    <div class="col-sm-3 col-6">
                        <div class="description-block border-right">
                            <span class="description-percentage text-warning"><i class="fas fa-caret-left"></i> 0%</span>
                            <h5 class="description-header">10,390,000 đ</h5>
                            <span class="description-text">TỔNG CHI PHÍ</span>
                        </div>
                        <!-- /.description-block -->
                    </div>
                    <!-- /.col -->
                    <div class="col-sm-3 col-6">
                        <div class="description-block border-right">
                            <span class="description-percentage text-success"><i class="fas fa-caret-up"></i> 20%</span>
                            <h5 class="description-header">24,813,000 đ</h5>
                            <span class="description-text">TỔNG LỢI NHUẬN</span>
                        </div>
                        <!-- /.description-block -->
                    </div>
                    <!-- /.col -->
                    <div class="col-sm-3 col-6">
                        <div class="description-block">
                            <span class="description-percentage text-danger"><i class="fas fa-caret-down"></i> 18%</span>
                            <h5 class="description-header">70.5%</h5>
                            <span class="description-text">TỶ SUẤT LỢI NHUẬN</span>
                        </div>
                        <!-- /.description-block -->
                    </div>
                </div>
                <!-- /.row -->
            </div>
            <!-- /.card-footer -->
        </div>
        <!-- /.card -->
    </div>
    <!-- /.col -->
    
    <div class="col-md-4">
        <!-- Sản phẩm bán chạy -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Sản phẩm bán chạy nhất</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <!-- /.card-header -->
            <div class="card-body p-0">
                <ul class="products-list product-list-in-card pl-2 pr-2">
                    <li class="item">
                        <div class="product-img">
                            <img src="<?= Yii::$app->request->baseUrl ?>/img/default-150x150.png" alt="Product Image" class="img-size-50">
                        </div>
                        <div class="product-info">
                            <a href="javascript:void(0)" class="product-title">iPhone 11 Pro
                                <span class="badge badge-warning float-right">32 đã bán</span></a>
                            <span class="product-description">
                                Apple iPhone 11 Pro 256GB
                            </span>
                        </div>
                    </li>
                    <!-- /.item -->
                    <li class="item">
                        <div class="product-img">
                            <img src="<?= Yii::$app->request->baseUrl ?>/img/default-150x150.png" alt="Product Image" class="img-size-50">
                        </div>
                        <div class="product-info">
                            <a href="javascript:void(0)" class="product-title">Samsung Galaxy S20
                                <span class="badge badge-info float-right">27 đã bán</span></a>
                            <span class="product-description">
                                Samsung Galaxy S20 Ultra Black
                            </span>
                        </div>
                    </li>
                    <!-- /.item -->
                    <li class="item">
                        <div class="product-img">
                            <img src="<?= Yii::$app->request->baseUrl ?>/img/default-150x150.png" alt="Product Image" class="img-size-50">
                        </div>
                        <div class="product-info">
                            <a href="javascript:void(0)" class="product-title">
                                Laptop Dell XPS 13
                                <span class="badge badge-danger float-right">21 đã bán</span>
                            </a>
                            <span class="product-description">
                                Dell XPS 13 9380 i7 16GB RAM
                            </span>
                        </div>
                    </li>
                    <!-- /.item -->
                    <li class="item">
                        <div class="product-img">
                            <img src="<?= Yii::$app->request->baseUrl ?>/img/default-150x150.png" alt="Product Image" class="img-size-50">
                        </div>
                        <div class="product-info">
                            <a href="javascript:void(0)" class="product-title">Tai nghe AirPods Pro
                                <span class="badge badge-success float-right">18 đã bán</span></a>
                            <span class="product-description">
                                Apple AirPods Pro with Wireless Charging Case
                            </span>
                        </div>
                    </li>
                    <!-- /.item -->
                    <li class="item">
                        <div class="product-img">
                            <img src="<?= Yii::$app->request->baseUrl ?>/img/default-150x150.png" alt="Product Image" class="img-size-50">
                        </div>
                        <div class="product-info">
                            <a href="javascript:void(0)" class="product-title">iPad Pro 11
                                <span class="badge badge-primary float-right">15 đã bán</span></a>
                            <span class="product-description">
                                Apple iPad Pro 11-inch 256GB Wi-Fi
                            </span>
                        </div>
                    </li>
                    <!-- /.item -->
                </ul>
            </div>
            <!-- /.card-body -->
            <div class="card-footer text-center">
                <a href="<?= \yii\helpers\Url::to(['/report/sales']) ?>" class="uppercase">Xem tất cả sản phẩm</a>
            </div>
            <!-- /.card-footer -->
        </div>
        <!-- /.card -->
        
        <!-- Đơn hàng gần đây -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Đơn hàng gần đây</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <!-- /.card-header -->
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table m-0">
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Khách hàng</th>
                                <th>Trạng thái</th>
                                <th>Thanh toán</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><a href="#">ORD0001</a></td>
                                <td>Nguyễn Văn A</td>
                                <td><span class="badge badge-success">Hoàn thành</span></td>
                                <td>
                                    <div class="sparkbar" data-color="#00a65a" data-height="20">2,500,000 đ</div>
                                </td>
                            </tr>
                            <tr>
                                <td><a href="#">ORD0002</a></td>
                                <td>Trần Thị B</td>
                                <td><span class="badge badge-warning">Đang xử lý</span></td>
                                <td>
                                    <div class="sparkbar" data-color="#f39c12" data-height="20">1,800,000 đ</div>
                                </td>
                            </tr>
                            <tr>
                                <td><a href="#">ORD0003</a></td>
                                <td>Lê Văn C</td>
                                <td><span class="badge badge-primary">Đã thanh toán</span></td>
                                <td>
                                    <div class="sparkbar" data-color="#f56954" data-height="20">3,200,000 đ</div>
                                </td>
                            </tr>
                            <tr>
                                <td><a href="#">ORD0004</a></td>
                                <td>Hoàng Thị D</td>
                                <td><span class="badge badge-info">Đang giao</span></td>
                                <td>
                                    <div class="sparkbar" data-color="#00c0ef" data-height="20">2,100,000 đ</div>
                                </td>
                            </tr>
                            <tr>
                                <td><a href="#">ORD0005</a></td>
                                <td>Phạm Văn E</td>
                                <td><span class="badge badge-danger">Hủy</span></td>
                                <td>
                                    <div class="sparkbar" data-color="#f56954" data-height="20">0 đ</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- /.table-responsive -->
            </div>
            <!-- /.card-body -->
            <div class="card-footer clearfix">
                <a href="<?= \yii\helpers\Url::to(['/order/create']) ?>" class="btn btn-sm btn-info float-left">Tạo đơn hàng mới</a>
                <a href="<?= \yii\helpers\Url::to(['/order']) ?>" class="btn btn-sm btn-secondary float-right">Xem tất cả đơn hàng</a>
            </div>
            <!-- /.card-footer -->
        </div>
        <!-- /.card -->
    </div>
    <!-- /.col -->
</div>
<!-- /.row -->

<div class="row">
    <!-- Sản phẩm sắp hết hàng -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Sản phẩm sắp hết hàng</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <!-- /.card-header -->
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Mã SP</th>
                                <th>Tên sản phẩm</th>
                                <th>Kho</th>
                                <th>Tồn kho</th>
                                <th>Tồn tối thiểu</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>SP001</td>
                                <td>iPhone 11 Pro 256GB</td>
                                <td>Kho chính</td>
                                <td>2</td>
                                <td>5</td>
                                <td><span class="badge bg-danger">Sắp hết</span></td>
                            </tr>
                            <tr>
                                <td>SP002</td>
                                <td>Samsung Galaxy S20 Ultra</td>
                                <td>Kho chính</td>
                                <td>3</td>
                                <td>5</td>
                                <td><span class="badge bg-warning">Sắp hết</span></td>
                            </tr>
                            <tr>
                                <td>SP003</td>
                                <td>Laptop Dell XPS 15</td>
                                <td>Kho phụ</td>
                                <td>1</td>
                                <td>3</td>
                                <td><span class="badge bg-danger">Sắp hết</span></td>
                            </tr>
                            <tr>
                                <td>SP004</td>
                                <td>Sạc dự phòng Anker</td>
                                <td>Kho chính</td>
                                <td>4</td>
                                <td>10</td>
                                <td><span class="badge bg-warning">Sắp hết</span></td>
                            </tr>
                            <tr>
                                <td>SP005</td>
                                <td>Tai nghe Sony WH-1000XM4</td>
                                <td>Kho phụ</td>
                                <td>2</td>
                                <td>5</td>
                                <td><span class="badge bg-warning">Sắp hết</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- /.table-responsive -->
            </div>
            <!-- /.card-body -->
            <div class="card-footer clearfix">
                <a href="<?= \yii\helpers\Url::to(['/stock-in/create']) ?>" class="btn btn-sm btn-info float-left">Tạo phiếu nhập kho</a>
                <a href="<?= \yii\helpers\Url::to(['/report/inventory']) ?>" class="btn btn-sm btn-secondary float-right">Xem báo cáo tồn kho</a>
            </div>
            <!-- /.card-footer -->
        </div>
        <!-- /.card -->
    </div>
    <!-- /.col -->
    
    <!-- Phiếu bảo hành mới -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Phiếu bảo hành mới</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <!-- /.card-header -->
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Mã BH</th>
                                <th>Khách hàng</th>
                                <th>Sản phẩm</th>
                                <th>Ngày nhận</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><a href="#">BH0001</a></td>
                                <td>Nguyễn Văn A</td>
                                <td>iPhone 11 Pro</td>
                                <td>18/05/2025</td>
                                <td><span class="badge bg-warning">Chờ xử lý</span></td>
                            </tr>
                            <tr>
                                <td><a href="#">BH0002</a></td>
                                <td>Trần Thị B</td>
                                <td>Laptop Dell XPS 13</td>
                                <td>17/05/2025</td>
                                <td><span class="badge bg-primary">Đang xử lý</span></td>
                            </tr>
                            <tr>
                                <td><a href="#">BH0003</a></td>
                                <td>Lê Văn C</td>
                                <td>Samsung Galaxy S20</td>
                                <td>16/05/2025</td>
                                <td><span class="badge bg-warning">Chờ xử lý</span></td>
                            </tr>
                            <tr>
                                <td><a href="#">BH0004</a></td>
                                <td>Hoàng Thị D</td>
                                <td>Tai nghe AirPods Pro</td>
                                <td>15/05/2025</td>
                                <td><span class="badge bg-success">Hoàn thành</span></td>
                            </tr>
                            <tr>
                                <td><a href="#">BH0005</a></td>
                                <td>Phạm Văn E</td>
                                <td>iPad Pro 11</td>
                                <td>14/05/2025</td>
                                <td><span class="badge bg-danger">Từ chối</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- /.table-responsive -->
            </div>
            <!-- /.card-body -->
            <div class="card-footer clearfix">
                <a href="<?= \yii\helpers\Url::to(['/warranty/create']) ?>" class="btn btn-sm btn-info float-left">Tạo phiếu bảo hành</a>
                <a href="<?= \yii\helpers\Url::to(['/warranty']) ?>" class="btn btn-sm btn-secondary float-right">Xem tất cả phiếu bảo hành</a>
            </div>
            <!-- /.card-footer -->
        </div>
        <!-- /.card -->
    </div>
    <!-- /.col -->
</div>
<!-- /.row -->

<!-- Chart.js script -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Dữ liệu cho biểu đồ
    var salesChartData = {
        labels: ['1/5', '6/5', '11/5', '16/5', '21/5', '26/5', '31/5'],
        datasets: [
            {
                label: 'Doanh thu',
                backgroundColor: 'rgba(60,141,188,0.9)',
                borderColor: 'rgba(60,141,188,0.8)',
                pointRadius: true,
                pointColor: '#3b8bba',
                pointStrokeColor: 'rgba(60,141,188,1)',
                pointHighlightFill: '#fff',
                pointHighlightStroke: 'rgba(60,141,188,1)',
                data: [5000000, 7500000, 6200000, 8100000, 7800000, 8900000, 9500000]
            },
            {
                label: 'Lợi nhuận',
                backgroundColor: 'rgba(210, 214, 222, 1)',
                borderColor: 'rgba(210, 214, 222, 1)',
                pointRadius: true,
                pointColor: 'rgba(210, 214, 222, 1)',
                pointStrokeColor: '#c1c7d1',
                pointHighlightFill: '#fff',
                pointHighlightStroke: 'rgba(220,220,220,1)',
                data: [3500000, 5200000, 4300000, 5700000, 5400000, 6200000, 6600000]
            }
        ]
    };

    var salesChartOptions = {
        maintainAspectRatio: false,
        responsive: true,
        legend: {
            display: true
        },
        scales: {
            xAxes: [{
                gridLines: {
                    display: false
                }
            }],
            yAxes: [{
                gridLines: {
                    display: false
                },
                ticks: {
                    callback: function(value) {
                        return value / 1000000 + ' triệu';
                    }
                }
            }]
        }
    };

    // Lấy canvas biểu đồ doanh thu
    var salesChartCanvas = document.getElementById('salesChart').getContext('2d');
    var salesChart = new Chart(salesChartCanvas, {
        type: 'line',
        data: salesChartData,
        options: salesChartOptions
    });
});
</script>