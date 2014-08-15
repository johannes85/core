<?php namespace net\xp_framework\unittest\core;

use lang\FunctionType;
use lang\Primitive;
use lang\XPClass;
use lang\Type;
use lang\ArrayType;
use lang\MapType;

/**
 * TestCase
 *
 * @see      xp://lang.FunctionType
 */
class FunctionTypeTest extends \unittest\TestCase {

  #[@test]
  public function can_create() {
    new FunctionType([Primitive::$STRING], Primitive::$STRING);
  }

  #[@test]
  public function a_function_accepting_one_string_arg_and_returning_a_string() {
    $this->assertEquals(
      new FunctionType([Primitive::$STRING], Primitive::$STRING),
      FunctionType::forName('function(string): string')
    );
  }

  #[@test]
  public function a_function_accepting_two_string_args_and_returning_a_string() {
    $this->assertEquals(
      new FunctionType([Primitive::$STRING, Primitive::$STRING], Primitive::$STRING),
      FunctionType::forName('function(string, string): string')
    );
  }

  #[@test]
  public function a_zero_arg_function_which_returns_bool() {
    $this->assertEquals(
      new FunctionType([], Primitive::$BOOL),
      FunctionType::forName('function(): bool')
    );
  }

  #[@test]
  public function a_zero_arg_function_which_returns_a_function_type() {
    $this->assertEquals(
      new FunctionType([], new FunctionType([Primitive::$STRING], Primitive::$INT)),
      FunctionType::forName('function(): function(string): int')
    );
  }

  #[@test]
  public function a_function_which_accepts_a_function_type() {
    $this->assertEquals(
      new FunctionType([new FunctionType([Primitive::$STRING], Primitive::$INT)], Type::$VAR),
      FunctionType::forName('function(function(string): int): var')
    );
  }

  #[@test]
  public function a_function_accepting_an_array_of_generic_objects_and_not_returning_anything() {
    $this->assertEquals(
      new FunctionType([new ArrayType(XPClass::forName('lang.Generic'))], Type::$VOID),
      FunctionType::forName('function(lang.Generic[]): void')
    );
  }

  #[@test]
  public function function_with_zero_args_is_instance_of_zero_arg_function_type() {
    $this->assertTrue((new FunctionType([], Type::$VAR))->isInstance(
      function() { }
    ));
  }

  #[@test]
  public function function_with_two_args_is_instance_of_two_arg_function_type() {
    $this->assertTrue((new FunctionType([Type::$VAR, Type::$VAR], Type::$VAR))->isInstance(
      function($a, $b) { }
    ));
  }

  #[@test]
  public function function_with_type_hinted_arg_is_instance_of_function_type_with_class_signature() {
    $this->assertTrue((new FunctionType([XPClass::forName('lang.XPClass')], Type::$VAR))->isInstance(
      function(XPClass $c) { }
    ));
  }

  #[@test]
  public function function_with_array_hinted_arg_is_instance_of_function_type_with_array_signature() {
    $this->assertTrue((new FunctionType([Primitive::$ARRAY], Type::$VAR))->isInstance(
      function(array $a) { }
    ));
  }

  #[@test]
  public function function_with_callable_hinted_arg_is_instance_of_function_type_with_function_signature() {
    $this->assertTrue((new FunctionType([new FunctionType([], Type::$VAR)], Type::$VAR))->isInstance(
      function(callable $a) { }
    ));
  }

  #[@test]
  public function function_with_two_args_is_not_instance_of_zero_arg_function_type() {
    $this->assertFalse((new FunctionType([], Type::$VAR))->isInstance(
      function($a, $b) { }
    ));
  }

  #[@test]
  public function function_with_zero_args_is_not_instance_of_two_arg_function_type() {
    $this->assertFalse((new FunctionType([Type::$VAR, Type::$VAR], Type::$VAR))->isInstance(
      function() { }
    ));
  }

  #[@test]
  public function builtin_strlen_isInstance() {
    $this->assertTrue((new FunctionType([Primitive::$STRING], Primitive::$INT))->isInstance('strlen'));
  }

  #[@test]
  public function array_referencing_static_class_method_is_instance() {
    $type= new FunctionType([Primitive::$STRING, XPClass::forName('lang.IClassLoader')], XPClass::forName('lang.XPClass'));
    $this->assertTrue($type->isInstance(['lang.XPClass', 'forName']));
  }

  #[@test]
  public function array_referencing_static_class_method_is_instance_without_optional_parameter() {
    $type= new FunctionType([Primitive::$STRING], XPClass::forName('lang.XPClass'));
    $this->assertTrue($type->isInstance(['lang.XPClass', 'forName']));
  }

  #[@test]
  public function return_type_verified_for_static_class_methods() {
    $type= new FunctionType([Primitive::$STRING], Type::$VOID);
    $this->assertFalse($type->isInstance(['lang.XPClass', 'forName']));
  }

  #[@test]
  public function parameter_type_verified_for_static_class_methods() {
    $type= new FunctionType([XPClass::forName('lang.Object')], XPClass::forName('lang.XPClass'));
    $this->assertFalse($type->isInstance(['lang.XPClass', 'forName']));
  }

  #[@test]
  public function array_referencing_non_existant_static_class_method_is_instance() {
    $type= new FunctionType([Primitive::$STRING], XPClass::forName('lang.XPClass'));
    $this->assertFalse($type->isInstance(['lang.XPClass', 'non-existant']));
  }

  #[@test]
  public function array_referencing_instance_method_is_instance() {
    $type= new FunctionType([], Primitive::$STRING);
    $this->assertTrue($type->isInstance([$this, 'getName']));
  }

  #[@test]
  public function return_type_verified_for_instance_methods() {
    $type= new FunctionType([], Primitive::$INT);
    $this->assertFalse($type->isInstance([$this, 'getName']));
  }

  #[@test]
  public function array_referencing_non_existant_instance_method_is_not_instance() {
    $type= new FunctionType([], Primitive::$STRING);
    $this->assertFalse($type->isInstance([$this, 'non-existant']));
  }

  #[@test]
  public function lang_Type_forName_parsed_function_type() {
    $this->assertEquals(
      new FunctionType([Type::$VAR], Primitive::$BOOL),
      Type::forName('function(var): bool')
    );
  }

  #[@test]
  public function cast() {
    $value= function($a) { };
    $this->assertEquals($value, (new FunctionType([Type::$VAR], Type::$VAR))->cast($value));
  }

  #[@test, @expect('lang.ClassCastException'), @values([
  #  0, -1, 0.5, true, false, '', 'Test',
  #  [[]], [['key' => 'value']],
  #  [['non-existant', 'method']], [['lang.XPClass', 'non-existant']],
  #  [[null, 'method']], [[new \lang\Object(), 'non-existant']]
  #])]
  public function cannot_cast_this($value) {
    (new FunctionType([Type::$VAR], Type::$VAR))->cast($value);
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function return_type_verified_for_instance_methods_when_casting() {
    (new FunctionType([], Primitive::$VOID))->newInstance([$this, 'getName']);
  }

  #[@test]
  public function create_instances_from_function() {
    $value= (new FunctionType([], Type::$VAR))->newInstance(function() { return 'Test'; });
    $this->assertEquals('Test', $value());
  }

  #[@test]
  public function create_instances_from_string_referencing_builtin() {
    $value= (new FunctionType([Primitive::$STRING], Type::$VAR))->newInstance('strlen');
    $this->assertEquals(4, $value('Test'));
  }

  #[@test]
  public function create_instances_from_array_referencing_static_class_method() {
    $value= (new FunctionType([Primitive::$STRING], XPClass::forName('lang.XPClass')))->newInstance(['lang.XPClass', 'forName']);
    $this->assertEquals(XPClass::forName('lang.Object'), $value('lang.Object'));
  }

  #[@test]
  public function create_instances_from_array_referencing_instance_method() {
    $value= (new FunctionType([], Primitive::$STRING))->newInstance([$this, 'getName']);
    $this->assertEquals($this->getName(), $value());
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function return_type_verified_for_instance_methods_when_creating_instances() {
    (new FunctionType([], Primitive::$VOID))->newInstance([$this, 'getName']);
  }

  #[@test, @expect('lang.IllegalArgumentException'), @values([
  #  0, -1, 0.5, true, false, '', 'Test',
  #  [[]], [['key' => 'value']],
  #  [['non-existant', 'method']], [['lang.XPClass', 'non-existant']],
  #  [[null, 'method']], [[new \lang\Object(), 'non-existant']]
  #])]
  public function cannot_create_instances_from($value) {
    (new FunctionType([], Type::$VAR))->newInstance($value);
  }

  #[@test]
  public function can_assign_to_itself() {
    $type= new FunctionType([Type::$VAR], Type::$VAR);
    $this->assertTrue($type->isAssignableFrom($type));
  }

  #[@test]
  public function cannot_assign_if_number_of_arguments_smaller() {
    $type= new FunctionType([Type::$VAR], Type::$VAR);
    $this->assertFalse($type->isAssignableFrom(new FunctionType([], Type::$VAR)));
  }

  #[@test]
  public function cannot_assign_if_number_of_arguments_larger() {
    $type= new FunctionType([Type::$VAR], Type::$VAR);
    $this->assertFalse($type->isAssignableFrom(new FunctionType([Type::$VAR, Type::$VAR], Type::$VAR)));
  }

  #[@test]
  public function cannot_assign_if_return_type_not_assignable() {
    $type= new FunctionType([], Primitive::$STRING);
    $this->assertFalse($type->isAssignableFrom(new FunctionType([], Type::$VOID)));
  }

  #[@test]
  public function signature_matching() {
    $type= new FunctionType([Type::$VAR], Type::$VAR);
    $this->assertTrue($type->isAssignableFrom(new FunctionType([Primitive::$STRING], Type::$VAR)));
  }
}
