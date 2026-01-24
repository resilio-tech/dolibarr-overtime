# CHANGELOG OVERTIME FOR [DOLIBARR ERP CRM](https://www.dolibarr.org)

## 1.9.0 (2026-01-24)

### Added
- HoursPerDayCalculator class for calculating hours per day from weekly hours
- Unit tests for HoursPerDayCalculator and OvertimeValidator
- Auto-creation of extrafield "overtime_daysperweek" on Users at module init
- Native Dolibarr weekly hours support (OVERTIME_USE_NATIVE_WEEKLYHOURS option)
- Help section on setup page explaining configuration methods
- Translated permissions (FR/EN)

### Changed
- Multi-entity support enabled for OvertimeDayCounted and OvertimeHoursKeep classes
- Entity field added to class field definitions
- Permissions now use translation keys

### Fixed
- Removed 24-hour limit on overtime hours validation
- Fixed trigger names (MYOBJECT_* -> proper class names)

### Removed
- Legacy MYOBJECT template code cleanup
- Removed unused setup page actions and form elements
- Removed ajax/myobject.php

## 1.8.0 (2026-01-24)

### Added
- README.md documentation
- GitHub build workflow for releases

### Changed
- Code cleanup and documentation improvements

## 1.7 (2024-07-22)

### Fixed
- List filter corrections
- Salary loop fix in lists

## 1.6 (2024-07-22)

### Fixed
- Various overtime fixes

## 1.5 (2024-07-22)

### Added
- Hours keeps functionality (OvertimeHoursKeep)
- Mass link refunds feature

## 1.4 (2024-03-26)

### Added
- Permission to delete overtime records

## 1.3 (2024-03-21)

### Added
- Count overtime as CP (paid leave) integration
- Holiday type selection for CP logs
- Link to salary payments (fk_payment)

### Changed
- Removed visibility of certain elements
- Updated CP integration

## 1.2 (2024-03-21)

### Added
- OvertimeDayCounted class for tracking counted days
- OvertimeHoursCounted functionality
- Status management improvements

## 1.1 (2024-01-24)

### Changed
- Version bump

## 1.0 (2024-01-23)

### Added
- Initial version
- Basic overtime management
- Overtime creation and validation
- Delete protection after validation
