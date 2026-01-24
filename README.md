# Overtime - Dolibarr Module

HR module for overtime management. Allows entering, validating and tracking employee overtime hours.

## Features

- Overtime entry per employee
- Validation workflow (Draft → Validated → Counted → Reimbursed)
- Automatic hour accumulation per employee
- Overtime day counting
- Extrafields support
- Functional tests included

---

## Installation

### Prerequisites

- Dolibarr >= 11.0
- PHP >= 7.0

### Module Installation

1. Copy the `overtime` folder into `htdocs/custom/`
2. Enable the module in **Setup > Modules > Human Resources**

---

## Usage

### Access
Menu: **HR > Overtime**

### Workflow

```
DRAFT (0) → VALIDATED (1) → COUNTED (4) → REIMBURSED (7)
                 ↓
            CANCELED (9)
```

### Available Pages

| Page | Description |
|------|-------------|
| `overtimeindex.php` | Dashboard |
| `overtime_list.php` | Overtime list |
| `overtime_card.php` | Detailed card |
| `overtimehourskeep_list.php` | Hours accumulation per employee |
| `overtimedaycounted_list.php` | Day counting |

---

## Architecture

### File Structure

```
overtime/
├── class/
│   ├── overtime.class.php              # Main object
│   ├── overtimehourskeep.class.php     # Hours accumulation
│   └── overtimedaycounted.class.php    # Day counting
├── core/modules/
│   └── modOvertime.class.php           # Module descriptor
├── admin/
│   ├── setup.php                       # Configuration
│   └── about.php                       # About
├── lib/
│   ├── overtime.lib.php                # Common functions
│   ├── overtime_overtime.lib.php       # Overtime object lib
│   └── overtime_overtimedaycounted.lib.php
├── sql/                                # Tables + triggers
├── test/phpunit/
│   └── OvertimeFunctionalTest.php      # Functional tests
├── backport/v16/                       # Dolibarr 16 compatibility
├── overtimeindex.php                   # Dashboard
├── overtime_list.php                   # List
├── overtime_card.php                   # Card
├── overtimehourskeep_list.php          # Hours accumulation
└── overtimedaycounted_list.php         # Day counting
```

### Business Objects

#### `Overtime`
Overtime record:

| Field | Type | Description |
|-------|------|-------------|
| `rowid` | int | Technical ID |
| `ref` | varchar | Unique reference |
| `fk_user` | int | Employee concerned |
| `date_overtime` | date | Overtime date |
| `duration` | double | Duration in hours |
| `status` | int | Status (0=draft, 1=validated, etc.) |
| `note` | text | Comment |

**Statuses:**
- `STATUS_DRAFT` (0): Draft
- `STATUS_VALIDATED` (1): Validated
- `STATUS_DECOMPTED` (4): Counted
- `STATUS_REMBOURSED` (7): Reimbursed
- `STATUS_CANCELED` (9): Canceled

#### `OvertimeHoursKeep`
Hours accumulation per employee:

| Field | Type | Description |
|-------|------|-------------|
| `fk_user` | int | Employee |
| `total_hours` | double | Total accumulated hours |
| `year` | int | Year |

#### `OvertimeDayCounted`
Day counting:

| Field | Type | Description |
|-------|------|-------------|
| `fk_user` | int | Employee |
| `date_counted` | date | Counted date |
| `counted` | int | Number of days |

### SQL Tables

```
llx_overtime_overtime           # Overtime records
llx_overtime_overtimehourskeep  # Hours accumulation
llx_overtime_overtimedaycounted # Day counting
```

---

## Development

### Running Tests

```bash
cd htdocs/custom/overtime/test/phpunit
phpunit OvertimeFunctionalTest.php
```

### Dolibarr 16 Compatibility

The `backport/v16/` folder contains necessary adaptations for Dolibarr 16.

### Dolibarr Tables Used

| Table | Usage |
|-------|-------|
| `llx_user` | Employees |

---

## License

GPLv3 - See COPYING file
