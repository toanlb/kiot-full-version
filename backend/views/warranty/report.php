<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $totalWarranties int */
/* @var $activeWarranties int */
/* @var $expiringWarranties int */
/* @var $statusStats array */
/* @var $productStats array */

$this->title = 'Warranty Reports';
$this->params['breadcrumbs'][] = ['label' => 'Warranties', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Prepare data for status chart
$statusLabels = [];
$statusData = [];
$statusColors = [];
foreach ($statusStats as $stat) {
    $statusLabels[] = $stat['name'];
    $statusData[] = (int) $stat['count'];
    $statusColors[] = $stat['color'];
}

// Prepare data for product chart
$productLabels = [];
$productData = [];
foreach ($productStats as $stat) {
    $productLabels[] = $stat['name'];
    $productData[] = (int) $stat['count'];
}

// Convert arrays to JSON for JavaScript
$statusLabelsJson = json_encode($statusLabels);
$statusDataJson = json_encode($statusData);
$statusColorsJson = json_encode($statusColors);
$productLabelsJson = json_encode($productLabels);
$productDataJson = json_encode($productData);

// Get monthly warranty creation stats for the past 12 months
$monthlyStats = Yii::$app->db->createCommand("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(id) as count
    FROM 
        warranty
    WHERE 
        created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY 
        DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY 
        month ASC
")->queryAll();

$monthLabels = [];
$monthData = [];

// Fill in the past 12 months, even if there's no data
for ($i = 11; $i >= 0; $i--) {
    $monthKey = date('Y-m', strtotime("-$i months"));
    $monthLabels[] = date('M Y', strtotime("-$i months"));
    $monthData[] = 0; // Default to 0
}

// Fill in actual data
foreach ($monthlyStats as $stat) {
    $monthIndex = array_search(date('M Y', strtotime($stat['month'])), $monthLabels);
    if ($monthIndex !== false) {
        $monthData[$monthIndex] = (int) $stat['count'];
    }
}

$monthLabelsJson = json_encode($monthLabels);
$monthDataJson = json_encode($monthData);
?>

<div class="warranty-report">
    <!-- Date Range Filter -->
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-filter"></i> Report Filters
            </h3>
        </div>
        <div class="card-body">
            <form id="report-filter-form" action="<?= Url::to(['report']) ?>" method="get">
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label>Date Range</label>
                            <div class="input-group">
                                <input type="text" name="date_range" id="report-date-range" class="form-control" value="<?= Yii::$app->request->get('date_range') ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status_id" class="form-control select2">
                                <option value="">All Statuses</option>
                                <?php 
                                $statuses = \common\models\WarrantyStatus::find()->all();
                                foreach ($statuses as $status): 
                                ?>
                                    <option value="<?= $status->id ?>" <?= Yii::$app->request->get('status_id') == $status->id ? 'selected' : '' ?>>
                                        <?= Html::encode($status->name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-group mb-0 w-100">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Apply Filters
                            </button>
                            <a href="<?= Url::to(['report']) ?>" class="btn btn-outline-secondary ml-2">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Summary Statistics -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3><?= $totalWarranties ?></h3>
                    <p>Total Warranties</p>
                </div>
                <div class="icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <a href="<?= Url::to(['index']) ?>" class="small-box-footer">
                    View all <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3><?= $activeWarranties ?></h3>
                    <p>Active Warranties</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <a href="<?= Url::to(['index', 'WarrantySearch[active]' => 1]) ?>" class="small-box-footer">
                    View active <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3><?= $expiringWarranties ?></h3>
                    <p>Expiring in 30 Days</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <a href="#expiring-warranties" class="small-box-footer">
                    View expiring <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3><?= $totalWarranties - $activeWarranties ?></h3>
                    <p>Inactive Warranties</p>
                </div>
                <div class="icon">
                    <i class="fas fa-times-circle"></i>
                </div>
                <a href="<?= Url::to(['index', 'WarrantySearch[active]' => 0]) ?>" class="small-box-footer">
                    View inactive <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Monthly Trend Chart -->
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-line mr-1"></i>
                Warranty Registrations - Monthly Trend
            </h3>
        </div>
        <div class="card-body">
            <canvas id="monthlyTrendChart" style="min-height: 300px;"></canvas>
        </div>
    </div>
    
    <!-- Status and Product Charts -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie mr-1"></i>
                        Warranties by Status
                    </h3>
                </div>
                <div class="card-body">
                    <?php if (empty($statusStats)): ?>
                        <div class="alert alert-info">No data available</div>
                    <?php else: ?>
                        <canvas id="statusChart" style="min-height: 250px;"></canvas>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar mr-1"></i>
                        Top Products Under Warranty
                    </h3>
                </div>
                <div class="card-body">
                    <?php if (empty($productStats)): ?>
                        <div class="alert alert-info">No data available</div>
                    <?php else: ?>
                        <canvas id="productChart" style="min-height: 250px;"></canvas>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Status Details Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-table mr-1"></i>
                        Warranty Status Details
                    </h3>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Count</th>
                                <th>Percentage</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($statusStats as $stat): ?>
                                <tr>
                                    <td>
                                        <span class="badge" style="background-color: <?= $stat['color'] ?>">
                                            <?= Html::encode($stat['name']) ?>
                                        </span>
                                    </td>
                                    <td><?= $stat['count'] ?></td>
                                    <td>
                                        <?= round(($stat['count'] / $totalWarranties) * 100, 2) ?>%
                                        <div class="progress">
                                            <div class="progress-bar" style="width: <?= round(($stat['count'] / $totalWarranties) * 100) ?>%; background-color: <?= $stat['color'] ?>"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <?= Html::a(
                                            'View',
                                            ['index', 'WarrantySearch[status_id]' => array_search($stat['name'], $statuses ?? [])],
                                            ['class' => 'btn btn-sm btn-primary']
                                        ) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Expiring Warranties Section -->
    <div class="row">
        <div class="col-md-12">
            <div class="card" id="expiring-warranties">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Warranties Expiring Soon
                    </h3>
                </div>
                <div class="card-body table-responsive">
                    <?php 
                    $expiringWarrantyList = \common\models\Warranty::find()
                        ->where(['active' => true])
                        ->andWhere(['between', 'end_date', date('Y-m-d'), date('Y-m-d', strtotime('+30 days'))])
                        ->with(['product', 'customer', 'status'])
                        ->orderBy(['end_date' => SORT_ASC])
                        ->limit(10)
                        ->all();
                    ?>
                    
                    <?php if (empty($expiringWarrantyList)): ?>
                        <div class="alert alert-info">No warranties expiring soon</div>
                    <?php else: ?>
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Product</th>
                                    <th>Customer</th>
                                    <th>End Date</th>
                                    <th>Days Left</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($expiringWarrantyList as $warranty): 
                                    $today = new \DateTime();
                                    $endDate = new \DateTime($warranty->end_date);
                                    $interval = $today->diff($endDate);
                                    $daysLeft = $interval->format('%r%a');
                                ?>
                                    <tr>
                                        <td><?= Html::encode($warranty->code) ?></td>
                                        <td><?= Html::encode($warranty->product->name ?? 'N/A') ?></td>
                                        <td><?= Html::encode($warranty->customer->name ?? 'N/A') ?></td>
                                        <td><?= Yii::$app->formatter->asDate($warranty->end_date) ?></td>
                                        <td>
                                            <span class="badge badge-<?= $daysLeft <= 7 ? 'danger' : 'warning' ?>">
                                                <?= $daysLeft ?> days
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge" style="background-color: <?= $warranty->status->color ?? '#777' ?>">
                                                <?= Html::encode($warranty->status->name ?? 'Unknown') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= Html::a(
                                                '<i class="fas fa-eye"></i>',
                                                ['view', 'id' => $warranty->id],
                                                ['class' => 'btn btn-sm btn-info', 'title' => 'View']
                                            ) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <?php if (count($expiringWarrantyList) < $expiringWarranties): ?>
                            <div class="text-center mt-3">
                                <?= Html::a(
                                    'View All Expiring Warranties',
                                    ['index', 'WarrantySearch[active]' => 1, 'WarrantySearch[date_range]' => date('d/m/Y') . ' - ' . date('d/m/Y', strtotime('+30 days'))],
                                    ['class' => 'btn btn-warning']
                                ) ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Service History Analytics -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-tools mr-1"></i>
                        Warranty Service Statistics
                    </h3>
                </div>
                <div class="card-body">
                    <?php
                    // Get service statistics
                    $serviceStats = Yii::$app->db->createCommand("
                        SELECT 
                            COUNT(*) as total_services,
                            SUM(CASE WHEN is_charged = 1 THEN 1 ELSE 0 END) as charged_services,
                            SUM(CASE WHEN is_charged = 0 THEN 1 ELSE 0 END) as free_services,
                            SUM(replacement_cost) as total_replacement_cost,
                            SUM(service_cost) as total_service_cost,
                            SUM(total_cost) as total_cost,
                            COUNT(DISTINCT warranty_id) as unique_warranties
                        FROM 
                            warranty_detail
                    ")->queryOne();
                    
                    // Get top 5 most common issues (based on description)
                    $commonIssues = Yii::$app->db->createCommand("
                        SELECT 
                            SUBSTRING_INDEX(description, ' ', 3) as issue_summary,
                            COUNT(*) as count
                        FROM 
                            warranty_detail
                        WHERE 
                            description IS NOT NULL AND description != ''
                        GROUP BY 
                            issue_summary
                        ORDER BY 
                            count DESC
                        LIMIT 5
                    ")->queryAll();
                    ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Service Overview</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Total Service Records</th>
                                    <td><?= $serviceStats['total_services'] ?? 0 ?></td>
                                </tr>
                                <tr>
                                    <th>Unique Warranties Serviced</th>
                                    <td><?= $serviceStats['unique_warranties'] ?? 0 ?></td>
                                </tr>
                                <tr>
                                    <th>Free Services (Under Warranty)</th>
                                    <td><?= $serviceStats['free_services'] ?? 0 ?></td>
                                </tr>
                                <tr>
                                    <th>Charged Services</th>
                                    <td><?= $serviceStats['charged_services'] ?? 0 ?></td>
                                </tr>
                                <tr>
                                    <th>Total Replacement Cost</th>
                                    <td><?= Yii::$app->formatter->asCurrency($serviceStats['total_replacement_cost'] ?? 0) ?></td>
                                </tr>
                                <tr>
                                    <th>Total Service Cost</th>
                                    <td><?= Yii::$app->formatter->asCurrency($serviceStats['total_service_cost'] ?? 0) ?></td>
                                </tr>
                                <tr>
                                    <th>Total Revenue</th>
                                    <td><?= Yii::$app->formatter->asCurrency($serviceStats['total_cost'] ?? 0) ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Common Issues</h5>
                            <?php if (empty($commonIssues)): ?>
                                <div class="alert alert-info">No service data available</div>
                            <?php else: ?>
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Issue</th>
                                            <th>Count</th>
                                            <th>Percentage</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($commonIssues as $issue): ?>
                                            <tr>
                                                <td><?= Html::encode($issue['issue_summary']) ?></td>
                                                <td><?= $issue['count'] ?></td>
                                                <td>
                                                    <?= round(($issue['count'] / $serviceStats['total_services']) * 100, 2) ?>%
                                                    <div class="progress">
                                                        <div class="progress-bar bg-info" style="width: <?= round(($issue['count'] / $serviceStats['total_services']) * 100) ?>%"></div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Print Section -->
    <div class="row">
        <div class="col-md-12 mt-3 mb-4 text-center">
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Print Report
            </button>
            <a href="<?= Url::to(['index']) ?>" class="btn btn-secondary ml-2">
                <i class="fas fa-list"></i> Back to Warranty List
            </a>
        </div>
    </div>
</div>

<?php
$js = <<<JS
    // Initialize select2 and daterangepicker
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
        
        $('#report-date-range').daterangepicker({
            locale: {
                format: 'DD/MM/YYYY',
                separator: ' - '
            },
            autoUpdateInput: false
        });
        
        $('#report-date-range').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
        });
        
        $('#report-date-range').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });
        
        // Initialize Charts
        
        // Status Chart
        var statusCtx = document.getElementById('statusChart');
        if (statusCtx) {
            var statusChart = new Chart(statusCtx, {
                type: 'pie',
                data: {
                    labels: $statusLabelsJson,
                    datasets: [{
                        data: $statusDataJson,
                        backgroundColor: $statusColorsJson,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20
                        }
                    },
                    tooltips: {
                        enabled: true
                    }
                }
            });
        }
        
        // Product Chart
        var productCtx = document.getElementById('productChart');
        if (productCtx) {
            var productChart = new Chart(productCtx, {
                type: 'horizontalBar',
                data: {
                    labels: $productLabelsJson,
                    datasets: [{
                        label: 'Number of Warranties',
                        data: $productDataJson,
                        backgroundColor: 'rgba(60, 141, 188, 0.8)',
                        borderColor: 'rgba(60, 141, 188, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        xAxes: [{
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    },
                    legend: {
                        display: false
                    },
                    tooltips: {
                        enabled: true
                    }
                }
            });
        }
        
        // Monthly Trend Chart
        var monthlyTrendCtx = document.getElementById('monthlyTrendChart');
        if (monthlyTrendCtx) {
            var monthlyTrendChart = new Chart(monthlyTrendCtx, {
                type: 'line',
                data: {
                    labels: $monthLabelsJson,
                    datasets: [{
                        label: 'New Warranties',
                        data: $monthDataJson,
                        backgroundColor: 'rgba(60, 141, 188, 0.2)',
                        borderColor: 'rgba(60, 141, 188, 1)',
                        borderWidth: 2,
                        pointRadius: 4,
                        pointBackgroundColor: 'rgba(60, 141, 188, 1)',
                        pointBorderColor: '#fff',
                        pointHoverRadius: 6,
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                precision: 0 // Only show whole numbers
                            }
                        }]
                    },
                    tooltips: {
                        enabled: true
                    }
                }
            });
        }
    });
    
    // Print styles
    @media print {
        .no-print, .main-header, .main-sidebar, .main-footer, 
        .card-tools, .btn, .select2, .daterangepicker, .page-item {
            display: none !important;
        }
        
        .content-wrapper {
            margin-left: 0 !important;
            padding: 0 !important;
        }
        
        .card {
            break-inside: avoid;
            margin-bottom: 20px;
        }
        
        body {
            background-color: white !important;
        }
        
        .card-header {
            background-color: #f4f6f9 !important;
            color: #000 !important;
        }
    }
JS;
$this->registerJs($js);
?>