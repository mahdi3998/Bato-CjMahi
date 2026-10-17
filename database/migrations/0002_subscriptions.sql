-- database/migrations/0002_subscriptions.sql

CREATE TABLE IF NOT EXISTS subscriptions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT UNSIGNED NOT NULL,
  panel_type VARCHAR(100) NOT NULL,
  remote_id VARCHAR(255) NULL,
  remote_uuid VARCHAR(255) NULL,
  username VARCHAR(255) NULL,
  subscription_url TEXT NULL,
  config_links JSON NULL,
  location_ids JSON NULL,
  status VARCHAR(50) DEFAULT 'pending',
  expires_at TIMESTAMP NULL,
  total_volume_bytes BIGINT NULL,
  used_bytes BIGINT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
