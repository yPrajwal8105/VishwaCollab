-- Removes the phone column from students table (safe to run multiple times).
-- Compatible with MySQL 5.7+ (shared hosting) and MySQL 8+.

SET @db := DATABASE();
SET @exists := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @db
      AND TABLE_NAME = 'students'
      AND COLUMN_NAME = 'phone'
);

SET @sql := IF(@exists > 0,
    'ALTER TABLE students DROP COLUMN phone',
    'SELECT ''OK: students.phone already removed'' AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

