<?php
/**
 * Calculator for hours per day based on weekly hours and days per week
 * Standalone class with no Dolibarr dependency for easy unit testing
 */

class HoursPerDayCalculator
{
	/**
	 * Calculate hours per day from weekly hours and days per week
	 *
	 * @param float $weeklyHours Weekly hours worked
	 * @param float $daysPerWeek Days worked per week
	 * @return float|false Hours per day or false if invalid input
	 */
	public function calculate($weeklyHours, $daysPerWeek)
	{
		if (!$this->isValidWeeklyHours($weeklyHours)) {
			return false;
		}

		if (!$this->isValidDaysPerWeek($daysPerWeek)) {
			return false;
		}

		return (float) $weeklyHours / (float) $daysPerWeek;
	}

	/**
	 * Validate weekly hours value
	 *
	 * @param mixed $weeklyHours
	 * @return bool
	 */
	public function isValidWeeklyHours($weeklyHours)
	{
		if (!is_numeric($weeklyHours)) {
			return false;
		}
		if ($weeklyHours <= 0) {
			return false;
		}
		if ($weeklyHours > 168) { // Max 24h * 7 days
			return false;
		}
		return true;
	}

	/**
	 * Validate days per week value
	 *
	 * @param mixed $daysPerWeek
	 * @return bool
	 */
	public function isValidDaysPerWeek($daysPerWeek)
	{
		if (!is_numeric($daysPerWeek)) {
			return false;
		}
		if ($daysPerWeek <= 0) {
			return false;
		}
		if ($daysPerWeek > 7) {
			return false;
		}
		return true;
	}

	/**
	 * Calculate how many full days of leave can be obtained from overtime hours
	 *
	 * @param float $overtimeHours Total overtime hours accumulated
	 * @param float $hoursPerDay Hours per working day
	 * @return int Number of full days
	 */
	public function calculateDaysFromOvertime($overtimeHours, $hoursPerDay)
	{
		if (!is_numeric($overtimeHours) || $overtimeHours < 0) {
			return 0;
		}
		if (!is_numeric($hoursPerDay) || $hoursPerDay <= 0) {
			return 0;
		}

		return (int) floor($overtimeHours / $hoursPerDay);
	}

	/**
	 * Calculate remaining hours after converting to days
	 *
	 * @param float $overtimeHours Total overtime hours accumulated
	 * @param float $hoursPerDay Hours per working day
	 * @return float Remaining hours
	 */
	public function calculateRemainingHours($overtimeHours, $hoursPerDay)
	{
		if (!is_numeric($overtimeHours) || $overtimeHours < 0) {
			return 0;
		}
		if (!is_numeric($hoursPerDay) || $hoursPerDay <= 0) {
			return $overtimeHours;
		}

		$days = $this->calculateDaysFromOvertime($overtimeHours, $hoursPerDay);
		return $overtimeHours - ($days * $hoursPerDay);
	}
}
