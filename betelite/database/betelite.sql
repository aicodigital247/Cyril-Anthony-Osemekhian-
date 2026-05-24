-- BETELITE Sportsbook Prediction Marketplace Database Schema
-- Optimized for MySQL 5.7+ & Shared Hosting cPanel
-- Table prefix: be_

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `be_ratings`;
DROP TABLE IF EXISTS `be_ads`;
DROP TABLE IF EXISTS `be_notifications`;
DROP TABLE IF EXISTS `be_withdrawals`;
DROP TABLE IF EXISTS `be_deposits`;
DROP TABLE IF EXISTS `be_transactions`;
DROP TABLE IF EXISTS `be_wallets`;
DROP TABLE IF EXISTS `be_orders`;
DROP TABLE IF EXISTS `be_cart`;
DROP TABLE IF EXISTS `be_prediction_items`;
DROP TABLE IF EXISTS `be_predictions`;
DROP TABLE IF EXISTS `be_matches`;
DROP TABLE IF EXISTS `be_predictors`;
DROP TABLE IF EXISTS `be_users`;
DROP TABLE IF EXISTS `be_referrals`;
DROP TABLE IF EXISTS `be_subscriptions`;
DROP TABLE IF EXISTS `be_support_tickets`;
DROP TABLE IF EXISTS `be_platform_settings`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. USERS TABLE
CREATE TABLE `be_users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) DEFAULT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `avatar` VARCHAR(255) DEFAULT 'default_avatar.png',
  `role` ENUM('user', 'predictor', 'admin') DEFAULT 'user',
  `status` ENUM('active', 'suspended', 'pending') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_email` (`email`),
  INDEX `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. PREDICTORS TABLE
CREATE TABLE `be_predictors` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL UNIQUE,
  `display_name` VARCHAR(100) NOT NULL,
  `bio` TEXT DEFAULT NULL,
  `badge` ENUM('Standard', 'VIP Pro', 'Elite Gold', 'Master Trader') DEFAULT 'Standard',
  `accuracy_rate` DECIMAL(5,2) DEFAULT '0.00',
  `total_predictions` INT DEFAULT 0,
  `won_predictions` INT DEFAULT 0,
  `subscribers_count` INT DEFAULT 0,
  `referral_code` VARCHAR(20) UNIQUE DEFAULT NULL,
  `status` ENUM('active', 'review_pending', 'suspended') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `be_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. MATCHES TABLE (Live or Upcoming)
CREATE TABLE `be_matches` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sport` ENUM('Football', 'Basketball', 'Tennis', 'Ice Hockey', 'Cricket') DEFAULT 'Football',
  `league` VARCHAR(100) NOT NULL,
  `home_team` VARCHAR(100) NOT NULL,
  `away_team` VARCHAR(100) NOT NULL,
  `home_score` INT DEFAULT 0,
  `away_score` INT DEFAULT 0,
  `match_time` INT DEFAULT 0, -- Minute of live match
  `match_status` ENUM('Upcoming', 'Live', 'Completed', 'Postponed') DEFAULT 'Upcoming',
  `start_datetime` DATETIME NOT NULL,
  `home_logo` VARCHAR(255) DEFAULT NULL,
  `away_logo` VARCHAR(255) DEFAULT NULL,
  -- Live metrics (AJAX analytics polling)
  `possession_home` INT DEFAULT 50,
  `possession_away` INT DEFAULT 50,
  `shots_home` INT DEFAULT 0,
  `shots_away` INT DEFAULT 0,
  `corners_home` INT DEFAULT 0,
  `corners_away` INT DEFAULT 0,
  `cards_yellow_home` INT DEFAULT 0,
  `cards_yellow_away` INT DEFAULT 0,
  `cards_red_home` INT DEFAULT 0,
  `cards_red_away` INT DEFAULT 0,
  -- Commentary JSON
  `live_commentary` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_status` (`match_status`),
  INDEX `idx_datetime` (`start_datetime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. PREDICTIONS (The items sold on the marketplace)
CREATE TABLE `be_predictions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `predictor_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `price` DECIMAL(10,2) DEFAULT '0.00', -- 0 = Free
  `confidence` INT DEFAULT 80, -- Confidence score 1-100%
  `is_vip` TINYINT(1) DEFAULT 0,
  `status` ENUM('Pending', 'Active', 'Won', 'Lost', 'Refunded') DEFAULT 'Active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`predictor_id`) REFERENCES `be_predictors`(`id`) ON DELETE CASCADE,
  INDEX `idx_vip` (`is_vip`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. PREDICTION_ITEMS (Individual match selections inside a prediction slip or bundle)
CREATE TABLE `be_prediction_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `prediction_id` INT NOT NULL,
  `match_id` INT NOT NULL,
  `market` VARCHAR(100) NOT NULL, -- e.g. "Home Win", "Over 2.5 Goals", "GG"
  `odds` DECIMAL(4,2) NOT NULL,
  `status` ENUM('Pending', 'Won', 'Lost', 'Void') DEFAULT 'Pending',
  FOREIGN KEY (`prediction_id`) REFERENCES `be_predictions`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`match_id`) REFERENCES `be_matches`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. WALLETS
CREATE TABLE `be_wallets` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL UNIQUE,
  `balance` DECIMAL(15,2) DEFAULT '0.00',
  `currency` VARCHAR(10) DEFAULT 'NGN', -- Can be NGN, USD, GHS, KES, USDT
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `be_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. TRANSACTIONS
CREATE TABLE `be_transactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `wallet_id` INT NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `type` ENUM('deposit', 'withdrawal', 'purchase', 'earnings', 'referral', 'refund', 'subscription') NOT NULL,
  `status` ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
  `reference` VARCHAR(100) NOT NULL UNIQUE,
  `payment_method` VARCHAR(50) DEFAULT 'wallet', -- e.g. 'Paystack', 'Flutterwave', 'USDT', 'Bank'
  `description` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`wallet_id`) REFERENCES `be_wallets`(`id`) ON DELETE CASCADE,
  INDEX `idx_ref` (`reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. CART (Simple Database-Driven Cart for Shared Session Backup)
CREATE TABLE `be_cart` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `prediction_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_user_pred` (`user_id`, `prediction_id`),
  FOREIGN KEY (`user_id`) REFERENCES `be_users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`prediction_id`) REFERENCES `be_predictions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. ORDERS (Completed prediction marketplace purchases)
CREATE TABLE `be_orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `prediction_id` INT NOT NULL,
  `amount_paid` DECIMAL(10,2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_user_order` (`user_id`, `prediction_id`),
  FOREIGN KEY (`user_id`) REFERENCES `be_users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`prediction_id`) REFERENCES `be_predictions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. WITHDRAWALS (Specifically tracks predictor cashout approvals)
CREATE TABLE `be_withdrawals` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `bank_name` VARCHAR(100) NOT NULL,
  `account_number` VARCHAR(30) NOT NULL,
  `account_name` VARCHAR(100) NOT NULL,
  `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  `reference` VARCHAR(100) NOT NULL UNIQUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `be_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. DEPOSITS
CREATE TABLE `be_deposits` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `payment_method` VARCHAR(50) NOT NULL,
  `reference` VARCHAR(100) NOT NULL UNIQUE,
  `status` ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `be_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. RUNNING ADVERTISEMENTS SYSTEM
CREATE TABLE `be_ads` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `banner_url` VARCHAR(255) NOT NULL,
  `target_url` VARCHAR(255) NOT NULL,
  `position` ENUM('header', 'sidebar', 'footer', 'popup') DEFAULT 'header',
  `impressions` INT DEFAULT 0,
  `clicks` INT DEFAULT 0,
  `status` ENUM('active', 'paused', 'expired') DEFAULT 'active',
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. NOTIFICATIONS SYSTEM
CREATE TABLE `be_notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `be_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. RATINGS/REVIEWS OF PREDICTORS
CREATE TABLE `be_ratings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `predictor_id` INT NOT NULL,
  `rating` INT CHECK (`rating` BETWEEN 1 AND 5),
  `review` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `be_users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`predictor_id`) REFERENCES `be_predictors`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uniq_rating` (`user_id`, `predictor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. REFERRALS SYSTEM
CREATE TABLE `be_referrals` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `referrer_user_id` INT NOT NULL,
  `referred_user_id` INT NOT NULL UNIQUE,
  `commission_earned` DECIMAL(10,2) DEFAULT '0.00',
  `status` ENUM('pending', 'active', 'rewarded') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`referrer_user_id`) REFERENCES `be_users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`referred_user_id`) REFERENCES `be_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. SUBSCRIPTIONS (VIP Bundle Access)
CREATE TABLE `be_subscriptions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `predictor_id` INT NOT NULL,
  `start_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `end_date` DATETIME NOT NULL,
  `amount_paid` DECIMAL(10,2) NOT NULL,
  `status` ENUM('active', 'expired', 'cancelled') DEFAULT 'active',
  FOREIGN KEY (`user_id`) REFERENCES `be_users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`predictor_id`) REFERENCES `be_predictors`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. PLATFORM GLOBAL SETTINGS
CREATE TABLE `be_platform_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(50) NOT NULL UNIQUE,
  `setting_value` TEXT DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEEDING SAMPLE DATA
INSERT INTO `be_users` (`id`, `username`, `email`, `password_hash`, `full_name`, `phone`, `role`, `status`) VALUES
(1, 'superadmin', 'admin@betelite.com', '$2y$10$wS2a2WvTq/s99E5.CGrKSu0R0G9T08PZqY96Y2UqO.Yf9f6Mfe7uS', 'BetElite Admin', '+2348000000000', 'admin', 'active'),
(2, 'kingtips', 'king@betelite.com', '$2y$10$wS2a2WvTq/s99E5.CGrKSu0R0G9T08PZqY96Y2UqO.Yf9f6Mfe7uS', 'Baba Kolawole (KingTips)', '+2348111222333', 'predictor', 'active'),
(3, 'elitesniper', 'sniper@betelite.com', '$2y$10$wS2a2WvTq/s99E5.CGrKSu0R0G9T08PZqY96Y2UqO.Yf9f6Mfe7uS', 'Elite Sniper Tips', '+2347099887766', 'predictor', 'active'),
(4, 'puntersand', 'punter@betelite.com', '$2y$10$wS2a2WvTq/s99E5.CGrKSu0R0G9T08PZqY96Y2UqO.Yf9f6Mfe7uS', 'Anthony Cyril', '+2347012345678', 'user', 'active');

INSERT INTO `be_predictors` (`id`, `user_id`, `display_name`, `bio`, `badge`, `accuracy_rate`, `total_predictions`, `won_predictions`, `subscribers_count`, `referral_code`) VALUES
(1, 2, 'KingTips (Baba Kola)', 'Nigeria premier football predictor. Specialists in over/under and long slips.', 'Elite Gold', 84.50, 420, 355, 128, 'KINGKOLA'),
(2, 3, 'Elite Sniper', 'High probability single bets and value hunting accumulator cards.', 'VIP Pro', 91.20, 150, 137, 245, 'SNIPERTIPS');

INSERT INTO `be_wallets` (`user_id`, `balance`, `currency`) VALUES
(1, 1500000.00, 'NGN'),
(2, 45200.00, 'NGN'),
(3, 128900.00, 'NGN'),
(4, 50000.00, 'NGN'); -- Give sample punter starting balance of 50k NGN!

INSERT INTO `be_matches` (`id`, `sport`, `league`, `home_team`, `away_team`, `home_score`, `away_score`, `match_time`, `match_status`, `start_datetime`, `possession_home`, `possession_away`, `shots_home`, `shots_away`, `corners_home`, `corners_away`, `live_commentary`) VALUES
(1, 'Football', 'Premier League', 'Arsenal', 'Manchester United', 2, 1, 74, 'Live', '2026-05-23 14:00:00', 58, 42, 12, 7, 6, 4, '[{\"time\":70, \"text\":\"GOAL! Martin Odegaard scores a beautiful curler from the edge of the box! 2-1 Assist by Bukayo Saka.\"}, {\"time\":45, \"text\":\"Halftime: Controlled show by Arsenal, Manchester United hitting well on counter attacks.\"}, {\"time\":12, \"text\":\"GOAL! Marcus Rashford scores on a counter, beautiful low finish past Raya! 0-1\"}]'),
(2, 'Football', 'La Liga', 'Real Madrid', 'Barcelona', 0, 0, 0, 'Upcoming', '2026-05-23 20:00:00', 50, 50, 0, 0, 0, 0, '[]'),
(3, 'Football', 'Serie A', 'Juventus', 'AC Milan', 1, 1, 90, 'Completed', '2026-05-23 12:00:00', 48, 52, 9, 11, 3, 5, '[{\"time\":90, \"text\":\"Fulltime whistles: Juve 1, Milan 1. Shared spoils in a gritty affair.\"}, {\"time\":82, \"text\":\"GOAL! Rafael Leao hammers it off the underside of the crossbar! Milan levels! 1-1.\"}, {\"time\":33, \"text\":\"GOAL! Vlahovic converts the cross to draw first blood! 1-0\"}]'),
(4, 'Football', 'Champions League', 'Manchester City', 'Real Madrid', 4, 3, 90, 'Completed', '2026-05-22 19:45:00', 64, 36, 18, 10, 8, 2, '[]');

INSERT INTO `be_predictions` (`id`, `predictor_id`, `title`, `description`, `price`, `confidence`, `is_vip`, `status`) VALUES
(1, 1, 'Weekend Goal Rush Slip (Odds 2.45)', 'Carefully analyzed over 2.5 banker accumulator for English Prem.', 1500.00, 88, 0, 'Active'),
(2, 2, 'Champions League VIP Banker (Odds 1.85)', 'Super confidence single bet for City vs Real. Match results prediction.', 5000.00, 95, 1, 'Active'),
(3, 1, 'Serie A Under-Value Single (Odds 2.10)', 'A strategic breakdown of Juve vs Milan under 2.5 expectations.', 0.00, 82, 0, 'Won');

INSERT INTO `be_prediction_items` (`prediction_id`, `match_id`, `market`, `odds`, `status`) VALUES
(1, 1, 'Over 2.5 Goals', 1.65, 'Pending'),
(1, 2, 'Both Teams to Score (GG)', 1.48, 'Pending'),
(2, 4, 'Manchester City Win', 1.85, 'Won'),
(3, 3, 'Under 2.5 Goals', 2.10, 'Won');

INSERT INTO `be_platform_settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'BETELITE'),
('admin_email', 'billing@betelite.com'),
('deposit_rate_usd_ngn', '1550'),
('withdrawal_fee_percent', '2'),
('vip_commission_percent', '25');

-- Add dynamic logs
INSERT INTO `be_transactions` (`wallet_id`, `amount`, `type`, `status`, `reference`, `payment_method`, `description`) VALUES
(4, 50000.00, 'deposit', 'completed', 'TXN-9092049281', 'Paystack', 'Initial funding for test punter puntersand');
