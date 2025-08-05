# Migration Guide - Loan Applications Modular Structure

## Tổng quan

File `loan-applications.php` đã được chuyển đổi từ cấu trúc monolithic sang cấu trúc modular để dễ bảo trì và mở rộng.

## Cấu trúc cũ vs Cấu trúc mới

### Cấu trúc cũ:
```
pages/admin/loan-applications.php (1740 dòng - khó bảo trì)
```

### Cấu trúc mới:
```
pages/admin/loan-applications/
├── index.php                          # Entry point
├── includes/
│   ├── loan-applications-model.php    # Model
│   ├── loan-applications-controller.php # Controller  
│   ├── loan-applications-views.php    # View
│   └── loan-applications-modals.php   # Modals
├── test.php                           # Test file
├── MIGRATION_GUIDE.md                 # Hướng dẫn này
└── README.md                          # Documentation
```

## Router Changes

### Trước:
```php
// Trong index.php
$page_file = "pages/admin/{$page}.php";
```

### Sau:
```php
// Trong index.php
if ($page === 'loan-applications') {
    $page_file = "pages/admin/loan-applications/index.php";
} else {
    $page_file = "pages/admin/{$page}.php";
}
```

## Cách sử dụng

### 1. Truy cập trang:
```
?page=loan-applications
```

### 2. Test cấu trúc:
```
?page=loan-applications&test=1
```

## Lợi ích của cấu trúc mới

### ✅ Dễ bảo trì
- Mỗi thành phần có trách nhiệm riêng biệt
- Code được tổ chức theo chức năng
- Dễ tìm và sửa lỗi

### ✅ Dễ mở rộng
- Thêm tính năng mới không ảnh hưởng code cũ
- Có thể tái sử dụng các component
- Modular design pattern

### ✅ Performance tốt hơn
- Load chỉ những file cần thiết
- Cache được tối ưu hơn
- Memory usage thấp hơn

### ✅ Security tốt hơn
- Input validation tập trung
- Output escaping nhất quán
- Error handling chặt chẽ

## Migration Steps

### 1. Backup file cũ
```bash
cp pages/admin/loan-applications.php pages/admin/loan-applications-backup.php
```

### 2. Update router
Đã được thực hiện trong `index.php`

### 3. Test functionality
- Kiểm tra tất cả tính năng hoạt động
- Test các modal
- Test form validation
- Test AJAX requests

### 4. Update documentation
- Cập nhật README
- Cập nhật API documentation
- Cập nhật user guide

## Troubleshooting

### Lỗi "Class not found"
- Kiểm tra include path trong `index.php`
- Đảm bảo tất cả file trong thư mục `includes/` tồn tại

### Lỗi "File not found"
- Kiểm tra đường dẫn trong router
- Đảm bảo thư mục `loan-applications/` tồn tại

### Lỗi JavaScript
- Kiểm tra đường dẫn CSS/JS trong `views.php`
- Đảm bảo file `loan-applications-modular.js` tồn tại

### Lỗi Database
- Kiểm tra connection trong `model.php`
- Đảm bảo các table tồn tại

## Rollback Plan

Nếu cần rollback về cấu trúc cũ:

1. Restore file backup:
```bash
cp pages/admin/loan-applications-backup.php pages/admin/loan-applications.php
```

2. Revert router changes trong `index.php`

3. Test lại functionality

## Support

Nếu gặp vấn đề:
1. Kiểm tra error logs
2. Chạy test file: `?page=loan-applications&test=1`
3. Kiểm tra browser console cho JavaScript errors
4. Kiểm tra network tab cho AJAX requests 