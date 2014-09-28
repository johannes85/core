<?php namespace lang;

/**
 * Enumeration base class
 *
 * @see   http://news.xp-framework.net/article/222/2007/11/12/
 * @see   http://news.xp-framework.net/article/207/2007/07/29/
 * @test  xp://net.xp_framework.unittest.core.EnumTest
 */
abstract class Enum extends Object {
  public $name= '';
  protected $ordinal= 0;

  static function __static() {
    if (__CLASS__ === ($class= get_called_class())) return;

    // Automatically initialize this enum's public static members
    $i= 0;
    $c= new \ReflectionClass($class);
    foreach ($c->getStaticProperties() as $name => $prop) {
      if (null !== $prop) $i= $prop;
      $c->setStaticPropertyValue($name, $c->newInstance($i++, $name));
    }
  }

  /**
   * Constructor
   *
   * @param   int ordinal default 0
   * @param   string name default ''
   */
  public function __construct($ordinal= 0, $name= '') {
    $this->ordinal= $ordinal;
    $this->name= $name;
  }

  /**
   * Returns the enumeration member uniquely identified by its name
   *
   * @param   lang.XPClass class class object
   * @param   string name enumeration member
   * @return  lang.Enum
   * @throws  lang.IllegalArgumentException in case the enum member does not exist or when the given class is not an enum
   */
  public static function valueOf(XPClass $class, $name) {
    if (!$class->isEnum()) {
      throw new IllegalArgumentException('Argument class must be lang.XPClass<? extends lang.Enum>');
    }
    try {
      $prop= $class->_reflect->getStaticPropertyValue($name);
      if ($class->isInstance($prop)) return $prop;
    } catch (\ReflectionException $e) {
      throw new IllegalArgumentException($e->getMessage());
    }
    throw new IllegalArgumentException('No such member "'.$name.'" in '.$class->getName());
  }

  /**
   * Returns the enumeration members for a given class
   *
   * @param   lang.XPClass class class object
   * @return  lang.Enum[]
   * @throws  lang.IllegalArgumentException in case the given class is not an enum
   */
  public static function valuesOf(XPClass $class) {
    if (!$class->isEnum()) {
      throw new \IllegalArgumentException('Argument class must be lang.XPClass<? extends lang.Enum>');
    }
    $r= [];
    foreach ($class->_reflect->getStaticProperties() as $prop) {
      $class->isInstance($prop) && $r[]= $prop;
    }
    return $r;
  }

  /**
   * Returns all members for the called enum class
   *
   * @return  lang.Enum[]
   */
  public static function values() {
    $r= [];
    $c= new \ReflectionClass(get_called_class());
    foreach ($c->getStaticProperties() as $prop) {
      if ($prop instanceof Generic && $c->isInstance($prop)) {
        $r[]= $prop;
      }
    }
    return $r;
  }

  /**
   * Clone interceptor - ensures enums cannot be cloned
   *
   * @throws  lang.CloneNotSupportedException
   */
  public final function __clone() {
    raise('lang.CloneNotSupportedException', 'Enums cannot be cloned');
  }

  /**
   * Returns the name of this enum constant, exactly as declared in its 
   * enum declaration.
   *
   * @return  string
   */
  public function name() {
    return $this->name;
  }
  
  /**
   * Returns the ordinal of this enumeration constant (its position in 
   * its enum declaration, where the initial constant is assigned an 
   * ordinal of zero).
   *
   * @return  int
   */
  public function ordinal() {
    return $this->ordinal;
  }

  /**
   * Create a string representation of this enum
   *
   * @return  string
   */
  public function toString() {
    return $this->name;
  }
}
