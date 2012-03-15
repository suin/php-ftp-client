# FTP Client Library for PHP

## Features

* Works without the ftp extension.
* Minimum and simple.
* Unit tested.

## Requirements

* PHP 5.2.0 or later

## Install

Just copy ```Source/Suin``` to vendors directory in your project.

## How to Use

```
<?php
try
{
	$client = new Suin_FTPClient_FTPClient('127.0.0.1');
	
	if ( $client->login('suin', 'password') === false )
	{
		echo 'Cannot login!';
	}
	
	if ( $client->upload('foo.php', 'foo.php', Suin_FTPClient_FTPClient::MODE_BINARY) === false )
	{
		echo 'Failed to upload!';
	}
	
	$client->disconnect();
}
catch ( Exception $e )
{
	echo $e;
}
```

More detail, please see ```Suin_FTPClient_FTPClientInterface```.

## Observer for debugging

For logging the TCP messages, you can assing an observer object to FTPClient object.
The observer object must implement ```Suin_FTPClient_ObserverInterface```.

```
<?php
class MyObserver implements Suin_FTPClient_ObserverInterface
{
	public function updateWithRequest($request)
	{
		echo 'PUT > '.$request;
	}

	public function updateWithResponse($message, $code)
	{
		echo 'GET < '.$message;
	}
}

$myObserver = new MyObserver();
$client = new Suin_FTPClient_FTPClient('127.0.0.1');
$client->setObserver($myObserver);
```

## Testing

* Needs PHPUnit 3.6
* Needs PHP 5.3 or later

### Prepare for test

```
cd Tests
cp FTPConfig.sample.php FTPConfig.php
```

And then, edit FTPConfig.php!

### How to run test

```
cd Tests
phpunit
```

