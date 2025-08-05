# Loan Applications - Modular Structure

## Tổng quan

File `loan-applications.php` đã được tái cấu trúc thành hệ thống modular với các thành phần riêng biệt để dễ bảo trì và mở rộng.

## Cấu trúc thư mục

```
loan-applications/
├── index.php                          # File chính - Entry point
├── includes/
│   ├── loan-applications-model.php    # Model - Xử lý dữ liệu
│   ├── loan-applications-controller.php # Controller - Logic nghiệp vụ
│   ├── loan-applications-views.php    # View - Hiển thị giao diện
│   └── loan-applications-modals.php   # Modals - Các modal
└── README.md                          # Hướng dẫn này
```

## Các thành phần chính

### 1. Model (`loan-applications-model.php`)
- Xử lý tất cả tương tác với database
- Chứa các method CRUD cho loan applications
- Sanitize và validate dữ liệu
- Format dữ liệu hiển thị

**Các method chính:**
- `getAllApplications()` - Lấy danh sách tất cả đơn vay
- `getApplicationById($id)` - Lấy chi tiết đơn vay
- `createApplication($data)` - Tạo đơn vay mới
- `updateApplication($id, $data)` - Cập nhật đơn vay
- `deleteApplication($id)` - Xóa đơn vay
- `approveApplication($id, $data)` - Phê duyệt đơn vay
- `rejectApplication($id, $data)` - Từ chối đơn vay

### 2. Controller (`loan-applications-controller.php`)
- Xử lý logic nghiệp vụ
- Điều hướng request
- Chuẩn bị dữ liệu cho view
- Xử lý form submission

**Các method chính:**
- `handleRequest()` - Xử lý request chính
- `handlePostRequest($action)` - Xử lý POST requests
- `handleGetRequest($action)` - Xử lý GET requests
- `prepareApplicationData($postData)` - Chuẩn bị dữ liệu đơn vay

### 3. View (`loan-applications-views.php`)
- Hiển thị giao diện
- Render HTML
- Quản lý CSS và JS

**Các method chính:**
- `render()` - Render giao diện chính
- `renderHeader()` - Render header với CSS/JS
- `renderMainContent()` - Render nội dung chính
- `renderModals()` - Render các modal

### 4. Modals (`loan-applications-modals.php`)
- Chứa tất cả các modal
- Form tạo đơn vay mới
- Modal phê duyệt
- Modal từ chối
- Modal xác nhận xóa

## API Endpoints

### 1. `pages/admin/api/loan-application-detail.php`
- **Method:** GET
- **Purpose:** Lấy chi tiết đơn vay
- **Parameters:** `id` (application ID)
- **Response:** JSON với thông tin đơn vay

### 2. `pages/admin/api/check-approval-permissions.php`
- **Method:** GET
- **Purpose:** Kiểm tra quyền phê duyệt
- **Parameters:** `application_id`
- **Response:** JSON với thông tin quyền

## JavaScript

File `assets/js/loan-applications-modular.js` chứa tất cả logic JavaScript:
- Xử lý modal
- Form validation
- AJAX requests
- Currency formatting
- Insurance calculation

## Cách sử dụng

### 1. Truy cập trang chính
```
?page=loan-applications
```

### 2. Tạo đơn vay mới
- Click nút "Tạo đơn vay mới"
- Điền thông tin trong modal
- Submit form

### 3. Phê duyệt đơn vay
- Click nút "Duyệt" (chỉ hiển thị với đơn vay pending)
- Điền thông tin phê duyệt
- Submit form

### 4. Từ chối đơn vay
- Click nút "Từ chối" (chỉ hiển thị với đơn vay pending)
- Chọn lý do và ghi chú
- Submit form

## Tính năng chính

### 1. Quản lý đơn vay
- Tạo, sửa, xóa đơn vay
- Xem chi tiết đơn vay
- Phê duyệt/từ chối đơn vay

### 2. Tính toán bảo hiểm
- Tự động tính phí bảo hiểm
- Hỗ trợ nhiều loại bảo hiểm
- Hiển thị chi tiết từng loại

### 3. Validation
- Validate form client-side và server-side
- Hiển thị lỗi rõ ràng
- Auto-format currency

### 4. Phân quyền
- Kiểm tra quyền phê duyệt
- Role-based access control
- Department-based permissions

## Mở rộng

### Thêm tính năng mới
1. Thêm method vào Model
2. Thêm logic vào Controller
3. Thêm giao diện vào View
4. Thêm JavaScript nếu cần

### Thêm validation
1. Thêm validation rule vào Model
2. Thêm client-side validation vào JavaScript
3. Thêm server-side validation vào Controller

### Thêm API endpoint
1. Tạo file PHP mới trong thư mục `api/`
2. Thêm logic xử lý
3. Trả về JSON response

## Lưu ý

- Tất cả file đều sử dụng UTF-8 encoding
- Database queries sử dụng prepared statements
- Input được sanitize trước khi lưu
- Output được escape trước khi hiển thị
- JavaScript sử dụng ES6+ syntax
- CSS sử dụng Bootstrap 5

## Troubleshooting

### Lỗi thường gặp
1. **Class not found:** Kiểm tra include path
2. **Database error:** Kiểm tra connection và query
3. **JavaScript error:** Kiểm tra console browser
4. **Permission denied:** Kiểm tra file permissions

### Debug
1. Enable error reporting trong PHP
2. Check browser console cho JavaScript errors
3. Check network tab cho AJAX requests
4. Check database logs 