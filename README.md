README
======

What is StreamLibrary?
-----------------

StreamLibrary is a PHP wrapper via stream functions. It allows to work with streams with more
comfortable environment.


Requirements
------------

StreamLibrary is only supported on PHP 5 and up.

Installation
------------

The best way to install StreamLibrary is with composer:

```
composer.phar require konstantin-kuklin/stream-library:dev-master
```

Documentation
-------------

First step to work is a creating object of Stream:

```php
$stream = new \Stream\Stream($path, $protocol, $port, $driver);
```

**path** - Path to file on system or ip address in network or hostname which we will work

**protocol** - String value of protocol type, can be Stream::PROTOCOL_TCP,Stream::PROTOCOL_UDP,Stream::PROTOCOL_UNIX

**port** - Integer value of port to connect. Not needs if protocol Stream::PROTOCOL_UNIX. Default value = 0.

**driver** - Object implements StreamDriverInterface. If your connection need to change transfer data you need to describe it logic with this object. Default value is null(mean data haven't been changed)

### Get data from Stream

```php
$stream = new \Stream\Stream($path, $protocol, $port, $driver);
$stream->getContents($maxLength, $offset);
```

**maxLength** - The maximum bytes to read. Default value is -1 (read all the remaining buffer)

**offset** - Seek to the specified offset before reading. Default value is -1 (read without offset)

### Send data to Stream

```php
$stream = new \Stream\Stream($path, $protocol, $port, $driver);
$stream->sendContents($contents);
```

**contents** - can contain any data
