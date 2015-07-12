<?php namespace net\xp_framework\unittest\core;

/**
 * Verifies lang.Object's `__call()` implementation
 *
 * @see   https://github.com/xp-framework/xp-framework/issues/133
 */
class MissingMethodsTest extends \unittest\TestCase {

  #[@test, @expect(class= 'lang.Error', withMessage= '/Call to undefined method lang.Object::run()/')]
  public function missingMethodInvocation() {
    $o= new \lang\Object();
    $o->run();
  }

  #[@test, @expect(class= 'lang.Error', withMessage= '/Call to undefined method lang.Object::run()/')]
  public function missingParentMethodInvocation() {
    $f= \lang\ClassLoader::defineClass('MissingMethodsTest_Fixture', 'lang.Object', [], '{
      public function run() {
        parent::run();
      }
    }');
    call_user_func([$f->newInstance(), 'run']);
  }

  #[@test, @expect(class= 'lang.Error', withMessage= '/Call to undefined method .+::run()/')]
  public function missingParentParentMethodInvocation() {
    $b= \lang\ClassLoader::defineClass('MissingMethodsTest_BaseFixture', 'lang.Object', [], '{}');
    $c= \lang\ClassLoader::defineClass('MissingMethodsTest_ChildFixture', $b->getName(), [], '{
      public function run() {
        parent::run();
      }
    }');
    call_user_func([$c->newInstance(), 'run']);
  }

  #[@test, @expect(class= 'lang.Error', withMessage= '/Call to undefined method lang.Object::run()/')]
  public function missingParentPassMethodInvocation() {
    $b= \lang\ClassLoader::defineClass('MissingMethodsTest_PassBaseFixture', 'lang.Object', [], '{
      public function run() {
        parent::run();
      }
    }');
    $c= \lang\ClassLoader::defineClass('MissingMethodsTest_PassChildFixture', $b->getName(), [], '{
      public function run() {
        parent::run();
      }
    }');
    call_user_func([$c->newInstance(), 'run']);
  }

  #[@test, @expect(class= 'lang.Error', withMessage= '/Call to undefined static method lang.Object::run()/')]
  public function missingStaticParentMethodInvocation() {
    $f= \lang\ClassLoader::defineClass('MissingMethodsTest_StaticFixture', 'lang.Object', [], '{
      public static function run() {
        parent::run();
      }
    }');
    call_user_func([$f->literal(), 'run']);
  }

  #[@test, @expect(class= 'lang.Error', withMessage= '/Call to undefined static method .+::run()/')]
  public function missingStaticParentParentMethodInvocation() {
    $b= \lang\ClassLoader::defineClass('MissingMethodsTest_StaticBaseFixture', 'lang.Object', [], '{}');
    $c= \lang\ClassLoader::defineClass('MissingMethodsTest_StaticChildFixture', $b->getName(), [], '{
      public static function run() {
        parent::run();
      }
    }');
    call_user_func([$c->literal(), 'run']);
  }

  #[@test, @expect(class= 'lang.Error', withMessage= '/Call to undefined static method lang.Object::run()/')]
  public function missingStaticParentPassMethodInvocation() {
    $b= \lang\ClassLoader::defineClass('MissingMethodsTest_StaticPassBaseFixture', 'lang.Object', [], '{
      public static function run() {
        parent::run();
      }
    }');
    $c= \lang\ClassLoader::defineClass('MissingMethodsTest_StaticPassChildFixture', $b->getName(), [], '{
      public static function run() {
        parent::run();
      }
    }');
    call_user_func([$c->literal(), 'run']);
  }
}
