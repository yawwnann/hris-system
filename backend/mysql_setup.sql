-- ============================================================
-- HRIS SYSTEM - MySQL Setup Script
-- Jalankan script ini sebagai root MySQL:
--   sudo mysql < mysql_setup.sql
--   ATAU: sudo mysql, lalu copy-paste isinya
-- ============================================================

-- 1. Buat Database
CREATE DATABASE IF NOT EXISTS hris_system
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- 2. Buat User
CREATE USER IF NOT EXISTS 'hris_user'@'localhost' IDENTIFIED BY 'lazizmuhris';
CREATE USER IF NOT EXISTS 'hris_user'@'127.0.0.1' IDENTIFIED BY 'lazizmuhris';

-- 3. Grant Privileges
GRANT ALL PRIVILEGES ON hris_system.* TO 'hris_user'@'localhost';
GRANT ALL PRIVILEGES ON hris_system.* TO 'hris_user'@'127.0.0.1';

FLUSH PRIVILEGES;

-- 4. Verifikasi
SELECT User, Host FROM mysql.user WHERE User = 'hris_user';
SHOW DATABASES LIKE 'hris_system';
