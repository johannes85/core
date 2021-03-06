<?php namespace net\xp_framework\unittest\core;

use lang\Object;
use lang\Closeable;
use lang\ClassLoader;
use lang\IllegalStateException;

/**
 * Tests with() functionality
 */
class WithTest extends \unittest\TestCase {
  private static $closes, $raises;

  #[@beforeClass]
  public static function defineCloseableSubclasses() {
    self::$closes= ClassLoader::defineClass('_WithTest_C0', Object::class, [Closeable::class], '{
      public $closed= false;
      public function close() { $this->closed= true; }
    }');
    self::$raises= ClassLoader::defineClass('_WithTest_C1', Object::class, [Closeable::class], '{
      private $throwable;
      public function __construct($class) { $this->throwable= $class; }
      public function close() { throw new $this->throwable("Cannot close"); }
    }');
  }

  #[@test]
  public function backwards_compatible_usage_without_closure() {
    with ($f= new Object()); {
      $this->assertInstanceOf(Object::class, $f);
    }
  }

  #[@test]
  public function new_usage_with_closure() {
    with (new Object(), function($f) {
      $this->assertInstanceOf(Object::class, $f);
    });
  }

  #[@test]
  public function closeable_is_open_inside_block() {
    with (self::$closes->newInstance(), function($f) {
      $this->assertFalse($f->closed);
    });
  }

  #[@test]
  public function closeable_is_closed_after_block() {
    $f= self::$closes->newInstance();
    with ($f, function() {
      // NOOP
    });
    $this->assertTrue($f->closed);
  }

  #[@test]
  public function all_closeables_are_closed_after_block() {
    $a= self::$closes->newInstance();
    $b= self::$closes->newInstance();
    with ($a, $b, function() {
      // NOOP
    });
    $this->assertEquals([true, true], [$a->closed, $b->closed]);
  }

  #[@test]
  public function all_closeables_are_closed_after_exception() {
    $a= self::$closes->newInstance();
    $b= self::$closes->newInstance();
    try {
      with ($a, $b, function() {
        throw new IllegalStateException('Test');
      });
      $this->fail('No exception thrown', null, 'lang.IllegalStateException');
    } catch (IllegalStateException $expected) {
      $this->assertEquals([true, true], [$a->closed, $b->closed]);
    }
  }

  #[@test]
  public function exceptions_from_close_are_ignored() {
    with (self::$raises->newInstance(IllegalStateException::class), function() {
      // NOOP
    });
  }

  #[@test, @values([IllegalStateException::class, 'Exception'])]
  public function exceptions_from_close_are_ignored_and_subsequent_closes_executed($class) {
    $b= self::$closes->newInstance();
    with (self::$raises->newInstance($class), $b, function() {
      // NOOP
    });
    $this->assertTrue($b->closed);
  }

  #[@test]
  public function usage_with_closure_returns_whatever_closure_returns() {
    $this->assertEquals('Test', with (function() { return 'Test'; }));
  }
}
