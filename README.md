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
try
{
	$client = new Suin_FTPClient_FTPClient('127.0.0.1');
	$client->login('suin', 'password');;
	$client->upload('foo.php', 'foo.php', Suin_FTPClient_FTPClient::MODE_BINARY);
	$client->disconnect();
}
catch ( Exception $e )
{
	echo $e;
}
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

