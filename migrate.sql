-- Migration: Add missing columns to contacts and notes tables
-- Run this if you get "Unknown column" errors

-- Step 1: Add user_id to contacts if missing
ALTER TABLE `contacts` ADD COLUMN `user_id` INT DEFAULT 1 AFTER `id`;

-- Step 2: Add foreign key to contacts
ALTER TABLE `contacts` ADD CONSTRAINT `fk_contacts_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Step 3: Add unique constraint to contacts
ALTER TABLE `contacts` ADD UNIQUE KEY `uq_contacts_email_user` (`email`, `user_id`);

-- Step 4: Add user_id to notes if missing
ALTER TABLE `notes` ADD COLUMN `user_id` INT DEFAULT 1 AFTER `contact_id`;

-- Step 5: Add foreign key to notes
ALTER TABLE `notes` ADD CONSTRAINT `fk_notes_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Verify the table structures
DESCRIBE `contacts`;
DESCRIBE `notes`;
