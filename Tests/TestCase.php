<?php

class Suin_FTPClient_Test_TestCase extends PHPUnit_Framework_TestCase
{
	protected static $warningEnabledOrig = null;
	protected static $errorReportingOrig = null;
	protected static $isDisabledErrorReporting = false;

	/**
	 * Tears down the fixture, for example, close a network connection.
	 * This method is called after a test is executed.
	 */
	final protected function tearDown()
	{
		if ( static::$isDisabledErrorReporting === true )
		{
			$this->enableErrorReporting();
		}

		$this->_tearDown();
	}

	/**
	 * Tears down the fixture, for example, close a network connection.
	 * This method is called after a test is executed.
	 */
	protected function _tearDown()
	{
	}

	/**
	 * Disable error reporting.
	 */
	public function disableErrorReporting()
	{
		/**
		 * refs php - test the return value of a method that triggers an error with PHPUnit - Stack Overflow
		 * http://stackoverflow.com/questions/1225776/test-the-return-value-of-a-method-that-triggers-an-error-with-phpunit
		 */
		static::$warningEnabledOrig = PHPUnit_Framework_Error_Warning::$enabled;
		static::$errorReportingOrig = error_reporting();
		PHPUnit_Framework_Error_Warning::$enabled = false;
		error_reporting(0);
		static::$isDisabledErrorReporting = true;
	}

	/**
	 * Enable error reporting.
	 */
	public function enableErrorReporting()
	{
		PHPUnit_Framework_Error_Warning::$enabled = static::$warningEnabledOrig;
		error_reporting(static::$errorReportingOrig);
		static::$isDisabledErrorReporting = false;
	}
}
