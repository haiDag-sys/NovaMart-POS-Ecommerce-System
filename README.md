# NovaMart

Website bán hàng và quản lý cửa hàng dành cho mô hình cửa hàng bán lẻ/siêu thị mini. Dự án hỗ trợ đồng thời **khách hàng mua sắm trực tuyến**, **nhân viên bán hàng tại quầy (POS)** và **quản trị viên vận hành cửa hàng**.

## Mục lục

- [Giới thiệu](#giới-thiệu)
- [Tính năng nổi bật](#tính-năng-nổi-bật)
- [Kiến trúc và công nghệ](#kiến-trúc-và-công-nghệ)
- [Cấu trúc thư mục](#cấu-trúc-thư-mục)
- [Yêu cầu hệ thống](#yêu-cầu-hệ-thống)
- [Hướng dẫn cài đặt](#hướng-dẫn-cài-đặt)
- [Tài khoản mặc định](#tài-khoản-mặc-định)
- [Cách sử dụng nhanh](#cách-sử-dụng-nhanh)
- [Luồng nghiệp vụ chính](#luồng-nghiệp-vụ-chính)
- [Một số lưu ý khi phát triển](#một-số-lưu-ý-khi-phát-triển)
- [Hạn chế hiện tại](#hạn-chế-hiện-tại)
- [Hướng phát triển](#hướng-phát-triển)
- [Tác giả](#tác-giả)

## Giới thiệu

NovaMart là hệ thống web phục vụ hai mục tiêu chính:

1. **Bán hàng trực tuyến cho khách hàng**: xem sản phẩm, tìm kiếm, thêm vào giỏ hàng, đặt hàng và theo dõi đơn mua.
2. **Quản lý cửa hàng**: quản lý sản phẩm, loại sản phẩm, nhà cung cấp, nhập kho, đơn hàng và bán hàng tại quầy thông qua giao diện POS.

Dự án phù hợp cho mục đích:

- niên luận / đồ án môn học;
- mô hình demo quản lý cửa hàng bán lẻ;
- nền tảng để mở rộng thành hệ thống thương mại điện tử quy mô nhỏ.

## Tính năng nổi bật

### 1) Khách hàng

- Đăng ký, đăng nhập, đăng xuất.
- Xem danh sách sản phẩm tại trang chủ.
- Tìm kiếm và lọc sản phẩm theo danh mục.
- Xem chi tiết sản phẩm.
- Thêm sản phẩm vào giỏ hàng.
- Đặt hàng trực tuyến.
- Theo dõi đơn mua và xem chi tiết đơn hàng.
- Cập nhật thông tin cá nhân.
- Đánh giá sản phẩm.
- Nhận thông báo khi trạng thái đơn hàng thay đổi.

### 2) Nhân viên

- Đăng nhập khu vực nhân viên.
- Bán hàng tại quầy bằng giao diện POS.
- Tìm kiếm sản phẩm nhanh.
- Tạo hóa đơn tạm và xác nhận thanh toán.
- Cập nhật tồn kho sau bán hàng.

### 3) Quản trị viên

- Đăng nhập khu vực quản trị.
- Quản lý sản phẩm.
- Quản lý loại sản phẩm.
- Quản lý nhà cung cấp.
- Lập phiếu nhập kho.
- Theo dõi và xử lý đơn hàng trực tuyến.
- Xem tổng quan và thống kê doanh thu.
- Theo dõi đơn hàng mới theo thời gian gần thực thông qua cơ chế tự làm mới dữ liệu trên trang quản trị.

## Kiến trúc và công nghệ

### Công nghệ sử dụng

- **PHP**
- **MySQL / MariaDB**
- **Bootstrap**
- **JavaScript**
- **XAMPP**

### Kiến trúc triển khai

Dự án đang được tổ chức theo hướng **MVC kết hợp cấu trúc PHP truyền thống**:

- Một phần logic được tách theo `app/Controllers`, `app/Models`, `app/Views`.
- Một phần entry point vẫn chạy trực tiếp qua các file PHP ở thư mục gốc, `admin/` và `staff/`.

Cách tổ chức này phù hợp với đồ án học thuật vì:

- dễ chạy trên môi trường local;
- thuận tiện cho việc trình bày nghiệp vụ;
- dễ mở rộng dần lên kiến trúc rõ ràng hơn trong tương lai.

## Cấu trúc thư mục

nienluan/
├── admin/ # Giao diện và chức năng quản trị
├── staff/ # Giao diện và chức năng POS cho nhân viên
├── app/
│ ├── Config/ # Cấu hình hệ thống
│ ├── Controllers/ # Controller xử lý nghiệp vụ
│ ├── Models/ # Model thao tác dữ liệu
│ ├── Support/ # Hàm hỗ trợ
│ └── Views/ # View giao diện
├── assets/
│ ├── css/ # CSS
│ ├── js/ # JavaScript
│ ├── img/ # Hình ảnh tĩnh
│ └── uploads/ # Ảnh upload từ hệ thống
├── includes/ # Thành phần dùng chung (db, header, footer...)
├── UML/ # File UML / ERD phục vụ báo cáo
├── index.php # Trang chủ khách hàng
├── cart.php # Giỏ hàng
├── detail.php # Chi tiết sản phẩm
├── profile.php # Hồ sơ khách hàng
├── register.php # Đăng ký tài khoản
├── login_member.php # Đăng nhập khách hàng
└── README.md

## Yêu cầu hệ thống

- **PHP 8.x** hoặc tương thích với phiên bản trong XAMPP đang dùng.
- **Apache**.
- **MySQL / MariaDB**.
- Trình duyệt hiện đại: Chrome, Edge, Firefox.
- Khuyến nghị môi trường local: **XAMPP**.

## Hướng dẫn cài đặt

### 1. Tải mã nguồn

Chép thư mục dự án vào:

C:\xampp\htdocs\nienluan

### 2. Khởi động dịch vụ

Mở **XAMPP Control Panel** và bật:

- `Apache`
- `MySQL`

### 3. Tạo cơ sở dữ liệu

Mở trình duyệt và truy cập:

http://localhost/phpmyadmin

Tạo database mới:

taphoa_db

Khuyến nghị collation:

utf8mb4_unicode_ci

### 4. Import file SQL

- Chọn database vừa tạo.
- Vào tab **Import**.
- Chọn file `.sql` của dự án.
- Bấm **Import**.

### 5. Cấu hình kết nối cơ sở dữ liệu

Kiểm tra lại thông tin kết nối trong các file cấu hình/kết nối của dự án, ví dụ các file trong:

- `includes/`
- `app/Config/`

Thông tin thường dùng trên XAMPP local:

DB_HOST=localhost
DB_NAME=taphoa_db
DB_USER=root
DB_PASS=

### 6. Chạy hệ thống

Truy cập các đường dẫn sau:

- **Trang khách hàng**: `http://localhost/nienluan/`
- **Trang quản trị**: `http://localhost/nienluan/admin/login.php`
- **Trang nhân viên**: `http://localhost/nienluan/staff/login.php`

## Tài khoản mặc định

| Vai trò       | Tài khoản                      | Mật khẩu             |
| ------------- | ------------------------------ | -------------------- |
| Quản trị viên | `admin`                        | `123`                |
| Nhân viên     | `nhanvien`                     | `123`                |
| Khách hàng    | Đăng ký trực tiếp trên website | Do người dùng tự tạo |

## Cách sử dụng nhanh

### Đối với khách hàng

1. Truy cập trang chủ.
2. Tìm kiếm hoặc chọn sản phẩm.
3. Thêm sản phẩm vào giỏ hàng.
4. Vào giỏ hàng và xác nhận đặt hàng.
5. Theo dõi trạng thái đơn trong mục đơn mua.

### Đối với nhân viên

1. Đăng nhập trang nhân viên.
2. Mở giao diện POS.
3. Tìm kiếm sản phẩm.
4. Chọn sản phẩm, nhập số lượng.
5. Xác nhận thanh toán để tạo hóa đơn tại quầy.

### Đối với quản trị viên

1. Đăng nhập trang quản trị.
2. Quản lý sản phẩm, loại sản phẩm, nhà cung cấp.
3. Lập phiếu nhập kho.
4. Theo dõi và xử lý đơn hàng mới.
5. Xem dashboard và thống kê doanh thu.

## Luồng nghiệp vụ chính

### 1. Đặt hàng trực tuyến

- Khách hàng chọn sản phẩm.
- Hệ thống lưu giỏ hàng.
- Khách hàng xác nhận đặt hàng.
- Hệ thống tạo hóa đơn và chi tiết hóa đơn.
- Quản trị viên theo dõi và cập nhật trạng thái đơn hàng.

### 2. Bán hàng tại quầy

- Nhân viên đăng nhập POS.
- Chọn sản phẩm và số lượng.
- Hệ thống tạo hóa đơn bán tại quầy.
- Tồn kho được cập nhật sau khi thanh toán.

### 3. Nhập kho

- Quản trị viên chọn nhà cung cấp.
- Lập phiếu nhập và chi tiết phiếu nhập.
- Hệ thống cập nhật tồn kho sản phẩm.

## Một số lưu ý khi phát triển

- Nếu sửa giao diện nhưng trình duyệt chưa cập nhật, hãy dùng `Ctrl + F5` để xóa cache.
- Nên lưu ảnh upload trong `assets/uploads/` và chỉ lưu đường dẫn ảnh trong cơ sở dữ liệu.
- Với môi trường demo/báo cáo, nên dùng dữ liệu mẫu ngắn gọn, dễ quan sát.
- Nếu thay đổi cấu trúc database, cần cập nhật lại các model/controller và file SQL đi kèm.

## Hạn chế hiện tại

- Dự án chủ yếu hướng đến môi trường local/XAMPP.
- Chưa triển khai thanh toán trực tuyến.
- Chưa tối ưu cho tải lớn hoặc nhiều người dùng đồng thời.
- Kiến trúc hiện tại chưa tách hoàn toàn thành MVC thuần.
- Chưa tích hợp hệ thống test tự động.

## Hướng phát triển

- Triển khai lên máy chủ thực tế.
- Tích hợp thanh toán online.
- Bổ sung báo cáo thống kê nâng cao.
- Tăng cường bảo mật và phân quyền chi tiết hơn.
- Hoàn thiện cơ chế realtime cho dashboard/admin.
- Chuẩn hóa toàn bộ mã nguồn theo kiến trúc MVC hoặc framework PHP.

## Tác giả

- **Nguyễn Hải Đăng**
