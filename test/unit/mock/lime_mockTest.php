<?php

/*
 * This file is part of the symfony package.
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

include dirname(__FILE__).'/../../bootstrap/unit.php';
include dirname(__FILE__).'/../mock_lime_test.class.php';


interface TestInterface
{
  public function testMethod();
}

abstract class TestClassAbstract
{
  abstract public function testMethod();
}

class TestClass
{
  public static $calls = 0;

  public function testMethod()
  {
    ++self::$calls;
  }
}

class TestException extends Exception {}


$t = new lime_test(46, new lime_output_color());


$t->comment('::create() creates a new mock object for an interface');

  // test
  $m = lime_mock::create('TestInterface');
  // assertions
  $t->ok($m instanceof TestInterface, 'The mock implements the interface');
  $t->ok($m instanceof lime_mock_interface, 'The mock implements "lime_mock_interface"');


$t->comment('::create() creates a new mock object for an abstract class');

  // test
  $m = lime_mock::create('TestClassAbstract');
  // assertions
  $t->ok($m instanceof TestClassAbstract, 'The mock inherits the class');
  $t->ok($m instanceof lime_mock_interface, 'The mock implements "lime_mock_interface"');


$t->comment('::create() creates a new mock object for a non-existing class');

  $m = lime_mock::create('FoobarClass');
  // assertions
  $t->ok($m instanceof FoobarClass, 'The mock generates and inherits the class');
  $t->ok($m instanceof lime_mock_interface, 'The mock implements "lime_mock_interface"');


$t->comment('Calling methods on the mock does not call methods in the original class');

  // fixtures
  TestClass::$calls = 0;
  $m = lime_mock::create('TestClass');
  // test
  $m->testMethod();
  // assertions
  $t->is(TestClass::$calls, 0, 'The method has not been called');


$t->comment('->returns() configures a custom return value for a method');

  // fixtures
  $m = lime_mock::create('TestClass');
  // test
  $m->testMethod()->returns('Foobar');
  $m->replay();
  $value = $m->testMethod();
  // assertions
  $t->is($value, 'Foobar', 'The correct value has been returned');


$t->comment('->returns() can configure different return values for different parameters');

  // fixtures
  $m = lime_mock::create('TestClass');
  // test
  $m->testMethod()->returns('Foobar');
  $m->testMethod(1)->returns('More foobar');
  $m->replay();
  $value1 = $m->testMethod();
  $value2 = $m->testMethod(1);
  // assertions
  $t->is($value1, 'Foobar', 'The correct value has been returned');
  $t->is($value2, 'More foobar', 'The correct value has been returned');


$t->comment('Custom exceptions can be configured using the class name');

  // fixtures
  $m = lime_mock::create('TestClass');
  // test
  $m->testMethod()->throws('TestException');
  $m->replay();
  try
  {
    $m->testMethod();
    $t->fail('The exception has been thrown');
  }
  catch (TestException $e)
  {
    $t->pass('The exception has been thrown');
  }


$t->comment('->verify() throws an exception if the mock has been created without a lime_test');

  // fixtures
  $m = lime_mock::create('TestClass');
  // test
  try
  {
    $m->verify();
    $t->fail('A "BadMethodCallException" is thrown');
  }
  catch (BadMethodCallException $e)
  {
    $t->pass('A "BadMethodCallException" is thrown');
  }


$t->comment('->verify() results in a failed test if a method was not called');

  // fixtures
  $m = lime_mock::create('TestClass', $mockTest = new mock_lime_test());
  // test
  $m->testMethod();
  $m->replay();
  $m->verify();
  // assertions
  $t->is($mockTest->fails, 1, 'One test failed');
  $t->is($mockTest->passes, 0, 'No test passed');


$t->comment('->verify() results in a failed test if a method was not called with different');

  // fixtures
  $m = lime_mock::create('TestClass', $mockTest = new mock_lime_test());
  // test
  $m->testMethod(1, 'Foobar');
  $m->replay();
  $m->testMethod(1);
  $m->verify();
  // assertions
  $t->is($mockTest->fails, 1, 'One test failed');
  $t->is($mockTest->passes, 0, 'No test passed');


$t->comment('->verify() results in a failed test if a method was called with different parameter order');

  // fixtures
  $m = lime_mock::create('TestClass', $mockTest = new mock_lime_test());
  // test
  $m->testMethod(1, 'Foobar');
  $m->replay();
  $m->testMethod('Foobar', 1);
  $m->verify();
  // assertions
  $t->is($mockTest->fails, 1, 'One test failed');
  $t->is($mockTest->passes, 0, 'No test passed');


$t->comment('->verify() results in a succeeding test if one method was called correctly');

  // fixtures
  $m = lime_mock::create('TestClass', $mockTest = new mock_lime_test());
  // test
  $m->testMethod(1, 'Foobar');
  $m->replay();
  $m->testMethod(1, 'Foobar');
  $m->verify();
  // assertions
  $t->is($mockTest->passes, 1, 'One test passed');
  $t->is($mockTest->fails, 0, 'No test failed');


$t->comment('->verify() results in a succeeding test if two methods were called correctly');

  // fixtures
  $m = lime_mock::create('TestClass', $mockTest = new mock_lime_test());
  // test
  $m->testMethod1();
  $m->testMethod2('Foobar');
  $m->replay();
  $m->testMethod1();
  $m->testMethod2('Foobar');
  $m->verify();
  // assertions
  $t->is($mockTest->passes, 1, 'One tests passed');
  $t->is($mockTest->fails, 0, 'No test failed');


$t->comment('A method can be expected twice with different parameters - insufficient calls');

  // fixtures
  $m = lime_mock::create('TestClass', $mockTest = new mock_lime_test());
  // test
  $m->testMethod();
  $m->testMethod('Foobar');
  $m->replay();
  $m->testMethod();
  $m->verify();
  // assertions
  $t->is($mockTest->passes, 0, 'No test passed');
  $t->is($mockTest->fails, 1, 'One test failed');


$t->comment('A method can be expected twice with different parameters - sufficient calls');

  // fixtures
  $m = lime_mock::create('TestClass', $mockTest = new mock_lime_test());
  // test
  $m->testMethod();
  $m->testMethod('Foobar');
  $m->replay();
  $m->testMethod();
  $m->testMethod('Foobar');
  $m->verify();
  // assertions
  $t->is($mockTest->passes, 1, 'One test passed');
  $t->is($mockTest->fails, 0, 'No test failed');


$t->comment('Methods may be called in any order');

  // fixtures
  $m = lime_mock::create('TestClass', $mockTest = new mock_lime_test());
  // test
  $m->testMethod1();
  $m->testMethod2();
  $m->replay();
  $m->testMethod2();
  $m->testMethod1();
  $m->verify();
  // assertions
  $t->is($mockTest->passes, 1, 'One test passed');
  $t->is($mockTest->fails, 0, 'No test failed');


$t->comment('If you call setFailOnVerify(), an exception is thrown at the first unexpected call');

  // fixtures
  $m = lime_mock::create('TestClass', new mock_lime_test());
  // test
  $m->setFailOnVerify();
  $m->testMethod();
  $m->replay();
  try
  {
    $m->testMethod('foobar');
    $t->fail('A "lime_expectation_exception" is thrown');
  }
  catch (lime_expectation_exception $e)
  {
    $t->pass('A "lime_expectation_exception" is thrown');
  }


$t->comment('By default, method parameters are compared with weak typing');

  // fixtures
  $m = lime_mock::create('TestClass', $mockTest = new mock_lime_test());
  // test
  $m->testMethod(1);
  $m->replay();
  $m->testMethod('1');
  $m->verify();
  // assertions
  $t->is($mockTest->passes, 1, 'One test passed');
  $t->is($mockTest->fails, 0, 'No test failed');


$t->comment('If you call setStrict(), method parameters are compared with strict typing - different types');

  // fixtures
  $m = lime_mock::create('TestClass', $mockTest = new mock_lime_test());
  // test
  $m->setStrict();
  $m->testMethod(1);
  $m->replay();
  $m->testMethod('1');
  $m->verify();
  // assertions
  $t->is($mockTest->passes, 0, 'No test passed');
  $t->is($mockTest->fails, 1, 'One test failed');


$t->comment('If you call setStrict(), method parameters are compared with strict typing - same type');

  // fixtures
  $m = lime_mock::create('TestClass', $mockTest = new mock_lime_test());
  // test
  $m->setStrict();
  $m->testMethod(1);
  $m->replay();
  $m->testMethod(1);
  $m->verify();
  // assertions
  $t->is($mockTest->passes, 1, 'One test passed');
  $t->is($mockTest->fails, 0, 'No test failed');


$t->comment('->times() specifies how often a method should be called - to few actual calls');

  // fixtures
  $m = lime_mock::create('TestClass', $mockTest = new mock_lime_test());
  // test
  $m->testMethod(1)->times(2);
  $m->replay();
  $m->testMethod(1);
  $m->verify();
  // assertions
  $t->is($mockTest->passes, 0, 'No test passed');
  $t->is($mockTest->fails, 1, 'One test failed');


$t->comment('->times() specifies how often a method should be called - to many actual calls');

  // fixtures
  $m = lime_mock::create('TestClass', $mockTest = new mock_lime_test());
  // test
  $m->testMethod(1)->times(2);
  $m->replay();
  $m->testMethod(1);
  $m->testMethod(1);
  $m->testMethod(1);
  $m->verify();
  // assertions
  $t->is($mockTest->passes, 0, 'No test passed');
  $t->is($mockTest->fails, 1, 'One test failed');


$t->comment('->times() specifies how often a method should be called - wrong parameters');

  // fixtures
  $m = lime_mock::create('TestClass', $mockTest = new mock_lime_test());
  // test
  $m->testMethod(1)->times(2);
  $m->replay();
  $m->testMethod(1);
  $m->testMethod();
  $m->verify();
  // assertions
  $t->is($mockTest->passes, 0, 'No test passed');
  $t->is($mockTest->fails, 1, 'One test failed');


$t->comment('->times() specifies how often a method should be called - correct number of actual calls');

  // fixtures
  $m = lime_mock::create('TestClass', $mockTest = new mock_lime_test());
  // test
  $m->testMethod(1)->times(2);
  $m->replay();
  $m->testMethod(1);
  $m->testMethod(1);
  $m->verify();
  // assertions
  $t->is($mockTest->passes, 1, 'One test passed');
  $t->is($mockTest->fails, 0, 'No test failed');


$t->comment('->times() and ->returns() can be combined');

  // fixtures
  $m = lime_mock::create('TestClass', $mockTest = new mock_lime_test());
  // test
  $m->testMethod(1)->returns('Foobar')->times(2);
  $m->replay();
  $value1 = $m->testMethod(1);
  $value2 = $m->testMethod(1);
  // assertions
  $t->is($value1, 'Foobar', 'The first return value is correct');
  $t->is($value2, 'Foobar', 'The second return value is correct');


$t->comment('The control methods like ->replay() can be mocked');

  // fixtures
  $m = lime_mock::create('TestClass', null, false);
  // test
  $m->replay()->returns('Foobar');
  lime_mock::replay($m);
  $value = $m->replay();
  // assertions
  $t->is($value, 'Foobar', 'The return value was correct');
