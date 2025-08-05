-- Thêm cột application_id vào bảng interest_waiver_applications
ALTER TABLE `interest_waiver_applications` 
ADD COLUMN `application_id` INT NULL AFTER `contract_id`;

-- Thêm foreign key constraint cho application_id
ALTER TABLE `interest_waiver_applications` 
ADD CONSTRAINT `interest_waiver_applications_ibfk_2` 
FOREIGN KEY (`application_id`) REFERENCES `loan_applications` (`id`) ON DELETE CASCADE;

-- Thêm index cho application_id để tối ưu performance
ALTER TABLE `interest_waiver_applications` 
ADD INDEX `idx_application_id` (`application_id`); 