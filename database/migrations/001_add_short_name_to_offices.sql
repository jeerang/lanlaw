-- =====================================================
-- Migration: Add short_name to offices table
-- Date: 2026-02-07
-- =====================================================

-- Add short_name column to offices table
ALTER TABLE offices ADD COLUMN short_name VARCHAR(20) AFTER code;

-- Update existing records with short_name from code
UPDATE offices SET short_name = code WHERE short_name IS NULL;

-- Add item_no to disbursement_items if missing (for ordering)
-- ALTER TABLE disbursement_items MODIFY COLUMN item_no INT DEFAULT 0;
