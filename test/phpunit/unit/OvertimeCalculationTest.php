<?php
/**
 * Unit tests for Overtime calculation scenarios
 * Tests integration between HoursPerDayCalculator and OvertimeValidator
 * No Dolibarr dependency required
 *
 * Run with: phpunit htdocs/custom/overtime/test/phpunit/unit/OvertimeCalculationTest.php
 */

use PHPUnit\Framework\TestCase;

require_once dirname(__FILE__).'/../../../class/HoursPerDayCalculator.class.php';
require_once dirname(__FILE__).'/../../../class/OvertimeValidator.class.php';

class OvertimeCalculationTest extends TestCase
{
	/**
	 * @var HoursPerDayCalculator
	 */
	private $calculator;

	/**
	 * @var OvertimeValidator
	 */
	private $validator;

	protected function setUp(): void
	{
		$this->calculator = new HoursPerDayCalculator();
		$this->validator = new OvertimeValidator();
	}

	// ========================================
	// Integration tests: Complete overtime workflow
	// ========================================

	public function testCompleteOvertimeWorkflowFullTime()
	{
		// Scenario: Full-time employee (35h/week, 5 days) with overtime validation and calculation

		// Step 1: Calculate hours per day
		$hoursPerDay = $this->calculator->calculate(35, 5);
		$this->assertEquals(7, $hoursPerDay);

		// Step 2: Validate overtime data
		$overtimeData = array(
			'date_start' => '2024-01-01',
			'date_end' => '2024-01-07',
			'hours' => 21, // 3 days worth of overtime
			'fk_user' => 1,
			'reason' => 'Year-end project completion'
		);

		$isValid = $this->validator->validate($overtimeData);
		$this->assertTrue($isValid);

		// Step 3: Calculate days from overtime
		$days = $this->calculator->calculateDaysFromOvertime(21, $hoursPerDay);
		$this->assertEquals(3, $days);

		// Step 4: Calculate remaining hours
		$remaining = $this->calculator->calculateRemainingHours(21, $hoursPerDay);
		$this->assertEquals(0, $remaining);
	}

	public function testCompleteOvertimeWorkflowPartTime()
	{
		// Scenario: Part-time employee (24h/week, 3 days) with overtime validation and calculation

		// Step 1: Calculate hours per day
		$hoursPerDay = $this->calculator->calculate(24, 3);
		$this->assertEquals(8, $hoursPerDay);

		// Step 2: Validate overtime data
		$overtimeData = array(
			'date_start' => '2024-02-01',
			'date_end' => '2024-02-15',
			'hours' => 20, // 2.5 days worth of overtime
			'fk_user' => 2,
			'reason' => 'Client emergency support'
		);

		$isValid = $this->validator->validate($overtimeData);
		$this->assertTrue($isValid);

		// Step 3: Calculate days from overtime
		$days = $this->calculator->calculateDaysFromOvertime(20, $hoursPerDay);
		$this->assertEquals(2, $days);

		// Step 4: Calculate remaining hours
		$remaining = $this->calculator->calculateRemainingHours(20, $hoursPerDay);
		$this->assertEquals(4, $remaining);
	}

	// ========================================
	// Edge cases: Calculation boundaries
	// ========================================

	public function testMinimalOvertimeCalculation()
	{
		// Minimum viable overtime: 1 hour
		$hoursPerDay = $this->calculator->calculate(35, 5);

		$days = $this->calculator->calculateDaysFromOvertime(1, $hoursPerDay);
		$remaining = $this->calculator->calculateRemainingHours(1, $hoursPerDay);

		$this->assertEquals(0, $days);
		$this->assertEquals(1, $remaining);
	}

	public function testExactDayOvertimeCalculation()
	{
		// Exactly one day of overtime (7 hours for 35h/5day employee)
		$hoursPerDay = $this->calculator->calculate(35, 5);

		$days = $this->calculator->calculateDaysFromOvertime(7, $hoursPerDay);
		$remaining = $this->calculator->calculateRemainingHours(7, $hoursPerDay);

		$this->assertEquals(1, $days);
		$this->assertEquals(0, $remaining);
	}

	public function testLargeOvertimeAccumulation()
	{
		// Large overtime accumulation (200 hours for 8h/day employee)
		$hoursPerDay = 8;

		$days = $this->calculator->calculateDaysFromOvertime(200, $hoursPerDay);
		$remaining = $this->calculator->calculateRemainingHours(200, $hoursPerDay);

		$this->assertEquals(25, $days);
		$this->assertEquals(0, $remaining);
	}

	public function testOvertimeWithDecimalHours()
	{
		// Decimal overtime hours (17.5 hours for 7h/day employee)
		$hoursPerDay = 7;

		$days = $this->calculator->calculateDaysFromOvertime(17.5, $hoursPerDay);
		$remaining = $this->calculator->calculateRemainingHours(17.5, $hoursPerDay);

		$this->assertEquals(2, $days);
		$this->assertEquals(3.5, $remaining);
	}

	// ========================================
	// Edge cases: Validation boundaries
	// ========================================

	public function testValidationWithTimestampDates()
	{
		$data = array(
			'date_start' => strtotime('2024-01-01'),
			'date_end' => strtotime('2024-01-31'),
			'hours' => 40,
			'fk_user' => 1,
			'reason' => 'Monthly overtime'
		);

		$result = $this->validator->validate($data);

		$this->assertTrue($result);
		$this->assertTrue($this->validator->isValid());
	}

	public function testValidationWithSameDayPeriod()
	{
		$data = array(
			'date_start' => '2024-01-15',
			'date_end' => '2024-01-15',
			'hours' => 2,
			'fk_user' => 1,
			'reason' => 'Single day overtime'
		);

		$result = $this->validator->validate($data);

		$this->assertTrue($result);
	}

	public function testValidationRejectsInvalidDateRange()
	{
		$data = array(
			'date_start' => '2024-01-31',
			'date_end' => '2024-01-01', // End before start
			'hours' => 8,
			'fk_user' => 1,
			'reason' => 'Invalid date range'
		);

		$result = $this->validator->validate($data);

		$this->assertFalse($result);
		$this->assertContains('DateEndBeforeDateStart', $this->validator->getErrors());
	}

	// ========================================
	// Duration calculation tests
	// ========================================

	public function testDurationCalculationOneDayPeriod()
	{
		$days = $this->validator->calculateDurationDays('2024-01-15', '2024-01-15');
		$this->assertEquals(1, $days);
	}

	public function testDurationCalculationOneWeekPeriod()
	{
		$days = $this->validator->calculateDurationDays('2024-01-01', '2024-01-07');
		$this->assertEquals(7, $days);
	}

	public function testDurationCalculationOneMonthPeriod()
	{
		$days = $this->validator->calculateDurationDays('2024-01-01', '2024-01-31');
		$this->assertEquals(31, $days);
	}

	public function testDurationCalculationCrossMonthPeriod()
	{
		$days = $this->validator->calculateDurationDays('2024-01-15', '2024-02-14');
		$this->assertEquals(31, $days);
	}

	public function testDurationCalculationWithTimestamps()
	{
		$startTimestamp = strtotime('2024-01-01');
		$endTimestamp = strtotime('2024-01-07');

		$days = $this->validator->calculateDurationDays($startTimestamp, $endTimestamp);
		$this->assertEquals(7, $days);
	}

	// ========================================
	// Complex scenarios
	// ========================================

	public function testScenarioFlexibleWorkingHours()
	{
		// Scenario: Employee with flexible schedule (37.5h/week, 4.5 days)
		$hoursPerDay = $this->calculator->calculate(37.5, 4.5);
		$this->assertEqualsWithDelta(8.333, $hoursPerDay, 0.001);

		// Overtime of 25 hours
		$days = $this->calculator->calculateDaysFromOvertime(25, $hoursPerDay);
		$remaining = $this->calculator->calculateRemainingHours(25, $hoursPerDay);

		// 25 / 8.333 = 3 full days
		$this->assertEquals(3, $days);
		// 25 - (3 * 8.333) = approximately 0.001 (rounding)
		$this->assertEqualsWithDelta(0.001, $remaining, 0.01);
	}

	public function testScenarioMultipleOvertimeEntries()
	{
		// Scenario: Multiple overtime entries for different users
		$entries = array(
			array(
				'date_start' => '2024-01-01',
				'date_end' => '2024-01-07',
				'hours' => 14,
				'fk_user' => 1,
				'reason' => 'Project Alpha'
			),
			array(
				'date_start' => '2024-01-08',
				'date_end' => '2024-01-14',
				'hours' => 21,
				'fk_user' => 1,
				'reason' => 'Project Beta'
			),
			array(
				'date_start' => '2024-01-01',
				'date_end' => '2024-01-14',
				'hours' => 8,
				'fk_user' => 2,
				'reason' => 'Support work'
			)
		);

		$validEntries = 0;
		$totalHoursUser1 = 0;
		$totalHoursUser2 = 0;

		foreach ($entries as $entry) {
			if ($this->validator->validate($entry)) {
				$validEntries++;
				if ($entry['fk_user'] == 1) {
					$totalHoursUser1 += $entry['hours'];
				} else {
					$totalHoursUser2 += $entry['hours'];
				}
			}
		}

		$this->assertEquals(3, $validEntries);
		$this->assertEquals(35, $totalHoursUser1);
		$this->assertEquals(8, $totalHoursUser2);

		// Calculate days for user 1 (assuming 7h/day)
		$hoursPerDay = 7;
		$daysUser1 = $this->calculator->calculateDaysFromOvertime($totalHoursUser1, $hoursPerDay);
		$this->assertEquals(5, $daysUser1);
	}

	public function testScenarioOvertimeConversionWithRounding()
	{
		// Scenario: Overtime conversion with various rounding situations
		$hoursPerDay = $this->calculator->calculate(40, 5); // 8 hours/day

		$testCases = array(
			array('hours' => 7.9, 'expectedDays' => 0, 'expectedRemaining' => 7.9),
			array('hours' => 8.0, 'expectedDays' => 1, 'expectedRemaining' => 0),
			array('hours' => 8.1, 'expectedDays' => 1, 'expectedRemaining' => 0.1),
			array('hours' => 15.9, 'expectedDays' => 1, 'expectedRemaining' => 7.9),
			array('hours' => 16.0, 'expectedDays' => 2, 'expectedRemaining' => 0),
		);

		foreach ($testCases as $case) {
			$days = $this->calculator->calculateDaysFromOvertime($case['hours'], $hoursPerDay);
			$remaining = $this->calculator->calculateRemainingHours($case['hours'], $hoursPerDay);

			$this->assertEquals($case['expectedDays'], $days, "Days calculation failed for {$case['hours']} hours");
			$this->assertEqualsWithDelta($case['expectedRemaining'], $remaining, 0.001, "Remaining calculation failed for {$case['hours']} hours");
		}
	}

	// ========================================
	// Error handling tests
	// ========================================

	public function testErrorAccumulationInValidator()
	{
		// Data with multiple errors
		$data = array(
			// Missing date_start
			// Missing date_end
			'hours' => -5, // Negative hours
			// Missing fk_user
			'reason' => '' // Empty reason
		);

		$result = $this->validator->validate($data);

		$this->assertFalse($result);
		$errors = $this->validator->getErrors();

		// Should have all expected errors
		$this->assertContains('DateStartRequired', $errors);
		$this->assertContains('DateEndRequired', $errors);
		$this->assertContains('HoursNegative', $errors);
		$this->assertContains('UserRequired', $errors);
		$this->assertContains('ReasonRequired', $errors);
	}

	public function testCalculatorReturnsZeroForInvalidInput()
	{
		// Calculator should handle invalid inputs gracefully
		$this->assertEquals(0, $this->calculator->calculateDaysFromOvertime('invalid', 8));
		$this->assertEquals(0, $this->calculator->calculateDaysFromOvertime(10, 'invalid'));
		$this->assertEquals(0, $this->calculator->calculateDaysFromOvertime(-10, 8));
	}

	// ========================================
	// Reset/reuse tests
	// ========================================

	public function testValidatorCanBeReused()
	{
		// First validation - invalid
		$invalidData = array(
			'date_start' => '',
			'date_end' => '',
			'hours' => '',
			'fk_user' => '',
			'reason' => ''
		);

		$this->validator->validate($invalidData);
		$this->assertFalse($this->validator->isValid());
		$this->assertNotEmpty($this->validator->getErrors());

		// Second validation - valid (should reset errors)
		$validData = array(
			'date_start' => '2024-01-01',
			'date_end' => '2024-01-07',
			'hours' => 8,
			'fk_user' => 1,
			'reason' => 'Test reason'
		);

		$this->validator->validate($validData);
		$this->assertTrue($this->validator->isValid());
		$this->assertEmpty($this->validator->getErrors());
	}
}
