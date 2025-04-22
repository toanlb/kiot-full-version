# Zplus Kiot - Hệ Thống Quản Lý Bán Hàng

## 1. Tổng Quan Dự Án

### 1.1. Giới thiệu

Phần mềm Quản lý Kiot là một hệ thống toàn diện được phát triển bằng PHP và framework Yii2, nhằm hỗ trợ việc quản lý cửa hàng bán lẻ, bao gồm quản lý hàng tồn kho, bán hàng, và các hoạt động kinh doanh khác.

### 1.2. Mục tiêu

- Tự động hóa quy trình bán hàng và quản lý kho
- Cung cấp hệ thống POS (Point of Sale) hiệu quả cho nhân viên bán hàng
- Theo dõi hàng tồn kho và cảnh báo khi hàng sắp hết
- Quản lý thông tin khách hàng và lịch sử mua hàng
- Theo dõi bảo hành sản phẩm
- Cung cấp báo cáo chi tiết về doanh số, lợi nhuận

### 1.3. Công nghệ sử dụng

- **Ngôn ngữ lập trình**: PHP 7.4+
- **Framework**: Yii2 Advanced
- **Cơ sở dữ liệu**: MySQL 8.0+
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 4/5
- **Giao diện người dùng**: AdminLTE cho backend, Responsive design
- **Báo cáo**: TCPDF/MPDF
- **Phân quyền**: RBAC (Role-Based Access Control) của Yii2

## 2. Cài Đặt Và Cấu Hình

### 2.1. Yêu cầu hệ thống

- PHP 7.4 hoặc cao hơn
- MySQL 8.0 hoặc cao hơn
- Composer
- Git

### 2.2. Cài đặt

```bash
# Clone repository
git clone https://github.com/your-username/zplus-kiot.git
cd zplus-kiot

# Cài đặt dependencies
composer install

# Khởi tạo ứng dụng
php init --env=Development --overwrite=All

# Cấu hình kết nối database trong common/config/main-local.php

# Tạo database
php yii migrate

# Khởi tạo RBAC
php yii rbac/init

# Tạo admin user (nếu cần)
php yii create-admin/index
```

### 2.3. Kết cấu thư mục

```
zplus-kiot/
├── backend/          # Ứng dụng quản trị
├── common/           # Mã dùng chung
├── console/          # Giao diện dòng lệnh
├── frontend/         # Ứng dụng POS
├── environments/     # Cấu hình môi trường
└── vendor/           # Thư viện của bên thứ ba
```

## 3. Tính Năng

### 3.1. Giao diện nhân viên (Frontend)

#### 3.1.1. Hệ thống POS (Point of Sale)

- **Màn hình bán hàng**:
  - Hiển thị danh sách sản phẩm theo danh mục
  - Tìm kiếm sản phẩm theo mã, tên, mã vạch
  - Thêm sản phẩm vào giỏ hàng
  - Áp dụng giảm giá (theo sản phẩm, tổng đơn hàng)
  - Chọn khách hàng hoặc thêm khách hàng mới
  - Thanh toán nhiều hình thức (tiền mặt, chuyển khoản, thẻ)
  - Hỗ trợ trả góp và ghi nợ

- **Quản lý ca làm việc**:
  - Đăng nhập/đăng xuất ca
  - Khai báo tiền đầu ca
  - Kết ca và kiểm đếm tiền cuối ca
  - Báo cáo doanh số theo ca

- **In đơn hàng**:
  - In hóa đơn bán hàng
  - In phiếu bảo hành
  - Hỗ trợ in nhiều định dạng (A4, A5, hóa đơn nhỏ)
  - Tùy chỉnh mẫu in

#### 3.1.2. Quản lý bảo hành

- Tạo phiếu bảo hành cho từng sản phẩm trong đơn hàng
- Theo dõi thời hạn bảo hành
- Cập nhật trạng thái bảo hành
- Lịch sử bảo hành sản phẩm

#### 3.1.3. Quản lý hóa đơn bán hàng

- Danh sách hóa đơn đã bán
- Tìm kiếm hóa đơn theo nhiều tiêu chí
- Chi tiết hóa đơn
- In lại hóa đơn
- Xử lý đổi/trả hàng

### 3.2. Giao diện quản trị (Backend)

#### 3.2.1. Quản lý sản phẩm

- **Danh mục sản phẩm**:
  - Thêm/sửa/xóa danh mục
  - Hỗ trợ danh mục đa cấp
  - Sắp xếp thứ tự hiển thị

- **Sản phẩm**:
  - Thêm/sửa/xóa sản phẩm
  - Quản lý thuộc tính sản phẩm (màu sắc, kích thước...)
  - Quản lý hình ảnh sản phẩm
  - Quản lý giá bán, giá nhập
  - Mã vạch sản phẩm
  - Cài đặt số lượng tồn kho tối thiểu
  - Quản lý sản phẩm combo/bộ

- **Đơn vị tính**:
  - Thêm/sửa/xóa đơn vị tính
  - Quản lý đơn vị tính cơ bản và quy đổi

#### 3.2.2. Quản lý khách hàng

- Thêm/sửa/xóa thông tin khách hàng
- Phân loại khách hàng
- Lịch sử mua hàng của khách
- Công nợ khách hàng
- Quản lý điểm thưởng/tích lũy

#### 3.2.3. Quản lý đơn hàng

- Danh sách đơn hàng
- Lọc đơn hàng theo trạng thái, thời gian, nhân viên
- Chi tiết đơn hàng
- Xử lý đơn hàng (hủy, hoàn thành, đổi trả)
- Lịch sử thanh toán

#### 3.2.4. Quản lý kho hàng

- **Quản lý danh sách kho**:
  - Thêm/sửa/xóa kho hàng
  - Thiết lập kho mặc định
  - Phân quyền quản lý kho theo người dùng
  - Thiết lập thông tin kho (tên, địa chỉ, người phụ trách)
  - Kích hoạt/vô hiệu hóa kho

- **Tồn kho**:
  - Xem số lượng tồn kho thực tế theo từng kho
  - Lọc tồn kho theo kho, danh mục, sản phẩm
  - Báo cáo hàng sắp hết theo từng kho
  - Báo cáo hàng tồn kho theo thời gian
  - Cảnh báo mức tồn kho tối thiểu theo từng kho
  - Kiểm kê kho riêng biệt cho từng kho

- **Nhập kho**:
  - Tạo phiếu nhập kho cho kho cụ thể
  - Nhập từ nhà cung cấp vào kho được chọn
  - Nhập kho nội bộ
  - In phiếu nhập kho
  - Lịch sử nhập kho theo từng kho

- **Xuất kho**:
  - Tạo phiếu xuất kho từ kho cụ thể
  - Xuất cho đơn hàng từ kho được chọn
  - Xuất hủy, xuất trả nhà cung cấp
  - In phiếu xuất kho
  - Lịch sử xuất kho theo từng kho

- **Chuyển kho**:
  - Tạo phiếu chuyển kho giữa các kho hàng
  - Chọn kho nguồn và kho đích
  - Chọn sản phẩm và số lượng cần chuyển
  - Theo dõi trạng thái chuyển kho (đang chuyển, đã nhận, đã hủy)
  - Xác nhận nhận hàng chuyển kho
  - In phiếu chuyển kho
  - Báo cáo lịch sử chuyển kho

#### 3.2.5. Quản lý nhà cung cấp

- Thêm/sửa/xóa nhà cung cấp
- Lịch sử nhập hàng từ nhà cung cấp
- Công nợ nhà cung cấp
- Đánh giá nhà cung cấp

#### 3.2.6. Quản lý bảo hành

- Danh sách phiếu bảo hành
- Tìm kiếm theo mã bảo hành, sản phẩm, khách hàng
- Cập nhật trạng thái bảo hành
- Báo cáo bảo hành

#### 3.2.7. Quản lý người dùng và phân quyền

- Thêm/sửa/xóa tài khoản nhân viên
- Phân quyền người dùng theo vai trò (RBAC)
- Quản lý vai trò và quyền hạn
- Phân quyền theo chức năng và dữ liệu
- Lịch sử đăng nhập
- Khóa/mở khóa tài khoản

#### 3.2.8. Báo cáo bán hàng

- Báo cáo doanh thu theo ngày/tuần/tháng/năm
- Báo cáo theo nhân viên
- Báo cáo theo sản phẩm
- Báo cáo lợi nhuận
- Báo cáo khách hàng
- Xuất báo cáo (Excel, PDF)

#### 3.2.9. Quản lý tài chính

- **Phiếu thu**:
  - Tạo phiếu thu
  - Phiếu thu từ khách hàng
  - Phiếu thu khác
  - In phiếu thu

- **Phiếu chi**:
  - Tạo phiếu chi
  - Phiếu chi cho nhà cung cấp
  - Phiếu chi khác
  - In phiếu chi

- **Sổ quỹ**:
  - Theo dõi thu chi
  - Báo cáo tồn quỹ
  - Đối chiếu quỹ

## 4. Kiến Trúc Hệ Thống

### 4.1. Kiến trúc tổng thể

Hệ thống được xây dựng theo mô hình MVC (Model-View-Controller) của Yii2 Advanced với ba ứng dụng chính:

- **Backend**: Giao diện quản trị dành cho quản lý cửa hàng
- **Frontend**: Giao diện POS dành cho nhân viên bán hàng
- **API**: RESTful API cho tích hợp với các ứng dụng khác

### 4.2. Cấu trúc cơ sở dữ liệu

Cơ sở dữ liệu được thiết kế với các nhóm bảng chính:

- **Quản lý sản phẩm**: product, product_category, product_attribute, product_image, product_unit...
- **Quản lý kho**: warehouse, stock, stock_movement, stock_in, stock_out, stock_transfer...
- **Bán hàng**: order, order_detail, payment_method, discount, return...
- **Khách hàng & Nhà cung cấp**: customer, customer_group, supplier...
- **Bảo hành**: warranty, warranty_detail, warranty_status...
- **Người dùng & Phân quyền**: user, auth_assignment, auth_item, auth_item_child...
- **Tài chính**: receipt, payment, cash_book...

### 4.3. Giao diện người dùng

#### 4.3.1. Giao diện POS

- Thiết kế giao diện đơn giản, dễ sử dụng
- Tối ưu cho màn hình cảm ứng
- Bố cục:
  - Bên trái: Danh sách sản phẩm, tìm kiếm
  - Bên phải: Giỏ hàng, thanh toán
  - Phía trên: Thông tin ca làm việc, nhân viên
  - Phía dưới: Các chức năng nhanh

#### 4.3.2. Giao diện Backend

- Sử dụng AdminLTE template
- Responsive design
- Navigation menu bên trái
- Header chứa thông tin người dùng, thông báo
- Nội dung chính ở giữa
- Dashboard tổng quan

## 5. Quy Trình Nghiệp Vụ

### 5.1. Quy trình bán hàng

1. Nhân viên đăng nhập và mở ca làm việc
2. Thêm sản phẩm vào giỏ hàng
3. Chọn khách hàng hoặc thêm khách hàng mới
4. Áp dụng giảm giá (nếu có)
5. Chọn phương thức thanh toán
6. Hoàn tất đơn hàng
7. In hóa đơn và phiếu bảo hành
8. Cập nhật tồn kho

### 5.2. Quy trình nhập hàng

1. Tạo phiếu nhập kho
2. Chọn kho hàng đích để nhập
3. Chọn nhà cung cấp
4. Thêm sản phẩm vào phiếu nhập
5. Nhập số lượng, giá nhập
6. Hoàn tất phiếu nhập
7. Cập nhật tồn kho của kho hàng đã chọn
8. Tạo phiếu chi (nếu thanh toán ngay)

### 5.3. Quy trình chuyển kho

1. Tạo phiếu chuyển kho
2. Chọn kho nguồn và kho đích
3. Thêm sản phẩm vào phiếu chuyển kho
4. Nhập số lượng cần chuyển
5. Hoàn tất phiếu chuyển kho và ghi chú vận chuyển
6. Cập nhật trạng thái "Đang chuyển"
7. Kho đích xác nhận nhận hàng
8. Cập nhật trạng thái "Đã nhận"
9. Hệ thống tự động cập nhật tồn kho ở cả hai kho

### 5.4. Quy trình bảo hành

1. Tìm kiếm thông tin bảo hành theo mã đơn hàng/sản phẩm
2. Tạo phiếu bảo hành
3. Cập nhật trạng thái bảo hành
4. Thông báo cho khách hàng
5. Hoàn tất bảo hành

### 5.5. Quy trình kiểm kê kho

1. Tạo phiếu kiểm kê cho kho cụ thể
2. Chọn danh sách sản phẩm cần kiểm kê hoặc kiểm kê toàn bộ kho
3. Nhập số lượng thực tế đếm được
4. Hệ thống tính toán chênh lệch giữa số lượng thực tế và số lượng trong hệ thống
5. Xác nhận điều chỉnh
6. Cập nhật tồn kho
7. Lưu lịch sử kiểm kê kho

## 6. Bảo Mật Và Phân Quyền

### 6.1. Phân quyền người dùng

- Sử dụng RBAC (Role-Based Access Control) của Yii2
- Phân quyền theo vai trò (Admin, Quản lý, Nhân viên bán hàng, Kế toán...)
- Phân quyền theo chức năng (xem, thêm, sửa, xóa)
- Phân quyền theo dữ liệu (kho hàng, chi nhánh)

### 6.2. Bảo mật hệ thống

- Xác thực người dùng an toàn
- Quản lý phiên làm việc
- Nhật ký hoạt động người dùng
- Sao lưu dữ liệu định kỳ

## 7. Tích Hợp Và Mở Rộng

### 7.1. Tích hợp với thiết bị ngoại vi

- Máy in hóa đơn
- Máy quét mã vạch
- Máy đọc thẻ
- Ngăn kéo đựng tiền

### 7.2. API cho ứng dụng di động

- RESTful API cho frontend app
- Xác thực và bảo mật API
- Endpoints cho quản lý đơn hàng, sản phẩm, kho

### 7.3. Mở rộng chức năng

- Mô-đun CRM (quản lý khách hàng nâng cao)
- Mô-đun bán hàng online
- Mô-đun báo cáo nâng cao

## 8. Yêu Cầu Cài Đặt Khi Triển Khai

- Máy chủ web: Nginx hoặc Apache
- PHP 7.4+
- MySQL 8.0+
- SSL (khuyến nghị)
- Dung lượng ổ đĩa: 1GB trở lên (không bao gồm dữ liệu)
- RAM: 2GB trở lên

## 9. Đóng Góp Và Phát Triển

Chúng tôi chào đón sự đóng góp từ cộng đồng. Nếu bạn muốn tham gia phát triển Zplus Kiot, vui lòng:

1. Fork dự án
2. Tạo nhánh tính năng (`git checkout -b feature/amazing-feature`)
3. Commit thay đổi của bạn (`git commit -m 'Add some amazing feature'`)
4. Push lên nhánh (`git push origin feature/amazing-feature`)
5. Mở Pull Request

## 10. Giấy Phép

Dự án được phân phối dưới giấy phép MIT. Xem thêm `LICENSE` để biết thêm chi tiết.

## 11. Liên Hệ

- Email: support@zpluskiot.com
- Website: [https://zpluskiot.com](https://zpluskiot.com)
- Địa chỉ: 123 Đường ABC, Quận XYZ, Thành phố HCM

---

© 2025 Zplus Kiot. All rights reserved.

## Tiến Độ Dự Án

### 1. Frontend (POS - Bán Hàng)

#### 1.1 Quản lý ca làm việc (✅ Hoàn thành)
- Đăng nhập/đăng xuất ca
- Khai báo tiền đầu ca
- Kết ca và kiểm đếm
- Báo cáo doanh số theo ca

#### 1.2 Bán hàng POS (✅ Hoàn thành)
- Giao diện bán hàng trực quan
- Tìm kiếm sản phẩm nhanh
- Quản lý giỏ hàng
- Áp dụng giảm giá
- Thanh toán đa hình thức
- In hóa đơn

#### 1.3 Bảo hành (✅ Hoàn thành)
- In phiếu bảo hành
- Quản lý thời hạn
- Cập nhật trạng thái

### 2. Backend (Quản Trị)

#### 2.1 Quản lý sản phẩm (✅ Hoàn thành)
- CRUD sản phẩm
- Quản lý danh mục
- Quản lý thuộc tính
- Quản lý đơn vị tính
- Quản lý combo/bộ
- Upload hình ảnh

#### 2.2 Quản lý kho (✅ Hoàn thành)
- Quản lý nhiều kho
- Nhập/xuất kho
- Chuyển kho
- Kiểm kê
- Báo cáo tồn kho
- Cảnh báo hết hàng

#### 2.3 Quản lý khách hàng (✅ Hoàn thành)
- CRUD khách hàng
- Phân nhóm khách hàng
- Tích điểm thành viên
- Lịch sử mua hàng
- Công nợ khách hàng

#### 2.4 Quản lý nhà cung cấp (✅ Hoàn thành)
- CRUD nhà cung cấp
- Quản lý nợ cần trả
- Lịch sử nhập hàng
- Đánh giá nhà cung cấp

#### 2.5 Báo cáo (✅ Hoàn thành)
- Báo cáo doanh số
- Báo cáo lợi nhuận
- Báo cáo tồn kho
- Báo cáo công nợ
- Xuất báo cáo (PDF, Excel)

#### 2.6 Phân quyền & Bảo mật (✅ Hoàn thành)
- Quản lý người dùng
- Phân quyền RBAC
- Lịch sử đăng nhập
- Nhật ký hoạt động

### 3. Tính Năng Cần Phát Triển (⚠️ Đang phát triển)

#### 3.1 Tích hợp thiết bị (🔄 Đang triển khai)
- Kết nối máy in
- Đọc mã vạch
- Máy quét thẻ
- Ngăn kéo tiền

#### 3.2 API & Ứng dụng di động (⏳ Chưa triển khai)
- REST API
- Ứng dụng Android/iOS
- Đồng bộ dữ liệu

#### 3.3 CRM Nâng cao (⏳ Chưa triển khai)
- Chăm sóc khách hàng
- Marketing tự động
- Khảo sát khách hàng
- Phân tích hành vi

#### 3.4 Bán hàng online (⏳ Chưa triển khai)
- Website bán hàng
- Quản lý đơn online
- Tích hợp vận chuyển
- Thanh toán trực tuyến

### 4. Đánh Giá Chung

#### 4.1 Điểm mạnh
- Hệ thống POS hoạt động ổn định
- Quản lý kho đa chi nhánh hiệu quả
- Báo cáo chi tiết, trực quan
- Phân quyền linh hoạt

#### 4.2 Cần cải thiện
- Tối ưu hiệu suất hệ thống
- Cải thiện UX/UI
- Tăng cường bảo mật
- Hoàn thiện tài liệu

## Kế Hoạch Phát Triển

### Q3/2024
- Hoàn thiện tích hợp thiết bị
- Phát triển REST API cơ bản
- Tối ưu hiệu suất hệ thống

### Q4/2024
- Phát triển ứng dụng di động
- Triển khai CRM nâng cao
- Cải thiện UX/UI

### Q1/2025
- Phát triển tính năng bán hàng online
- Tích hợp các cổng thanh toán
- Hoàn thiện tài liệu kỹ thuật