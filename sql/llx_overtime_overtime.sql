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


CREATE TABLE llx_overtime_overtime(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL,
	date_creation datetime NOT NULL, 
	tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
	fk_user_creat integer NOT NULL, 
	fk_user_modif integer, 
	status integer NOT NULL, 
	hours real NOT NULL, 
	date_start date NOT NULL, 
	date_end date NOT NULL
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;

-- Add field 'ref' to table 'llx_overtime_overtime'
ALTER TABLE llx_overtime_overtime
ADD COLUMN ref varchar(255) NOT NULL AFTER rowid;

-- Add field 'fk_user' to table 'llx_overtime_overtime'
ALTER TABLE llx_overtime_overtime
ADD COLUMN fk_user integer NOT NULL AFTER tms;

-- Add field 'reason' to table 'llx_overtime_overtime'
ALTER TABLE llx_overtime_overtime
ADD COLUMN reason text NOT NULL;

-- Add field 'fk_payment' to table 'llx_overtime_overtime'
ALTER TABLE llx_overtime_overtime
ADD COLUMN fk_payment integer NOT NULL AFTER reason;
