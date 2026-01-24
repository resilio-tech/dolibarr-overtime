<?php
/* Copyright (C) 2024 SuperAdmin
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file       class/OvertimeValidator.class.php
 * \ingroup    overtime
 * \brief      Class for validating overtime data (standalone, no Dolibarr dependency)
 */

/**
 * Class OvertimeValidator
 *
 * Validates overtime data before creation or update
 * This class has no dependency on Dolibarr and can be unit tested independently
 */
class OvertimeValidator
{
	/**
	 * @var array Error messages
	 */
	public $errors = array();

	/**
	 * Constructor
	 */
	public function __construct()
	{
	}

	/**
	 * Validate overtime data
	 *
	 * @param array $data Overtime data with keys: date_start, date_end, hours, fk_user, reason
	 * @return bool True if valid, false otherwise (check $this->errors for details)
	 */
	public function validate(array $data): bool
	{
		$this->errors = array();

		$this->validateDates($data);
		$this->validateHours($data);
		$this->validateUser($data);
		$this->validateReason($data);

		return empty($this->errors);
	}

	/**
	 * Validate date_start and date_end
	 *
	 * @param array $data Overtime data
	 * @return void
	 */
	protected function validateDates(array $data): void
	{
		$dateStart = isset($data['date_start']) ? $data['date_start'] : null;
		$dateEnd = isset($data['date_end']) ? $data['date_end'] : null;

		// Check required
		if (empty($dateStart)) {
			$this->errors[] = 'DateStartRequired';
		}

		if (empty($dateEnd)) {
			$this->errors[] = 'DateEndRequired';
		}

		// Check date_end >= date_start
		if (!empty($dateStart) && !empty($dateEnd)) {
			$startTimestamp = $this->parseDate($dateStart);
			$endTimestamp = $this->parseDate($dateEnd);

			if ($startTimestamp !== false && $endTimestamp !== false) {
				if ($endTimestamp < $startTimestamp) {
					$this->errors[] = 'DateEndBeforeDateStart';
				}
			}
		}
	}

	/**
	 * Validate hours
	 *
	 * @param array $data Overtime data
	 * @return void
	 */
	protected function validateHours(array $data): void
	{
		$hours = isset($data['hours']) ? $data['hours'] : null;

		if ($hours === null || $hours === '') {
			$this->errors[] = 'HoursRequired';
			return;
		}

		if (!is_numeric($hours)) {
			$this->errors[] = 'HoursNotNumeric';
			return;
		}

		$hoursFloat = floatval($hours);

		if ($hoursFloat < 0) {
			$this->errors[] = 'HoursNegative';
		}
	}

	/**
	 * Validate user ID
	 *
	 * @param array $data Overtime data
	 * @return void
	 */
	protected function validateUser(array $data): void
	{
		$fkUser = isset($data['fk_user']) ? $data['fk_user'] : null;

		if (empty($fkUser)) {
			$this->errors[] = 'UserRequired';
			return;
		}

		if (!is_numeric($fkUser) || intval($fkUser) <= 0) {
			$this->errors[] = 'UserInvalid';
		}
	}

	/**
	 * Validate reason
	 *
	 * @param array $data Overtime data
	 * @return void
	 */
	protected function validateReason(array $data): void
	{
		$reason = isset($data['reason']) ? $data['reason'] : null;

		if (empty($reason) || trim($reason) === '') {
			$this->errors[] = 'ReasonRequired';
		}
	}

	/**
	 * Parse a date value to timestamp
	 *
	 * @param mixed $date Date value (timestamp, string, or DateTime-like)
	 * @return int|false Unix timestamp or false on failure
	 */
	public function parseDate($date)
	{
		if ($date === null || $date === '') {
			return false;
		}

		// Already a timestamp
		if (is_int($date) || (is_numeric($date) && strlen((string)$date) >= 8)) {
			return intval($date);
		}

		// String date
		if (is_string($date)) {
			$timestamp = strtotime($date);
			if ($timestamp !== false) {
				return $timestamp;
			}
		}

		return false;
	}

	/**
	 * Calculate duration in days between two dates
	 *
	 * @param mixed $dateStart Start date (timestamp or string)
	 * @param mixed $dateEnd End date (timestamp or string)
	 * @return int|false Number of days or false on error
	 */
	public function calculateDurationDays($dateStart, $dateEnd)
	{
		$startTimestamp = $this->parseDate($dateStart);
		$endTimestamp = $this->parseDate($dateEnd);

		if ($startTimestamp === false || $endTimestamp === false) {
			return false;
		}

		$diff = $endTimestamp - $startTimestamp;
		return intval(ceil($diff / 86400)) + 1; // +1 to include both start and end day
	}

	/**
	 * Check if validation passed without errors
	 *
	 * @return bool True if no errors
	 */
	public function isValid(): bool
	{
		return empty($this->errors);
	}

	/**
	 * Get all error messages
	 *
	 * @return array Array of error message keys
	 */
	public function getErrors(): array
	{
		return $this->errors;
	}
}
