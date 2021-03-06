<?php namespace net\xp_framework\unittest\reflection;

use lang\ClassLoader;
use lang\XPClass;
use lang\IllegalAccessException;

/**
 * TestCase
 *
 * @see      xp://lang.reflect.Constructor
 * @see      xp://lang.reflect.Method
 * @see      xp://lang.reflect.Field
 */
class PrivateAccessibilityTest extends \unittest\TestCase {
  private static $fixture, $fixtureChild, $fixtureCtorChild;
  
  /**
   * Initialize fixture, fixtureChild and fixtureCtorChild members
   */
  #[@beforeClass]
  public static function initializeClasses() {
    self::$fixture= XPClass::forName('net.xp_framework.unittest.reflection.PrivateAccessibilityFixture');
    self::$fixtureChild= XPClass::forName('net.xp_framework.unittest.reflection.PrivateAccessibilityFixtureChild');
    self::$fixtureCtorChild= XPClass::forName('net.xp_framework.unittest.reflection.PrivateAccessibilityFixtureCtorChild');
  }

  #[@test, @expect(IllegalAccessException::class)]
  public function invokingPrivateConstructor() {
    self::$fixture->getConstructor()->newInstance([]);
  }

  #[@test]
  public function invokingPrivateConstructorFromSameClass() {
    $this->assertInstanceOf(self::$fixture, PrivateAccessibilityFixture::construct(self::$fixture));
  }

  #[@test, @expect(IllegalAccessException::class)]
  public function invokingPrivateConstructorFromParentClass() {
    PrivateAccessibilityFixtureCtorChild::construct(self::$fixture);
  }

  #[@test, @expect(IllegalAccessException::class)]
  public function invokingPrivateConstructorFromChildClass() {
    PrivateAccessibilityFixtureCtorChild::construct(self::$fixtureChild);
  }

  #[@test]
  public function invokingPrivateConstructorMadeAccessible() {
    $this->assertInstanceOf(self::$fixture, self::$fixture
      ->getConstructor()
      ->setAccessible(true)
      ->newInstance([])
    );
  }

  #[@test, @expect(IllegalAccessException::class)]
  public function invokingPrivateMethod() {
    self::$fixture->getMethod('target')->invoke(PrivateAccessibilityFixture::construct(self::$fixture));
  }

  #[@test]
  public function invokingPrivateMethodFromSameClass() {
    $this->assertEquals('Invoked', PrivateAccessibilityFixture::invoke(self::$fixture));
  }

  #[@test, @expect(IllegalAccessException::class)]
  public function invokingPrivateMethodFromParentClass() {
    PrivateAccessibilityFixtureChild::invoke(self::$fixture);
  }

  #[@test, @expect(IllegalAccessException::class)]
  public function invokingPrivateMethodFromChildClass() {
    PrivateAccessibilityFixtureChild::invoke(self::$fixtureChild);
  }

  #[@test]
  public function invokingPrivateMethodMadeAccessible() {
    $this->assertEquals('Invoked', self::$fixture
      ->getMethod('target')
      ->setAccessible(true)
      ->invoke(PrivateAccessibilityFixture::construct(self::$fixture))
    );
  }

  #[@test, @expect(IllegalAccessException::class)]
  public function invokingPrivateStaticMethod() {
    self::$fixture->getMethod('staticTarget')->invoke(null);
  }

  #[@test]
  public function invokingPrivateStaticMethodFromSameClass() {
    $this->assertEquals('Invoked', PrivateAccessibilityFixture::invokeStatic(self::$fixture));
  }

  #[@test, @expect(IllegalAccessException::class)]
  public function invokingPrivateStaticMethodFromParentClass() {
    PrivateAccessibilityFixtureChild::invokeStatic(self::$fixture);
  }

  #[@test, @expect(IllegalAccessException::class)]
  public function invokingPrivateStaticMethodFromChildClass() {
    PrivateAccessibilityFixtureChild::invokeStatic(self::$fixtureChild);
  }

  #[@test]
  public function invokingPrivateStaticMethodMadeAccessible() {
    $this->assertEquals('Invoked', self::$fixture
      ->getMethod('staticTarget')
      ->setAccessible(true)
      ->invoke(null)
    );
  }

  #[@test, @expect(IllegalAccessException::class)]
  public function readingPrivateMember() {
    self::$fixture->getField('target')->get(PrivateAccessibilityFixture::construct(self::$fixture));
  }

  #[@test]
  public function readingPrivateMemberFromSameClass() {
    $this->assertEquals('Target', PrivateAccessibilityFixture::read(self::$fixture));
  }

  #[@test, @expect(IllegalAccessException::class)]
  public function readingPrivateMemberFromParentClass() {
    PrivateAccessibilityFixtureChild::read(self::$fixture);
  }

  #[@test, @ignore('$this->getClass()->getField($field) does not yield private field declared in parent class')]
  public function readingPrivateMemberFromChildClass() {
    PrivateAccessibilityFixtureChild::read(self::$fixtureChild);
  }

  #[@test]
  public function readingPrivateMemberMadeAccessible() {
    $this->assertEquals('Target', self::$fixture
      ->getField('target')
      ->setAccessible(true)
      ->get(PrivateAccessibilityFixture::construct(self::$fixture))
    );
  }

  #[@test, @expect(IllegalAccessException::class)]
  public function readingPrivateStaticMember() {
    self::$fixture->getField('staticTarget')->get(null);
  }

  #[@test]
  public function readingPrivateStaticMemberFromSameClass() {
    $this->assertEquals('Target', PrivateAccessibilityFixture::readStatic(self::$fixture));
  }

  #[@test, @expect(IllegalAccessException::class)]
  public function readingPrivateStaticMemberFromParentClass() {
    PrivateAccessibilityFixtureChild::readStatic(self::$fixture);
  }

  #[@test, @ignore('$this->getClass()->getField($field) does not yield private field declared in parent class')]
  public function readingPrivateStaticMemberFromChildClass() {
    PrivateAccessibilityFixtureChild::readStatic(self::$fixtureChild);
  }

  #[@test]
  public function readingPrivateStaticMemberMadeAccessible() {
    $this->assertEquals('Target', self::$fixture
      ->getField('staticTarget')
      ->setAccessible(true)
      ->get(null)
    );
  }

  #[@test, @expect(IllegalAccessException::class)]
  public function writingPrivateMember() {
    self::$fixture->getField('target')->set(PrivateAccessibilityFixture::construct(self::$fixture), null);
  }

  #[@test]
  public function writingPrivateMemberFromSameClass() {
    $this->assertEquals('Modified', PrivateAccessibilityFixture::write(self::$fixture));
  }

  #[@test, @expect(IllegalAccessException::class)]
  public function writingPrivateMemberFromParentClass() {
    PrivateAccessibilityFixtureChild::write(self::$fixture);
  }

  #[@test, @ignore('$this->getClass()->getField($field) does not yield private field declared in parent class')]
  public function writingPrivateMemberFromChildClass() {
    PrivateAccessibilityFixtureChild::write(self::$fixtureChild);
  }

  #[@test]
  public function writingPrivateMemberMadeAccessible() {
    with ($f= self::$fixture->getField('target'), $i= PrivateAccessibilityFixture::construct(self::$fixture)); {
      $f->setAccessible(true);
      $f->set($i, 'Modified');
      $this->assertEquals('Modified', $f->get($i));
    }
  }

  #[@test, @expect(IllegalAccessException::class)]
  public function writingPrivateStaticMember() {
    self::$fixture->getField('staticTarget')->set(null, 'Modified');
  }

  #[@test]
  public function writingPrivateStaticMemberFromSameClass() {
    $this->assertEquals('Modified', PrivateAccessibilityFixture::writeStatic(self::$fixture));
  }

  #[@test, @expect(IllegalAccessException::class)]
  public function writingPrivateStaticMemberFromParentClass() {
    PrivateAccessibilityFixtureChild::writeStatic(self::$fixture);
  }

  #[@test, @ignore('$this->getClass()->getField($field) does not yield private field declared in parent class')]
  public function writingPrivateStaticMemberFromChildClass() {
    PrivateAccessibilityFixtureChild::writeStatic(self::$fixtureChild);
  }

  #[@test]
  public function writingPrivateStaticMemberMadeAccessible() {
    with ($f= self::$fixture->getField('staticTarget')); {
      $f->setAccessible(true);
      $f->set(null, 'Modified');
      $this->assertEquals('Modified', $f->get(null));
    }
  }
}
