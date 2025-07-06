-- Add Firebase UID column to users table
ALTER TABLE users ADD COLUMN firebase_uid VARCHAR(128) UNIQUE;