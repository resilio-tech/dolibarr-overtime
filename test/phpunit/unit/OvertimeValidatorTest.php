<?php
/**
 * Standalone unit tests for OvertimeValidator
 * No Dolibarr dependency required
 *
 * Run with: phpunit htdocs/custom/overtime/test/phpunit/unit/OvertimeValidatorTest.php
 */

use PHPUnit\Framework\TestCase;

require_once dirname(__FILE__).'/../../../class/OvertimeValidator.class.php';

class OvertimeValidatorTest extends TestCase
{
	/**
	 * @var OvertimeValidator
	 */
	private $validator;

	protected function setUp(): void
	{
		$this->validator = new OvertimeValidator();
	}

	// ========================================
	// Tests for validate() - Valid data
	// ========================================

	public function testValidateValidData()
	{
		$data = $this->getValidData();

		$result = $this->validator->validate($data);

		$this->assertTrue($result);
		$this->assertTrue($this->validator->isValid());
		$this->assertEmpty($this->validator->getErrors());
	}

	public function testValidateValidDataWithStringDates()
	{
		$data = array(
			'date_start' => '2024-01-01',
			'date_end' => '2024-01-15',
			'hours' => 8.5,
			'fk_user' => 1,
			'reason' => 'Extra project work'
		);

		$result = $this->validator->validate($data);

		$this->assertTrue($result);
	}

	public function testValidateValidDataWithZeroHours()
	{
		$data = $this->getValidData();
		$data['hours'] = 0;

		$result = $this->validator->validate($data);

		$this->assertTrue($result);
	}

	// ========================================
	// Tests for date validation
	// ========================================

	public function testValidateMissingDateStart()
	{
		$data = $this->getValidData();
		unset($data['date_start']);

		$result = $this->validator->validate($data);

		$this->assertFalse($result);
		$this->assertContains('DateStartRequired', $this->validator->getErrors());
	}

	public function testValidateEmptyDateStart()
	{
		$data = $this->getValidData();
		$data['date_start'] = '';

		$result = $this->validator->validate($data);

		$this->assertFalse($result);
		$this->assertContains('DateStartRequired', $this->validator->getErrors());
	}

	public function testValidateMissingDateEnd()
	{
		$data = $this->getValidData();
		unset($data['date_end']);

		$result = $this->validator->validate($data);

		$this->assertFalse($result);
		$this->assertContains('DateEndRequired', $this->validator->getErrors());
	}

	public function testValidateDateEndBeforeDateStart()
	{
		$data = $this->getValidData();
		$data['date_start'] = '2024-01-15';
		$data['date_end'] = '2024-01-01';

		$result = $this->validator->validate($data);

		$this->assertFalse($result);
		$this->assertContains('DateEndBeforeDateStart', $this->validator->getErrors());
	}

	public function testValidateSameDateStartAndEnd()
	{
		$data = $this->getValidData();
		$data['date_start'] = '2024-01-15';
		$data['date_end'] = '2024-01-15';

		$result = $this->validator->validate($data);

		$this->assertTrue($result);
	}

	// ========================================
	// Tests for hours validation
	// ========================================

	public function testValidateMissingHours()
	{
		$data = $this->getValidData();
		unset($data['hours']);

		$result = $this->validator->validate($data);

		$this->assertFalse($result);
		$this->assertContains('HoursRequired', $this->validator->getErrors());
	}

	public function testValidateEmptyHours()
	{
		$data = $this->getValidData();
		$data['hours'] = '';

		$result = $this->validator->validate($data);

		$this->assertFalse($result);
		$this->assertContains('HoursRequired', $this->validator->getErrors());
	}

	public function testValidateNonNumericHours()
	{
		$data = $this->getValidData();
		$data['hours'] = 'abc';

		$result = $this->validator->validate($data);

		$this->assertFalse($result);
		$this->assertContains('HoursNotNumeric', $this->validator->getErrors());
	}

	public function testValidateNegativeHours()
	{
		$data = $this->getValidData();
		$data['hours'] = -5;

		$result = $this->validator->validate($data);

		$this->assertFalse($result);
		$this->assertContains('HoursNegative', $this->validator->getErrors());
	}

	public function testValidateLargeHours()
	{
		// Large hours values should be valid (e.g., a month of overtime)
		$data = $this->getValidData();
		$data['hours'] = 100;

		$result = $this->validator->validate($data);

		$this->assertTrue($result);
	}

	public function testValidateDecimalHours()
	{
		$data = $this->getValidData();
		$data['hours'] = 8.5;

		$result = $this->validator->validate($data);

		$this->assertTrue($result);
	}

	public function testValidateStringNumericHours()
	{
		$data = $this->getValidData();
		$data['hours'] = '8.5';

		$result = $this->validator->validate($data);

		$this->assertTrue($result);
	}

	// ========================================
	// Tests for user validation
	// ========================================

	public function testValidateMissingUser()
	{
		$data = $this->getValidData();
		unset($data['fk_user']);

		$result = $this->validator->validate($data);

		$this->assertFalse($result);
		$this->assertContains('UserRequired', $this->validator->getErrors());
	}

	public function testValidateEmptyUser()
	{
		$data = $this->getValidData();
		$data['fk_user'] = '';

		$result = $this->validator->validate($data);

		$this->assertFalse($result);
		$this->assertContains('UserRequired', $this->validator->getErrors());
	}

	public function testValidateZeroUser()
	{
		$data = $this->getValidData();
		$data['fk_user'] = 0;

		$result = $this->validator->validate($data);

		$this->assertFalse($result);
		$this->assertContains('UserRequired', $this->validator->getErrors());
	}

	public function testValidateNegativeUser()
	{
		$data = $this->getValidData();
		$data['fk_user'] = -1;

		$result = $this->validator->validate($data);

		$this->assertFalse($result);
		$this->assertContains('UserInvalid', $this->validator->getErrors());
	}

	public function testValidateNonNumericUser()
	{
		$data = $this->getValidData();
		$data['fk_user'] = 'abc';

		$result = $this->validator->validate($data);

		$this->assertFalse($result);
		$this->assertContains('UserInvalid', $this->validator->getErrors());
	}

	// ========================================
	// Tests for reason validation
	// ========================================

	public function testValidateMissingReason()
	{
		$data = $this->getValidData();
		unset($data['reason']);

		$result = $this->validator->validate($data);

		$this->assertFalse($result);
		$this->assertContains('ReasonRequired', $this->validator->getErrors());
	}

	public function testValidateEmptyReason()
	{
		$data = $this->getValidData();
		$data['reason'] = '';

		$result = $this->validator->validate($data);

		$this->assertFalse($result);
		$this->assertContains('ReasonRequired', $this->validator->getErrors());
	}

	public function testValidateWhitespaceOnlyReason()
	{
		$data = $this->getValidData();
		$data['reason'] = '   ';

		$result = $this->validator->validate($data);

		$this->assertFalse($result);
		$this->assertContains('ReasonRequired', $this->validator->getErrors());
	}

	// ========================================
	// Tests for parseDate()
	// ========================================

	public function testParseDateWithTimestamp()
	{
		$timestamp = 1704067200; // 2024-01-01 00:00:00

		$result = $this->validator->parseDate($timestamp);

		$this->assertEquals($timestamp, $result);
	}

	public function testParseDateWithString()
	{
		$result = $this->validator->parseDate('2024-01-15');

		$this->assertIsInt($result);
		$this->assertEquals('2024-01-15', date('Y-m-d', $result));
	}

	public function testParseDateWithNull()
	{
		$this->assertFalse($this->validator->parseDate(null));
	}

	public function testParseDateWithEmptyString()
	{
		$this->assertFalse($this->validator->parseDate(''));
	}

	// ========================================
	// Tests for calculateDurationDays()
	// ========================================

	public function testCalculateDurationDaysSameDay()
	{
		$result = $this->validator->calculateDurationDays('2024-01-15', '2024-01-15');

		$this->assertEquals(1, $result);
	}

	public function testCalculateDurationDaysTwoDays()
	{
		$result = $this->validator->calculateDurationDays('2024-01-15', '2024-01-16');

		$this->assertEquals(2, $result);
	}

	public function testCalculateDurationDaysOneWeek()
	{
		$result = $this->validator->calculateDurationDays('2024-01-01', '2024-01-07');

		$this->assertEquals(7, $result);
	}

	public function testCalculateDurationDaysOneMonth()
	{
		$result = $this->validator->calculateDurationDays('2024-01-01', '2024-01-31');

		$this->assertEquals(31, $result);
	}

	public function testCalculateDurationDaysInvalidStart()
	{
		$result = $this->validator->calculateDurationDays('', '2024-01-15');

		$this->assertFalse($result);
	}

	public function testCalculateDurationDaysInvalidEnd()
	{
		$result = $this->validator->calculateDurationDays('2024-01-15', '');

		$this->assertFalse($result);
	}

	// ========================================
	// Tests for multiple errors
	// ========================================

	public function testValidateMultipleErrors()
	{
		$data = array(); // All fields missing

		$result = $this->validator->validate($data);

		$this->assertFalse($result);
		$errors = $this->validator->getErrors();
		$this->assertContains('DateStartRequired', $errors);
		$this->assertContains('DateEndRequired', $errors);
		$this->assertContains('HoursRequired', $errors);
		$this->assertContains('UserRequired', $errors);
		$this->assertContains('ReasonRequired', $errors);
	}

	// ========================================
	// Helper methods
	// ========================================

	private function getValidData(): array
	{
		return array(
			'date_start' => strtotime('2024-01-01'),
			'date_end' => strtotime('2024-01-15'),
			'hours' => 8,
			'fk_user' => 1,
			'reason' => 'Extra project work for client deadline'
		);
	}
}
