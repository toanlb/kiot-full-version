$(function () {
    'use strict'
    
    // Làm mới dữ liệu dashboard định kỳ
    var refreshInterval = 300000; // 5 phút
    
    function refreshDashboardData() {
        $.ajax({
            url: BASE_URL + '/site/dashboard-data',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                // Cập nhật các thẻ dữ liệu
                $('#today-orders-count').text(formatNumber(data.todayOrders));
                $('#today-revenue').text(formatCurrency(data.todayRevenue));
                $('#new-customers-count').text(formatNumber(data.newCustomers));
                $('#low-stock-count').text(formatNumber(data.lowStockProducts));
                
                // Hiển thị thời gian cập nhật
                var serverTime = new Date(data.serverTime);
                $('#last-update-time').text(formatDateTime(serverTime));
            },
            error: function() {
                console.log('Không thể cập nhật dữ liệu dashboard');
            }
        });
    }
    
    // Khởi tạo hàm làm mới tự động
    setInterval(refreshDashboardData, refreshInterval);
    
    // Khởi tạo các datepicker
    $('.datepicker').datepicker({
        format: 'dd/mm/yyyy',
        autoclose: true,
        todayHighlight: true,
        language: 'vi'
    });
    
    // Khởi tạo Select2 cho các dropdown
    $('.select2').select2({
        theme: 'bootstrap4'
    });
    
    // Định dạng số
    function formatNumber(number) {
        return new Intl.NumberFormat('vi-VN').format(number);
    }
    
    // Định dạng tiền tệ
    function formatCurrency(number) {
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(number);
    }
    
    // Định dạng ngày giờ
    function formatDateTime(date) {
        return new Intl.DateTimeFormat('vi-VN', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        }).format(date);
    }
    
    // Xử lý nút làm mới dữ liệu
    $('#refresh-dashboard').on('click', function() {
        $(this).find('i').addClass('fa-spin');
        refreshDashboardData();
        setTimeout(function() {
            $('#refresh-dashboard').find('i').removeClass('fa-spin');
        }, 1000);
    });
    
    // Xử lý chuyển đổi biểu đồ
    $('.chart-type-switch').on('click', function(e) {
        e.preventDefault();
        var target = $(this).data('target');
        var type = $(this).data('type');
        
        switch(target) {
            case 'revenue':
                // Chuyển đổi kiểu biểu đồ doanh thu
                if (revenueChart.config.type !== type) {
                    revenueChart.config.type = type;
                    revenueChart.update();
                }
                break;
                
            case 'warehouse':
                // Chuyển đổi kiểu biểu đồ kho hàng
                if (warehouseChart.config.type !== type) {
                    warehouseChart.config.type = type;
                    warehouseChart.update();
                }
                break;
        }
        
        // Cập nhật trạng thái active cho nút
        $(this).siblings().removeClass('active');
        $(this).addClass('active');
    });
    
    // Xử lý thay đổi thời gian cho biểu đồ
    $('.time-range-selector').on('click', function(e) {
        e.preventDefault();
        var range = $(this).data('range');
        var chart = $(this).data('chart');
        
        // Cập nhật UI
        $(this).siblings().removeClass('active');
        $(this).addClass('active');
        
        // Thực hiện AJAX để lấy dữ liệu mới theo khoảng thời gian
        $.ajax({
            url: BASE_URL + '/site/chart-data',
            type: 'GET',
            data: {
                chart: chart,
                range: range
            },
            dataType: 'json',
            success: function(data) {
                // Cập nhật dữ liệu biểu đồ tương ứng
                switch(chart) {
                    case 'revenue':
                        revenueChart.data.labels = data.labels;
                        revenueChart.data.datasets[0].data = data.values;
                        revenueChart.update();
                        break;
                    case 'products':
                        // Cập nhật biểu đồ sản phẩm
                        break;
                }
            }
        });
    });
    
    // Xử lý hiển thị trợ giúp
    $('.help-tooltip').tooltip();
    
    // Tối ưu giao diện cho màn hình nhỏ
    function optimizeForMobile() {
        if (window.innerWidth < 768) {
            $('.card').addClass('collapsed-card');
        } else {
            $('.card').removeClass('collapsed-card');
        }
    }
    
    // Gọi khi tải trang và thay đổi kích thước
    optimizeForMobile();
    $(window).resize(optimizeForMobile);
    
    // Hiện modal nếu cần thông báo
    if (SHOW_NOTIFICATION) {
        $('#notificationModal').modal('show');
    }
});