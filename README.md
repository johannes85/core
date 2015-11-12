XP Framework Core
=================
[![Build Status on TravisCI](https://secure.travis-ci.org/xp-framework/core.png)](http://travis-ci.org/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Required PHP 5.5+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-5_5plus.png)](http://php.net/)
[![Supports PHP 7.0+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-7_0plus.png)](http://php.net/)
[![Supports HHVM 3.5+](https://raw.githubusercontent.com/xp-framework/web/master/static/hhvm-3_5plus.png)](http://hhvm.com/)
[![Latest Stable Version](https://poser.pugx.org/xp-framework/core/version.png)](https://packagist.org/packages/xp-framework/core)

This is the XP Framework's development checkout. 

Installation
------------
If you just want to use the XP Framework, grab a release using `composer require xp-framework/core`. If you wish to use this development checkout, clone this repository instead.

### Runners
The entry point for software written in the XP Framework is not the PHP
interpreter's CLI / web server API but either a command line runner or
a specialized *web* entry point. These runners can be installed by using
the following one-liner:

```sh
$ cd ~/bin
$ wget 'https://github.com/xp-framework/xp-runners/releases/download/v5.6.1/setup' -O - | php
```

### Using it
To use the the XP Framework development checkout, put the following
in your `~/bin/xp.ini` file:

```ini
use=/path/to/xp/core
```

Finally, start `xp -v` to see it working:

```sh
$ xp -v
XP 6.6.0-dev { PHP 5.6.15 & ZE 2.6.0 } @ Windows NT SLATE 10.0 build 10240 (Windows 10) i586
Copyright (c) 2001-2015 the XP group
FileSystemCL<...\xp\core\src\main\php\>
FileSystemCL<...\xp\core\src\test\php\>
FileSystemCL<...\xp\core\src\test\resources\>
FileSystemCL<...\home\Timm\devel\xp\core\>
```

Basic usage
-----------
The XP Framework runs classes with a public static `main()` method. To run a class, supply the fully qualified classname as command line argument: `$ xp {class.Name}`.

Any argument after the classname will be passed to the class' main method.

### Hello World
Save the following sourcecode to a file called AgeInDays.class.php:

```php
<?php
use util\Date;
use util\DateUtil;
use util\cmd\Console;

class AgeInDays {

  public static function main(array $args) {
    $span= DateUtil::timespanBetween(new Date($args[0]), Date::now());
    Console::writeLine('Hey, you are ', $span->getDays(), ' days old');
  }
}
```

Now run it:

```sh
$ xp AgeInDays 1977-12-14
Hey, you are 13724 days old
```

Alternatively, you can run this directly in the shell:

```sh
$ xp -w '
use util\{Date, DateUtil};

$span= DateUtil::timespanBetween(new Date($argv[1]), Date::now());
return "Hey, you are ".$span->getDays()." days old"
' 1977-12-14
Hey, you are 13724 days old
```

**Enjoy!**

Contributing
------------
To contribute, use the GitHub way - fork, hack, and submit a pull request! :octocat: