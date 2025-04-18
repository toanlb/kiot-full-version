/**
 * Dashboard.js
 * Script cho trang dashboard
 */

$(function () {
    'use strict'

    // Bootstrap tooltips
    $('[data-toggle="tooltip"]').tooltip()

    // Initialize Card Widget
    $('.card-collapse').on('click', function(e) {
        e.preventDefault()
        $(this).closest('.card').find('.card-body, .card-footer').slideToggle()
        $(this).find('i').toggleClass('fa-minus fa-plus')
    })

    // Toggle sidebar
    $('[data-widget="pushmenu"]').on('click', function () {
        $('body').toggleClass('sidebar-collapse')
    })

    // Đếm ngược cho phiếu bảo hành
    function countdownWarranty() {
        $('.warranty-countdown').each(function() {
            var endDate = new Date($(this).data('end-date'))
            var now = new Date()
            var diff = Math.floor((endDate - now) / (1000 * 60 * 60 * 24))
            
            if (diff <= 0) {
                $(this).html('<span class="badge bg-danger">Hết hạn</span>')
            } else if (diff <= 30) {
                $(this).html('<span class="badge bg-warning">' + diff + ' ngày</span>')
            } else {
                $(this).html('<span class="badge bg-success">' + diff + ' ngày</span>')
            }
        })
    }
    
    // Cập nhật số liệu realtime
    function updateDashboardStats() {
        // Đây là nơi để gọi Ajax lấy dữ liệu dashboard thời gian thực
        // Ví dụ:
        /*
        $.ajax({
            url: 'path/to/dashboard-stats',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                $('#today-orders').text(data.todayOrders);
                $('#today-revenue').text(formatCurrency(data.todayRevenue));
                $('#new-customers').text(data.newCustomers);
                $('#low-stock').text(data.lowStock);
            },
            error: function(xhr, status, error) {
                console.error("Couldn't load dashboard stats:", error);
            }
        });
        */
    }
    
    // Format currency
    function formatCurrency(value) {
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
    }

    // Khởi động các function
    countdownWarranty();
    updateDashboardStats();
    
    // Cập nhật dữ liệu theo khoảng thời gian
    setInterval(countdownWarranty, 60000); // 1 phút cập nhật một lần
    setInterval(updateDashboardStats, 300000); // 5 phút cập nhật một lần

    // Bắt sự kiện khi thay đổi bộ lọc dashboard
    $('#dashboard-filters').on('change', function() {
        // Thực hiện lọc dữ liệu
        var filter = $(this).val();
        // Gọi AJAX để lấy dữ liệu mới theo bộ lọc
    });

    // Bắt sự kiện khi người dùng click vào thông báo
    $('.navbar-nav .dropdown-menu a').on('click', function(e) {
        // Xử lý sự kiện khi click vào thông báo
        var notificationId = $(this).data('notification-id');
        if (notificationId) {
            // Đánh dấu thông báo là đã đọc
            markNotificationAsRead(notificationId);
        }
    });

    function markNotificationAsRead(id) {
        // Gọi API để đánh dấu là đã đọc
        /*
        $.ajax({
            url: 'path/to/mark-notification',
            type: 'POST',
            data: { id: id },
            success: function(response) {
                // Thực hiện các thao tác UI sau khi đánh dấu thành công
            }
        });
        */
    }

    // Hiển thị thời gian thực
    function updateClock() {
        var now = new Date();
        var hours = now.getHours();
        var minutes = now.getMinutes();
        
        // Thêm số 0 ở đầu nếu cần thiết
        hours = hours < 10 ? '0' + hours : hours;
        minutes = minutes < 10 ? '0' + minutes : minutes;
        
        // Hiển thị giờ
        $('#live-clock').text(hours + ':' + minutes);
    }
    
    // Cập nhật đồng hồ mỗi giây
    setInterval(updateClock, 1000);
    updateClock(); // Khởi tạo
});