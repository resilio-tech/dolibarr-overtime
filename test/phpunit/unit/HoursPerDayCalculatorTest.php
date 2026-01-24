<?php
/**
 * Unit tests for HoursPerDayCalculator
 * No Dolibarr dependency required
 */

use PHPUnit\Framework\TestCase;

require_once dirname(__FILE__).'/../../../class/HoursPerDayCalculator.class.php';

class HoursPerDayCalculatorTest extends TestCase
{
	/**
	 * @var HoursPerDayCalculator
	 */
	private $calculator;

	protected function setUp(): void
	{
		$this->calculator = new HoursPerDayCalculator();
	}

	// ========================================
	// Tests for calculate()
	// ========================================

	public function testCalculateFullTime35h()
	{
		// 35h/week, 5 days = 7h/day
		$result = $this->calculator->calculate(35, 5);
		$this->assertEquals(7, $result);
	}

	public function testCalculateFullTime40h()
	{
		// 40h/week, 5 days = 8h/day
		$result = $this->calculator->calculate(40, 5);
		$this->assertEquals(8, $result);
	}

	public function testCalculatePartTime27h3days()
	{
		// 27h/week, 3 days = 9h/day
		$result = $this->calculator->calculate(27, 3);
		$this->assertEquals(9, $result);
	}

	public function testCalculatePartTime20h4days()
	{
		// 20h/week, 4 days = 5h/day
		$result = $this->calculator->calculate(20, 4);
		$this->assertEquals(5, $result);
	}

	public function testCalculateWithDecimalResult()
	{
		// 35h/week, 4 days = 8.75h/day
		$result = $this->calculator->calculate(35, 4);
		$this->assertEquals(8.75, $result);
	}

	public function testCalculateWithDecimalInputs()
	{
		// 37.5h/week, 5 days = 7.5h/day
		$result = $this->calculator->calculate(37.5, 5);
		$this->assertEquals(7.5, $result);
	}

	public function testCalculateZeroWeeklyHours()
	{
		$result = $this->calculator->calculate(0, 5);
		$this->assertFalse($result);
	}

	public function testCalculateNegativeWeeklyHours()
	{
		$result = $this->calculator->calculate(-35, 5);
		$this->assertFalse($result);
	}

	public function testCalculateZeroDaysPerWeek()
	{
		$result = $this->calculator->calculate(35, 0);
		$this->assertFalse($result);
	}

	public function testCalculateNegativeDaysPerWeek()
	{
		$result = $this->calculator->calculate(35, -1);
		$this->assertFalse($result);
	}

	public function testCalculateMoreThan7Days()
	{
		$result = $this->calculator->calculate(35, 8);
		$this->assertFalse($result);
	}

	public function testCalculateMoreThan168Hours()
	{
		$result = $this->calculator->calculate(200, 5);
		$this->assertFalse($result);
	}

	public function testCalculateNonNumericHours()
	{
		$result = $this->calculator->calculate('abc', 5);
		$this->assertFalse($result);
	}

	public function testCalculateNonNumericDays()
	{
		$result = $this->calculator->calculate(35, 'abc');
		$this->assertFalse($result);
	}

	// ========================================
	// Tests for isValidWeeklyHours()
	// ========================================

	public function testIsValidWeeklyHoursValid()
	{
		$this->assertTrue($this->calculator->isValidWeeklyHours(35));
		$this->assertTrue($this->calculator->isValidWeeklyHours(40));
		$this->assertTrue($this->calculator->isValidWeeklyHours(20));
		$this->assertTrue($this->calculator->isValidWeeklyHours(0.5));
		$this->assertTrue($this->calculator->isValidWeeklyHours(168));
	}

	public function testIsValidWeeklyHoursInvalid()
	{
		$this->assertFalse($this->calculator->isValidWeeklyHours(0));
		$this->assertFalse($this->calculator->isValidWeeklyHours(-1));
		$this->assertFalse($this->calculator->isValidWeeklyHours(169));
		$this->assertFalse($this->calculator->isValidWeeklyHours('abc'));
		$this->assertFalse($this->calculator->isValidWeeklyHours(null));
	}

	// ========================================
	// Tests for isValidDaysPerWeek()
	// ========================================

	public function testIsValidDaysPerWeekValid()
	{
		$this->assertTrue($this->calculator->isValidDaysPerWeek(1));
		$this->assertTrue($this->calculator->isValidDaysPerWeek(3));
		$this->assertTrue($this->calculator->isValidDaysPerWeek(5));
		$this->assertTrue($this->calculator->isValidDaysPerWeek(7));
		$this->assertTrue($this->calculator->isValidDaysPerWeek(4.5));
	}

	public function testIsValidDaysPerWeekInvalid()
	{
		$this->assertFalse($this->calculator->isValidDaysPerWeek(0));
		$this->assertFalse($this->calculator->isValidDaysPerWeek(-1));
		$this->assertFalse($this->calculator->isValidDaysPerWeek(8));
		$this->assertFalse($this->calculator->isValidDaysPerWeek('abc'));
		$this->assertFalse($this->calculator->isValidDaysPerWeek(null));
	}

	// ========================================
	// Tests for calculateDaysFromOvertime()
	// ========================================

	public function testCalculateDaysFromOvertimeExactDays()
	{
		// 16h overtime, 8h/day = 2 days
		$result = $this->calculator->calculateDaysFromOvertime(16, 8);
		$this->assertEquals(2, $result);
	}

	public function testCalculateDaysFromOvertimeWithRemainder()
	{
		// 18h overtime, 8h/day = 2 days (2h remaining)
		$result = $this->calculator->calculateDaysFromOvertime(18, 8);
		$this->assertEquals(2, $result);
	}

	public function testCalculateDaysFromOvertimeLessThanOneDay()
	{
		// 5h overtime, 8h/day = 0 days
		$result = $this->calculator->calculateDaysFromOvertime(5, 8);
		$this->assertEquals(0, $result);
	}

	public function testCalculateDaysFromOvertimePartTime()
	{
		// 18h overtime, 9h/day (part-time) = 2 days
		$result = $this->calculator->calculateDaysFromOvertime(18, 9);
		$this->assertEquals(2, $result);
	}

	public function testCalculateDaysFromOvertimeZeroHours()
	{
		$result = $this->calculator->calculateDaysFromOvertime(0, 8);
		$this->assertEquals(0, $result);
	}

	public function testCalculateDaysFromOvertimeNegativeHours()
	{
		$result = $this->calculator->calculateDaysFromOvertime(-5, 8);
		$this->assertEquals(0, $result);
	}

	public function testCalculateDaysFromOvertimeZeroHoursPerDay()
	{
		$result = $this->calculator->calculateDaysFromOvertime(16, 0);
		$this->assertEquals(0, $result);
	}

	// ========================================
	// Tests for calculateRemainingHours()
	// ========================================

	public function testCalculateRemainingHoursExactDays()
	{
		// 16h overtime, 8h/day = 0h remaining
		$result = $this->calculator->calculateRemainingHours(16, 8);
		$this->assertEquals(0, $result);
	}

	public function testCalculateRemainingHoursWithRemainder()
	{
		// 18h overtime, 8h/day = 2h remaining
		$result = $this->calculator->calculateRemainingHours(18, 8);
		$this->assertEquals(2, $result);
	}

	public function testCalculateRemainingHoursLessThanOneDay()
	{
		// 5h overtime, 8h/day = 5h remaining
		$result = $this->calculator->calculateRemainingHours(5, 8);
		$this->assertEquals(5, $result);
	}

	public function testCalculateRemainingHoursPartTime()
	{
		// 20h overtime, 9h/day = 2h remaining (2 days = 18h)
		$result = $this->calculator->calculateRemainingHours(20, 9);
		$this->assertEquals(2, $result);
	}

	public function testCalculateRemainingHoursZeroHoursPerDay()
	{
		// Invalid hours per day, return original overtime
		$result = $this->calculator->calculateRemainingHours(16, 0);
		$this->assertEquals(16, $result);
	}

	// ========================================
	// Real-world scenarios
	// ========================================

	public function testScenarioFullTimeEmployee()
	{
		// Employee: 35h/week, 5 days/week = 7h/day
		// Overtime: 14h accumulated
		// Expected: 2 days off, 0h remaining

		$hoursPerDay = $this->calculator->calculate(35, 5);
		$this->assertEquals(7, $hoursPerDay);

		$days = $this->calculator->calculateDaysFromOvertime(14, $hoursPerDay);
		$this->assertEquals(2, $days);

		$remaining = $this->calculator->calculateRemainingHours(14, $hoursPerDay);
		$this->assertEquals(0, $remaining);
	}

	public function testScenarioPartTimeEmployee()
	{
		// Employee: 27h/week, 3 days/week = 9h/day
		// Overtime: 20h accumulated
		// Expected: 2 days off, 2h remaining

		$hoursPerDay = $this->calculator->calculate(27, 3);
		$this->assertEquals(9, $hoursPerDay);

		$days = $this->calculator->calculateDaysFromOvertime(20, $hoursPerDay);
		$this->assertEquals(2, $days);

		$remaining = $this->calculator->calculateRemainingHours(20, $hoursPerDay);
		$this->assertEquals(2, $remaining);
	}

	public function testScenario4DayWeekEmployee()
	{
		// Employee: 32h/week, 4 days/week = 8h/day
		// Overtime: 25h accumulated
		// Expected: 3 days off, 1h remaining

		$hoursPerDay = $this->calculator->calculate(32, 4);
		$this->assertEquals(8, $hoursPerDay);

		$days = $this->calculator->calculateDaysFromOvertime(25, $hoursPerDay);
		$this->assertEquals(3, $days);

		$remaining = $this->calculator->calculateRemainingHours(25, $hoursPerDay);
		$this->assertEquals(1, $remaining);
	}
}
