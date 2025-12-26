-- Users table: stores system users (agents, admins)
-- Users table: stores system users (agents, admins)
CREATE TABLE IF NOT EXISTS `Users` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `firstname` VARCHAR(50) NOT NULL,
    `lastname` VARCHAR(50) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('Admin','Member') NOT NULL DEFAULT 'Member',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Contacts table: stores client information
CREATE TABLE IF NOT EXISTS `contacts` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `title` VARCHAR(10),
    `firstname` VARCHAR(50) NOT NULL,
    `lastname` VARCHAR(50) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `telephone` VARCHAR(20),
    `company` VARCHAR(100),
    `type` VARCHAR(20) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_contacts_email_user` (`email`, `user_id`),
    KEY `idx_contacts_user_id` (`user_id`),
    CONSTRAINT `fk_contacts_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Notes table: stores comments linked to contacts (tracks creator as created_by)
CREATE TABLE IF NOT EXISTS `notes` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `contact_id` INT NOT NULL,
    `comment` TEXT NOT NULL,
    `created_by` INT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_notes_contact_id` (`contact_id`),
    KEY `idx_notes_created_by` (`created_by`),
    CONSTRAINT `fk_notes_contact` FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_notes_created_by` FOREIGN KEY (`created_by`) REFERENCES `Users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Insert an admin user (example)
INSERT INTO `Users` (`firstname`, `lastname`, `email`, `password`, `role`)
VALUES ('Admin', 'User', 'admin@project2.com', 'password123', 'Admin')
ON DUPLICATE KEY UPDATE `email` = `email`;