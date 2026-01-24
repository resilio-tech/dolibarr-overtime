-- Migration from 1.7.0 to 1.8.0
-- Add entity field for multi-entity support

ALTER TABLE llx_overtime_overtime ADD COLUMN entity integer DEFAULT 1 NOT NULL;
ALTER TABLE llx_overtime_overtime ADD INDEX idx_overtime_overtime_entity (entity);

ALTER TABLE llx_overtime_overtimehourskeep ADD COLUMN entity integer DEFAULT 1 NOT NULL;
ALTER TABLE llx_overtime_overtimehourskeep ADD INDEX idx_overtime_overtimehourskeep_entity (entity);

ALTER TABLE llx_overtime_overtimedaycounted ADD COLUMN entity integer DEFAULT 1 NOT NULL;
ALTER TABLE llx_overtime_overtimedaycounted ADD INDEX idx_overtime_overtimedaycounted_entity (entity);
