-- Bảng lưu OTP xác thực hợp đồng
CREATE TABLE IF NOT EXISTS `otp_verification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contract_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `otp_code` varchar(6) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `send_method` enum('email','sms','both') DEFAULT 'email',
  `status` enum('sent','verified','expired','failed') DEFAULT 'sent',
  `expires_at` datetime NOT NULL,
  `verified_at` datetime DEFAULT NULL,
  `attempts` int(11) DEFAULT 0,
  `max_attempts` int(11) DEFAULT 3,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_contract_id` (`contract_id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_otp_code` (`otp_code`),
  KEY `idx_status` (`status`),
  KEY `idx_expires_at` (`expires_at`),
  FOREIGN KEY (`contract_id`) REFERENCES `electronic_contracts` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng lưu lịch sử tải document
CREATE TABLE IF NOT EXISTS `document_downloads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contract_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `otp_verification_id` int(11) NOT NULL,
  `document_type` varchar(50) DEFAULT 'contract',
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `download_count` int(11) DEFAULT 1,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `downloaded_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_contract_id` (`contract_id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_otp_verification_id` (`otp_verification_id`),
  FOREIGN KEY (`contract_id`) REFERENCES `electronic_contracts` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`otp_verification_id`) REFERENCES `otp_verification` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;