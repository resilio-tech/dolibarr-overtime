# Overtime - Dolibarr Module

Track the overtime worked by employees and settle it, either as leave days or as pay.

## Goal

An employee declares the overtime they worked. A manager validates it, then settles it in one of two ways:

- **Counted**: the hours are converted into leave days, added to the employee's leave balance in the Dolibarr Leave module
- **Reimbursed**: the hours are paid with the salary. The overtime is linked to the salary payment

The two ways are alternatives: an overtime is either counted or reimbursed, never both.

Each year, the first hours counted for an employee go to a reserve and give neither leave nor pay. They are the overtime already included in the employment contract. The size of this reserve is set in the module setup.

---

## Workflow

```
DRAFT --> VALIDATED --> COUNTED     (converted into leave days)
  |           |     \-> REIMBURSED  (paid with the salary)
  \-----------\-----> CANCELED     (refused)
```

| Status | Meaning |
|--------|---------|
| Draft | Declared by the employee, can still be edited or deleted |
| Validated | Accepted by the manager, waiting to be settled |
| Counted | Converted into leave days, final |
| Reimbursed | Paid with the salary. Can be reversed to validated if the payment was a mistake |
| Canceled | Refused, kept for the record |

---

## How counting works

When an overtime is counted:

1. Its hours are added to the employee's pending hours.
2. If the yearly reserve is not full yet, pending hours fill it first. The reserve restarts at zero each year.
3. The remaining pending hours are converted into whole leave days, using the employee's hours per day.
4. The days are added to the employee's balance for the leave type chosen in the setup.
5. The hours that do not make a full day stay pending for the next count.

The hours per day come from the employee's weekly hours divided by the days worked per week.

Example: an employee works 27h per week over 3 days, so 9h per day. With a reserve of 10h, a first overtime of 25h fills the reserve (10h), gives 1 leave day (9h) and keeps 6h pending.

---

## Installation

Prerequisites:

- Dolibarr >= 11.0
- PHP >= 7.4
- Leave module enabled

Steps:

1. Copy the `overtime` folder into `htdocs/custom/`
2. Enable the module in **Setup > Modules > Human Resources**
3. Choose the leave type to credit in the module setup

---

## Setup

In **Setup > Modules > Overtime**:

| Setting | Description |
|---------|-------------|
| Hours to reserve | Hours per employee and per year that give neither leave nor pay. 0 disables the reserve |
| Use native Dolibarr weekly hours | Take the hours per day from the weekly hours of the user card (recommended) |
| Default days per week | Used when the user has no "days per week" value |
| Extrafield for hours per day | Only when native weekly hours are disabled: user extrafield holding the hours per day |
| Leave type | Leave type credited with the days obtained from overtime. Required for counting |

For each employee, fill in the weekly hours on the user card (HR tab). For part-time employees, also fill in the "days per week" field, which the module creates on users.

---

## Usage

Menu **HR > Overtime**:

- **List** / **New**: overtime records. Employees see their own overtime and the overtime of the people they manage
- **Counted days**: the reserve used per employee and per year
- **Kept hours**: the hours pending per employee, not yet converted into a leave day

Permissions:

| Permission | Allows |
|------------|--------|
| Change overtime status | Count, reimburse, cancel and link payments |
| See the overtime of all employees | See every overtime, not only the ones of the user's team |
| View counted days | See the yearly reserves |
| Create/modify counted days | Correct the yearly reserves |
| Delete counted days | Delete the yearly reserves |
| View kept hours | See the pending hours |
| Modify kept hours | Correct the pending hours |
| Delete kept hours | Delete the pending hours |

---

## Known issues

Several bugs still prevent the module from working as described above (decimal hours, reserve, double counting, permissions). They are tracked in the GitHub issues of the repository.

---

## Tests

```bash
phpunit test/phpunit/unit/
```

The unit tests cover the hours per day calculation and the input validation.

---

## License

GPLv3 or (at your option) any later version. See file COPYING for more information.
