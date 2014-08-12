<?php namespace lang;

/**
 * Type is the base class for the XPClass and Primitive classes.
 *
 * @see    xp://lang.XPClass
 * @see    xp://lang.Primitive
 * @test   xp://net.xp_framework.unittest.reflection.TypeTest 
 */
class Type extends Object {
  public static $VAR;
  public static $VOID;
  public $name= '';

  static function __static() {
    self::$VAR= new self('var');
    self::$VOID= new self('void');
  }

  /**
   * Constructor
   *
   * @param   string name
   */
  public function __construct($name) {
    $this->name= $name;
  }

  /**
   * Retrieves the fully qualified class name for this class.
   * 
   * @return  string name - e.g. "io.File", "rdbms.mysql.MySQL"
   */
  public function getName() {
    return $this->name;
  }
  
  /**
   * Creates a string representation of this object
   *
   * @return  string
   */
  public function toString() {
    return $this->getClassName().'<'.$this->name.'>';
  }

  /**
   * Checks whether a given object is equal to this type
   *
   * @param   lang.Generic cmp
   * @return  bool
   */
  public function equals($cmp) {
    return $cmp instanceof self && $cmp->name === $this->name;
  }

  /**
   * Returns a hashcode for this object
   *
   * @return  string
   */
  public function hashCode() {
    return get_class($this).':'.$this->name;
  }
  
  /**
   * Creates a type list from a given string
   *
   * @param   string names
   * @return  lang.Type[] list
   */
  public static function forNames($names) {
    $types= [];
    for ($args= $names.',', $o= 0, $brackets= 0, $i= 0, $s= strlen($args); $i < $s; $i++) {
      if (',' === $args{$i} && 0 === $brackets) {
        $types[]= self::forName(ltrim(substr($args, $o, $i- $o)));
        $o= $i+ 1;
      } else if ('<' === $args{$i}) {
        $brackets++;
      } else if ('>' === $args{$i}) {
        $brackets--;
      }
    }
    return $types;
  }
  
  /**
   * Gets a type for a given name
   *
   * Checks for:
   * <ul>
   *   <li>Primitive types (string, integer, double, boolean, array)</li>
   *   <li>Array notations (string[] or string*)</li>
   *   <li>Resources</li>
   *   <li>Any type (var or *)</li>
   *   <li>Generic notations (util.collections.HashTable<lang.types.String, lang.Generic>)</li>
   *   <li>Anything else will be passed to XPClass::forName()</li>
   * </ul>
   *
   * @param   string name
   * @return  lang.Type
   */
  public static function forName($name) {
    static $deprecated= [
      'char'      => 'string',
      'integer'   => 'int',
      'boolean'   => 'bool',
      'float'     => 'double',
      'mixed'     => 'var',
      '*'         => 'var',
      'array'     => 'var[]',
      'resource'  => 'var'
    ];
    static $primitives= [
      'string'    => true,
      'int'       => true,
      'double'    => true,
      'bool'      => true
    ];
    
    // Map deprecated type names
    $type= isset($deprecated[$name]) ? $deprecated[$name] : $name;
    
    // Map well-known primitives, var and void, handle rest syntactically:
    // * T[] is an array
    // * [:T] is a map 
    // * T* is a vararg
    // * T<K, V> is a generic
    // * Anything else is a qualified or unqualified class name
    if (isset($primitives[$type])) {
      return Primitive::forName($type);
    } else if ('var' === $type) {
      return self::$VAR;
    } else if ('void' === $type) {
      return self::$VOID;
      return $type;
    } else if (0 === substr_compare($type, '[]', -2)) {
      return new ArrayType(substr($type, 0, -2));
    } else if (0 === substr_compare($type, '[:', 0, 2)) {
      return new MapType(substr($type, 2, -1));
    } else if (0 === substr_compare($type, '*', -1)) {
      return new ArrayType(substr($type, 0, -1));
    } else if (false === ($p= strpos($type, '<'))) {
      return strstr($type, '.') ? XPClass::forName($type) : new XPClass($type);
    }
    
    // Generics
    // * D<K, V> is a generic type definition D with K and V components
    //   except if any of K, V contains a ?, in which case it's a wild 
    //   card type.
    // * Deprecated: array<T> is T[], array<K, V> is [:T]
    if (strstr($type, '?')) {
      return WildcardType::forName($type);
    } else if (0 === substr_compare($type, 'array', 0, $p)) {
      $components= self::forNames(substr($type, $p+ 1, -1));
      $s= sizeof($components);
      if (2 === $s) {
        return new MapType($components[1]);
      } else if (1 === $s) {
        return new ArrayType($components[0]);
      }
    } else {
      $base= substr($type, 0, $p);
      $components= self::forNames(substr($type, $p+ 1, -1));
      return cast(self::forName($base), 'lang.XPClass')->newGenericType($components);
    }

    throw new \IllegalArgumentException('Unparseable name '.$name);
  }
  
  /**
   * Returns type literal
   *
   * @return  string
   */
  public function literal() {
    return $this->name;
  }

  /**
   * Determines whether the specified object is an instance of this
   * type. 
   *
   * @param   var obj
   * @return  bool
   */
  public function isInstance($obj) {
    return self::$VAR === $this;      // VAR is always true, VOID never
  }

  /**
   * Returns a new instance of this object
   *
   * @param   var value
   * @return  var
   */
  public function newInstance($value= null) {
    if (self::$VAR === $this) return $value;
    throw new IllegalAccessException('Cannot instantiate '.$this->name.' type');
  }

  /**
   * Cast a value to this type
   *
   * @param   var value
   * @return  var
   * @throws  lang.ClassCastException
   */
  public function cast($value) {
    if (self::$VAR === $this) return $value;
    raise('lang.ClassCastException', 'Cannot cast '.\xp::typeOf($value).' to the void type');
  }

  /**
   * Tests whether this type is assignable from another type
   *
   * @param   var type
   * @return  bool
   */
  public function isAssignableFrom($type) {
    return self::$VAR === $this;      // VAR is always assignable, VOID never
  }
}
