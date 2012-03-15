<?php

class Suin_FTPClient_FTPClientTest extends Suin_FTPClient_Test_TestCase
{
	/**
	 * Return new FTPClent object.
	 * @param bool $login
	 * @return Suin_FTPClient_FTPClient
	 * @throws RuntimeException
	 * @throws RuntimeException
	 */
	protected function getNewFTPClient($login = true)
	{
		$client = new Suin_FTPClient_FTPClient(FTP_CLIENT_TEST_FTP_HOST, FTP_CLIENT_TEST_FTP_PORT);

		if ( $login === false )
		{
			return $client;
		}

		$observer = new Suin_FTPClient_Test_FTPMessageObserver();
		$client->setObserver($observer);

		$success = $client->login(FTP_CLIENT_TEST_FTP_USER, FTP_CLIENT_TEST_FTP_PASS);

		if ( $success === false )
		{
			throw new RuntimeException("Failed to login to the FTP Server.\n".$observer->getMessagesAsString());
		}

		$success = $client->changeDirectory(FTP_CLIENT_TEST_REMOTE_SANDBOX_DIR);

		if ( $success === false )
		{
			throw new RuntimeException("Failed to change the current directory.\n".$observer->getMessagesAsString());
		}

		return $client;
	}

	public function testRealServerAvailable()
	{
		if ( defined('FTP_CLIENT_TEST_FTP_HOST') === false )
		{
			$this->markTestSkipped("This test was skipped. Please set up FTP config at FTPConfig.php");
		}

		if ( is_resource(@fsockopen(FTP_CLIENT_TEST_FTP_HOST, FTP_CLIENT_TEST_FTP_PORT)) === false )
		{
			$this->markTestSkipped("This test was skipped. Please confirm if FTP server is running.");
		}
	}

	/**
	 * @expectedException RuntimeException
	 */
	public function test__construct()
	{
		$this->disableErrorReporting();
		// Case: fsockopen() returns NOT resource.
		new Suin_FTPClient_FTPClient('foo', 0);
	}

	/**
	 * @depends testRealServerAvailable
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Transfer mode is invalid.
	 */
	public function test__construct_With_invalid_transfer_mode()
	{
		new Suin_FTPClient_FTPClient(FTP_CLIENT_TEST_FTP_HOST, FTP_CLIENT_TEST_FTP_PORT, 'invalid');
	}

	/**
	 * @depends testRealServerAvailable
	 * @expectedException RuntimeException
	 * @expectedExceptionMessage Failed to connect to the FTP Server.
	 */
	public function test__construct_With_unexpected_response_code()
	{
		$client = $this
			->getMockBuilder('Suin_FTPClient_FTPClient')
			->disableOriginalConstructor()
			->setMethods(array('_getResponse'))
			->getMock();
		$client
			->expects($this->once())
			->method('_getResponse')
			->will($this->returnValue(0));
		$client->__construct(FTP_CLIENT_TEST_FTP_HOST, FTP_CLIENT_TEST_FTP_PORT);
	}

	public function testLogin()
	{
		// Case: Response code is not 331
		$client = $this
			->getMockBuilder('Suin_FTPClient_FTPClient')
			->disableOriginalConstructor()
			->setMethods(array('_request'))
			->getMock();
		$client
			->expects($this->once())
			->method('_request')
			->with('USER foo')
			->will($this->returnValue(array('code' => 0)));

		$actual = $client->login('foo', 'pass');
		$this->assertFalse($actual);
	}

	public function testLogin_With_invalid_password()
	{
		// Case: Response code is not 230
		$client = $this
			->getMockBuilder('Suin_FTPClient_FTPClient')
			->disableOriginalConstructor()
			->setMethods(array('_request'))
			->getMock();
		$client
			->expects($this->at(0))
			->method('_request')
			->with('USER foo')
			->will($this->returnValue(array('code' => 331)));
		$client
			->expects($this->at(1))
			->method('_request')
			->with('PASS pass')
			->will($this->returnValue(array('code' => 0)));
		$actual = $client->login('foo', 'pass');
		$this->assertFalse($actual);
	}

	public function testLogin_Success_to_login()
	{
		$client = $this
			->getMockBuilder('Suin_FTPClient_FTPClient')
			->disableOriginalConstructor()
			->setMethods(array('_request'))
			->getMock();
		$client
			->expects($this->at(0))
			->method('_request')
			->with('USER foo')
			->will($this->returnValue(array('code' => 331)));
		$client
			->expects($this->at(1))
			->method('_request')
			->with('PASS pass')
			->will($this->returnValue(array('code' => 230)));
		$actual = $client->login('foo', 'pass');
		$this->assertTrue($actual);
	}

	/**
	 * @depends testRealServerAvailable
	 */
	public function testLogin_With_real_server()
	{
		$client = $this->getNewFTPClient(false);
		$actual = $client->login(FTP_CLIENT_TEST_FTP_USER, FTP_CLIENT_TEST_FTP_PASS);
		$this->assertTrue($actual);
	}

	public function testDisconnect()
	{
		$client = $this
			->getMockBuilder('Suin_FTPClient_FTPClient')
			->disableOriginalConstructor()
			->setMethods(array('_request'))
			->getMock();
		$client
			->expects($this->once())
			->method('_request')
			->with('QUIT');

		$reflectionClass = new ReflectionClass($client);
		$reflectionProperty = $reflectionClass->getProperty('connection');
		$reflectionProperty->setAccessible(true);
		$reflectionProperty->setValue($client, 'this is not Null.');

		$client->disconnect();
		$this->assertAttributeSame(null, 'connection', $client);
	}

	public function testGetCurrentDirectory()
	{
		// Case: Response code is not 257
		$client = $this
			->getMockBuilder('Suin_FTPClient_FTPClient')
			->disableOriginalConstructor()
			->setMethods(array('_request'))
			->getMock();
		$client
			->expects($this->once())
			->method('_request')
			->with('PWD')
			->will($this->returnValue(array('code' => 0)));
		$actual = $client->getCurrentDirectory();
		$this->assertFalse($actual);
	}

	public function testGetCurrentDirectory_Success()
	{
		$client = $this
			->getMockBuilder('Suin_FTPClient_FTPClient')
			->disableOriginalConstructor()
			->setMethods(array('_request'))
			->getMock();
		$client
			->expects($this->once())
			->method('_request')
			->with('PWD')
			->will($this->returnValue(array(
				'code' => 257,
				'message' => '257 "/Users/suin" is the current directory.',
			)));
		$actual = $client->getCurrentDirectory();
		$this->assertSame('/Users/suin', $actual);
	}

	/**
	 * @depends testRealServerAvailable
	 */
	public function testGetCurrentDirectory_With_real_server()
	{
		$client = $this->getNewFTPClient(false);
		$client->login(FTP_CLIENT_TEST_FTP_USER, FTP_CLIENT_TEST_FTP_PASS);
		$actual = $client->getCurrentDirectory();
		$this->assertTrue(is_string($actual));
	}

	public function testChangeDirectory()
	{
		// Case: Response code is not 250
		$client = $this
			->getMockBuilder('Suin_FTPClient_FTPClient')
			->disableOriginalConstructor()
			->setMethods(array('_request'))
			->getMock();
		$client
			->expects($this->once())
			->method('_request')
			->with('CWD /foo/bar')
			->will($this->returnValue(array('code' => 0)));
		$actual = $client->changeDirectory('/foo/bar');
		$this->assertFalse($actual);
	}

	public function testChangeDirectory_Success()
	{
		$client = $this
			->getMockBuilder('Suin_FTPClient_FTPClient')
			->disableOriginalConstructor()
			->setMethods(array('_request'))
			->getMock();
		$client
			->expects($this->once())
			->method('_request')
			->with('CWD /foo/bar')
			->will($this->returnValue(array('code' => 250)));
		$actual = $client->changeDirectory('/foo/bar');
		$this->assertTrue($actual);
	}

	/**
	 * @depends testRealServerAvailable
	 */
	public function testChangeDirectory_With_real_server()
	{
		$client = $this->getNewFTPClient(false);
		$client->login(FTP_CLIENT_TEST_FTP_USER, FTP_CLIENT_TEST_FTP_PASS);
		$actual = $client->changeDirectory(FTP_CLIENT_TEST_REMOTE_SANDBOX_DIR);
		$this->assertTrue($actual);
	}

	public function testRemoveDirectory()
	{
		// Case: Response code is not 250
		$client = $this
			->getMockBuilder('Suin_FTPClient_FTPClient')
			->disableOriginalConstructor()
			->setMethods(array('_request'))
			->getMock();
		$client
			->expects($this->once())
			->method('_request')
			->with('RMD foo')
			->will($this->returnValue(array('code' => 0)));
		$actual = $client->removeDirectory('foo');
		$this->assertFalse($actual);
	}

	public function testRemoveDirectory_With_success()
	{
		$client = $this
			->getMockBuilder('Suin_FTPClient_FTPClient')
			->disableOriginalConstructor()
			->setMethods(array('_request'))
			->getMock();
		$client
			->expects($this->once())
			->method('_request')
			->with('RMD foo')
			->will($this->returnValue(array('code' => 250)));
		$actual = $client->removeDirectory('foo');
		$this->assertTrue($actual);
	}

	public function testRemoveDirectory_With_real_server()
	{
		// TODO
	}

	public function testCreateDirectory()
	{
		// Case: Response code is not 257
		$client = $this
			->getMockBuilder('Suin_FTPClient_FTPClient')
			->disableOriginalConstructor()
			->setMethods(array('_request'))
			->getMock();
		$client
			->expects($this->once())
			->method('_request')
			->with('MKD foo')
			->will($this->returnValue(array('code' => 0)));
		$actual = $client->createDirectory('foo');
		$this->assertFalse($actual);
	}

	public function testCreateDirectory_Success()
	{
		$client = $this
			->getMockBuilder('Suin_FTPClient_FTPClient')
			->disableOriginalConstructor()
			->setMethods(array('_request'))
			->getMock();
		$client
			->expects($this->once())
			->method('_request')
			->with('MKD foo')
			->will($this->returnValue(array('code' => 257)));
		$actual = $client->createDirectory('foo');
		$this->assertTrue($actual);
	}

	public function testCreateDirectory_With_real_server()
	{
		// TODO 
	}

	public function testRename()
	{
		// Return code for RNFR is not 350
		$client = $this
			->getMockBuilder('Suin_FTPClient_FTPClient')
			->disableOriginalConstructor()
			->setMethods(array('_request'))
			->getMock();
		$client
			->expects($this->once())
			->method('_request')
			->with('RNFR foo')
			->will($this->returnValue(array('code' => 0)));
		$actual = $client->rename('foo', 'bar');
		$this->assertFalse($actual);
	}

	public function testRename_With_RNTO_fail()
	{
		// Return code for RNTO is not 250
		$client = $this
			->getMockBuilder('Suin_FTPClient_FTPClient')
			->disableOriginalConstructor()
			->setMethods(array('_request'))
			->getMock();
		$client
			->expects($this->at(0))
			->method('_request')
			->with('RNFR foo')
			->will($this->returnValue(array('code' => 350)));
		$client
			->expects($this->at(1))
			->method('_request')
			->with('RNTO bar')
			->will($this->returnValue(array('code' => 0)));
		$actual = $client->rename('foo', 'bar');
		$this->assertFalse($actual);
	}

	public function testRename_Success()
	{
		$client = $this
			->getMockBuilder('Suin_FTPClient_FTPClient')
			->disableOriginalConstructor()
			->setMethods(array('_request'))
			->getMock();
		$client
			->expects($this->at(0))
			->method('_request')
			->with('RNFR foo')
			->will($this->returnValue(array('code' => 350)));
		$client
			->expects($this->at(1))
			->method('_request')
			->with('RNTO bar')
			->will($this->returnValue(array('code' => 250)));
		$actual = $client->rename('foo', 'bar');
		$this->assertTrue($actual);
	}

	public function testRename_With_real_server()
	{
		// TODO
	}

	public function testRemoveFile()
	{
		// Return code for RNFR is not 250
		$client = $this
			->getMockBuilder('Suin_FTPClient_FTPClient')
			->disableOriginalConstructor()
			->setMethods(array('_request'))
			->getMock();
		$client
			->expects($this->once())
			->method('_request')
			->with('DELE foo')
			->will($this->returnValue(array('code' => 0)));
		$actual = $client->removeFile('foo');
		$this->assertFalse($actual);
	}

	public function testRemoveFile_Success()
	{
		$client = $this
			->getMockBuilder('Suin_FTPClient_FTPClient')
			->disableOriginalConstructor()
			->setMethods(array('_request'))
			->getMock();
		$client
			->expects($this->once())
			->method('_request')
			->with('DELE foo')
			->will($this->returnValue(array('code' => 250)));
		$actual = $client->removeFile('foo');
		$this->assertTrue($actual);
	}

	/**
	 * @param $mode
	 * @dataProvider data4testSetPermission
	 * @expectedException InvalidArgumentException
	 */
	public function testSetPermission($mode)
	{
		// Case: Invalid mode
		$client = $this
			->getMockBuilder('Suin_FTPClient_FTPClient')
			->disableOriginalConstructor()
			->setMethods(array('_request'))
			->getMock();
		$client
			->expects($this->never())
			->method('_request');
		$client->setPermission('foo', $mode);
	}

	public static function data4testSetPermission()
	{
		return array(
			array('1'), // Not int
			array(-1), // invalid range
			array(0777 + 1), //invalid range
		);
	}

	public function testSetPermission_With_unexpected_response_code()
	{
		// Case: Invalid mode
		$client = $this
			->getMockBuilder('Suin_FTPClient_FTPClient')
			->disableOriginalConstructor()
			->setMethods(array('_request'))
			->getMock();
		$client
			->expects($this->once())
			->method('_request')
			->with('SITE CHMOD 777 foo')
			->will($this->returnValue(array('code' => 0)));
		$actual = $client->setPermission('foo', 0777);
		$this->assertFalse($actual);
	}

	public function testSetPermission_Success()
	{
		$client = $this
			->getMockBuilder('Suin_FTPClient_FTPClient')
			->disableOriginalConstructor()
			->setMethods(array('_request'))
			->getMock();
		$client
			->expects($this->once())
			->method('_request')
			->with('SITE CHMOD 777 foo')
			->will($this->returnValue(array('code' => 200)));
		$actual = $client->setPermission('foo', 0777);
		$this->assertTrue($actual);
	}

	public function testSetPermission_With_real_server()
	{
		// TODO
	}

	public function testGetList()
	{
		// Case: Fails to open data connection.
		$client = $this
			->getMockBuilder('Suin_FTPClient_FTPClient')
			->disableOriginalConstructor()
			->setMethods(array('_openPassiveDataConnection', '_request'))
			->getMock();
		$client
			->expects($this->once())
			->method('_openPassiveDataConnection')
			->will($this->returnValue(false));
		$client
			->expects($this->never())
			->method('_request');

		$actual = $client->getList('dir');
		$this->assertFalse($actual);
	}

	public function testGetList_With_NLST_response_code_not_150()
	{
		$client = $this
			->getMockBuilder('Suin_FTPClient_FTPClient')
			->disableOriginalConstructor()
			->setMethods(array('_openPassiveDataConnection', '_request'))
			->getMock();
		$client
			->expects($this->once())
			->method('_openPassiveDataConnection')
			->will($this->returnValue(true));
		$client
			->expects($this->once())
			->method('_request')
			->with('NLST dir')
			->will($this->returnValue(array('code' => 0)));
		$actual = $client->getList('dir');
		$this->assertFalse($actual);
	}

	public function testGetList_Success()
	{
		$resource = fopen('php://memory', 'rw');
		fwrite($resource, "index.php\r\nfoo.gif\nbar.png\r");
		fseek($resource, 0);

		$expect = array('index.php', 'foo.gif', 'bar.png');

		$client = $this
			->getMockBuilder('Suin_FTPClient_FTPClient')
			->disableOriginalConstructor()
			->setMethods(array('_openPassiveDataConnection', '_request'))
			->getMock();
		$client
			->expects($this->once())
			->method('_openPassiveDataConnection')
			->will($this->returnValue($resource));
		$client
			->expects($this->once())
			->method('_request')
			->with('NLST dir')
			->will($this->returnValue(array('code' => 150)));

		$actual = $client->getList('dir');

		$this->assertSame($expect, $actual);
	}

	public function testGetList_With_real_server()
	{
		// TODO
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid mode "invalid mode" was given
	 */
	public function testDownload()
	{
		$client = $this
			->getMockBuilder('Suin_FTPClient_FTPClient')
			->disableOriginalConstructor()
			->setMethods(array('_openPassiveDataConnection', '_request'))
			->getMock();
		$client
			->expects($this->never())
			->method('_openPassiveDataConnection');
		$client
			->expects($this->never())
			->method('_request');

		$client->download('remote file', 'local file', 'invalid mode');
	}

	/**
	 * @expectedException RuntimeException
	 * @expectedExceptionMessage Failed to open local file ""
	 */
	public function testDownload_Fails_to_open_local_file()
	{
		$this->disableErrorReporting();

		$client = $this
			->getMockBuilder('Suin_FTPClient_FTPClient')
			->disableOriginalConstructor()
			->setMethods(array('_openPassiveDataConnection', '_request'))
			->getMock();
		$client
			->expects($this->never())
			->method('_openPassiveDataConnection');
		$client
			->expects($this->never())
			->method('_request');

		$client->download('remote file', null, Suin_FTPClient_FTPClient::MODE_ASCII);
	}

	public function testDownload_Ascii_mode()
	{
		$client = $this
			->getMockBuilder('Suin_FTPClient_FTPClient')
			->disableOriginalConstructor()
			->setMethods(array('_openPassiveDataConnection', '_request'))
			->getMock();
		$client
			->expects($this->never())
			->method('_openPassiveDataConnection');
		$client
			->expects($this->once())
			->method('_request')
			->with('TYPE A')
			->will($this->returnValue(array('code' => 0)));

		$client->download('remote file', 'php://stdout', Suin_FTPClient_FTPClient::MODE_ASCII);
	}

	public function testDownload_Binary_mode()
	{
		$client = $this
			->getMockBuilder('Suin_FTPClient_FTPClient')
			->disableOriginalConstructor()
			->setMethods(array('_openPassiveDataConnection', '_request'))
			->getMock();
		$client
			->expects($this->never())
			->method('_openPassiveDataConnection');
		$client
			->expects($this->once())
			->method('_request')
			->with('TYPE I')
			->will($this->returnValue(array('code' => 0)));

		$client->download('remote file', 'php://stdout', Suin_FTPClient_FTPClient::MODE_BINARY);
	}

	public function testDownload_TYPE_not_200()
	{
		$client = $this
			->getMockBuilder('Suin_FTPClient_FTPClient')
			->disableOriginalConstructor()
			->setMethods(array('_openPassiveDataConnection', '_request'))
			->getMock();
		$client
			->expects($this->never())
			->method('_openPassiveDataConnection');
		$client
			->expects($this->once())
			->method('_request')
			->with('TYPE I')
			->will($this->returnValue(array('code' => 0)));

		$actual = $client->download('remote file', 'php://stdout', Suin_FTPClient_FTPClient::MODE_BINARY);
		$this->assertFalse($actual);
	}

	public function testDownload_Fails_to_open_data_connection()
	{
		$client = $this
			->getMockBuilder('Suin_FTPClient_FTPClient')
			->disableOriginalConstructor()
			->setMethods(array('_openPassiveDataConnection', '_request'))
			->getMock();
		$client
			->expects($this->once())
			->method('_request')
			->with('TYPE I')
			->will($this->returnValue(array('code' => 200)));
		$client
			->expects($this->once())
			->method('_openPassiveDataConnection')
			->will($this->returnValue(false));

		$actual = $client->download('remote file', 'php://stdout', Suin_FTPClient_FTPClient::MODE_BINARY);
		$this->assertFalse($actual);
	}

	public function testDownload_RETR_not_150()
	{
		$client = $this
			->getMockBuilder('Suin_FTPClient_FTPClient')
			->disableOriginalConstructor()
			->setMethods(array('_openPassiveDataConnection', '_request'))
			->getMock();
		$client
			->expects($this->at(0))
			->method('_request')
			->with('TYPE I')
			->will($this->returnValue(array('code' => 200)));
		$client
			->expects($this->at(1))
			->method('_openPassiveDataConnection')
			->will($this->returnValue(true));
		$client
			->expects($this->at(2))
			->method('_request')
			->with('RETR foo')
			->will($this->returnValue(array('code' => 0)));

		$actual = $client->download('foo', 'php://stdout', Suin_FTPClient_FTPClient::MODE_BINARY);
		$this->assertFalse($actual);
	}

	public function testDownload_Success()
	{
		$contents = 'data data data';

		$dataConnection = tmpfile();
		fwrite($dataConnection, $contents);
		fseek($dataConnection, 0);

		$localFile = tempnam(sys_get_temp_dir(), 'Tux');

		$client = $this
			->getMockBuilder('Suin_FTPClient_FTPClient')
			->disableOriginalConstructor()
			->setMethods(array('_openPassiveDataConnection', '_request'))
			->getMock();
		$client
			->expects($this->at(0))
			->method('_request')
			->with('TYPE I')
			->will($this->returnValue(array('code' => 200)));
		$client
			->expects($this->at(1))
			->method('_openPassiveDataConnection')
			->will($this->returnValue($dataConnection));
		$client
			->expects($this->at(2))
			->method('_request')
			->with('RETR foo')
			->will($this->returnValue(array('code' => 150)));

		$actual = $client->download('foo', $localFile, Suin_FTPClient_FTPClient::MODE_BINARY);
		$this->assertTrue($actual);

		$this->assertSame($contents, file_get_contents($localFile));
	}

	public function testDownload_With_real_server_ASCII()
	{
		
	}

	public function testDownload_With_real_server_BINARY()
	{

	}
}
