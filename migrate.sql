-- Migration: Add user_id column to contacts table if it doesn't exist
-- Run this if you get "Unknown column 'user_id'" errors

-- Step 1: Check if user_id column exists, if not add it
ALTER TABLE `contacts` ADD COLUMN `user_id` INT DEFAULT 1 AFTER `id`;

-- Step 2: Add the foreign key constraint (if it doesn't exist)
ALTER TABLE `contacts` ADD CONSTRAINT `fk_contacts_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Step 3: Make sure unique constraint exists
ALTER TABLE `contacts` ADD UNIQUE KEY `uq_contacts_email_user` (`email`, `user_id`);

-- Verify the table structure
DESCRIBE `contacts`;
