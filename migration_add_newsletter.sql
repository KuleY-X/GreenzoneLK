-- Database migration script to add newsletter column to existing users table
-- Run this script if you already have a users table without the newsletter column

USE greenzone_ecommerce;

-- Add newsletter column to users table if it doesn't exist
SET @sql = CONCAT('ALTER TABLE users ADD COLUMN newsletter TINYINT(1) DEFAULT 0');

-- Check if column exists
SELECT COUNT(*) INTO @exists 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'users' 
AND COLUMN_NAME = 'newsletter';

-- Execute the query only if the column doesn't exist
IF @exists = 0 THEN
    SET @sql = 'ALTER TABLE users ADD COLUMN newsletter TINYINT(1) DEFAULT 0';
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
    SELECT 'Newsletter column added successfully' as message;
ELSE
    SELECT 'Newsletter column already exists' as message;
END IF;
