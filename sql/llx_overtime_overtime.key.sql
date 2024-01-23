-- Copyright (C) 2024 SuperAdmin
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see https://www.gnu.org/licenses/.


-- BEGIN MODULEBUILDER INDEXES
ALTER TABLE llx_overtime_overtime ADD INDEX idx_overtime_overtime_rowid (rowid);
ALTER TABLE llx_overtime_overtime ADD CONSTRAINT llx_overtime_overtime_fk_user_creat FOREIGN KEY (fk_user_creat) REFERENCES llx_user(rowid);
ALTER TABLE llx_overtime_overtime ADD INDEX idx_overtime_overtime_status (status);
-- END MODULEBUILDER INDEXES

--ALTER TABLE llx_overtime_overtime ADD UNIQUE INDEX uk_overtime_overtime_fieldxy(fieldx, fieldy);

--ALTER TABLE llx_overtime_overtime ADD CONSTRAINT llx_overtime_overtime_fk_field FOREIGN KEY (fk_field) REFERENCES llx_overtime_myotherobject(rowid);

