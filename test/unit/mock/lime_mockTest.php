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


$t->comment('Interfaces can be mocked');

  // test
  $m = lime_mock::create('TestInterface');
  // assertions
  $t->ok($m instanceof TestInterface, 'The mock implements the interface');
  $t->ok($m instanceof lime_mock_interface, 'The mock implements "lime_mock_interface"');


$t->comment('Abstract classes can be mocked');

  // test
  $m = lime_mock::create('TestClassAbstract');
  // assertions
  $t->ok($m instanceof TestClassAbstract, 'The mock inherits the class');
  $t->ok($m instanceof lime_mock_interface, 'The mock implements "lime_mock_interface"');


$t->comment('Non-existing classes can be mocked');

  $m = lime_mock::create('FoobarClass');
  // assertions
  $t->ok($m instanceof FoobarClass, 'The mock generates and inherits the class');
  $t->ok($m instanceof lime_mock_interface, 'The mock implements "lime_mock_interface"');


$t->comment('Methods in the mocked class are not called');

  // fixtures
  TestClass::$calls = 0;
  $m = lime_mock::create('TestClass');
  // test
  $m->testMethod();
  // assertions
  $t->is(TestClass::$calls, 0, 'The method has not been called');


$t->comment('Return values can be stubbed');

  // fixtures
  $m = lime_mock::create('TestClass');
  // test
  $m->testMethod()->returns('Foobar');
  $m->replay();
  $value = $m->testMethod();
  // assertions
  $t->is($value, 'Foobar', 'The correct value has been returned');


$t->comment('Return values can be stubbed based on method parameters');

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


$t->comment('Exceptions can be stubbed');

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


$t->comment('->verify() fails if a method was not called');

  // fixtures
  $m = lime_mock::create('TestClass', $mockTest = new mock_lime_test());
  // test
  $m->testMethod();
  $m->replay();
  $m->verify();
  // assertions
  $t->is($mockTest->fails, 1, 'One test failed');
  $t->is($mockTest->passes, 0, 'No test passed');


$t->comment('->verify() fails if a method was called with wrong parameters');

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


$t->comment('->verify() fails if a method was called with the right parameters in a wrong order');

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


$t->comment('->verify() passes if a method was called correctly');

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


$t->comment('->verify() passes if two methods were called correctly');

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


$t->comment('A method can be expected twice with different parameters');

  $t->comment('Case 1: Insufficient method calls');

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


  $t->comment('Case 2: Sufficient method calls');

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


$t->comment('If you call setFailOnVerify(), an exception is thrown at the first unexpected method call');

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


$t->comment('If you call setStrict(), method parameters are compared with strict typing');

  $t->comment('Case 1: Type comparison fails');

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


  $t->comment('Case 2: Type comparison passes');

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


$t->comment('->times() configures how often a method should be called');

  $t->comment('Case 1: The method is called too seldom');

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


  $t->comment('Case 2: The method is called too often');

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


  $t->comment('Case 3: The number of method calls matches times()');

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


  $t->comment('Case 4: The method is called with different parameters');

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
