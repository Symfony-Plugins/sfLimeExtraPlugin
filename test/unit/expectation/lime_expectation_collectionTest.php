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


class TestExpectationCollection extends lime_expectation_collection
{
  private $isExpected;

  public function __construct(lime_test $test = null, $isExpected = true)
  {
    parent::__construct($test);

    $this->isExpected = $isExpected;
  }

  protected function isExpected($value)
  {
    return $this->isExpected;;
  }

}


$t = new lime_test(21, new lime_output_color());


$t->comment('No value expected, no value retrieved');

  // fixtures
  $l = new TestExpectationCollection($mockTest = new mock_lime_test());
  // test
  $l->verify();
  // assertions
  $t->is($mockTest->passes, 1, 'One test passed');
  $t->is($mockTest->fails, 0, 'No test failed');


$t->comment('One value expected, no value retrieved');

  // fixtures
  $l = new TestExpectationCollection($mockTest = new mock_lime_test());
  // test
  $l->addExpected(1);
  $l->verify();
  // assertions
  $t->is($mockTest->passes, 0, 'No test passed');
  $t->is($mockTest->fails, 1, 'One test failed');


$t->comment('One value expected, one different value retrieved');

  // fixtures
  $l = new TestExpectationCollection($mockTest = new mock_lime_test());
  // test
  $l->addExpected(1);
  $l->addActual(2);
  $l->verify();
  // assertions
  $t->is($mockTest->passes, 0, 'No test passed');
  $t->is($mockTest->fails, 1, 'One test failed');


$t->comment('No expectations are set, added values are ignored');

  // fixtures
  $l = new TestExpectationCollection($mockTest = new mock_lime_test());
  // test
  $l->addActual(1);
  $l->verify();
  // assertions
  $t->is($mockTest->passes, 1, 'One test passed');
  $t->is($mockTest->fails, 0, 'No test failed');


$t->comment('An exception is thrown if an unexpected value is added');

  // fixtures
  $l = new TestExpectationCollection(new mock_lime_test(), false);
  // test
  $l->addExpected('Foo');
  try
  {
    $l->addActual('Bar');
    $t->fail('A "lime_expectation_exception" is thrown');
  }
  catch (lime_expectation_exception $e)
  {
    $t->pass('A "lime_expectation_exception" is thrown');
  }


$t->comment('Exactly no values are expected');

  // fixtures
  $l = new TestExpectationCollection(new mock_lime_test(), false);
  // test
  $l->setExpectNothing();
  try
  {
    $l->addActual('Bar');
    $t->fail('A "lime_expectation_exception" is thrown');
  }
  catch (lime_expectation_exception $e)
  {
    $t->pass('A "lime_expectation_exception" is thrown');
  }


$t->comment('The expected value was added');

  // fixtures
  $l = new TestExpectationCollection($mockTest = new mock_lime_test());
  // test
  $l->addExpected(1);
  $l->addActual(1);
  $l->verify();
  // assertions
  $t->is($mockTest->passes, 1, 'One test passed');
  $t->is($mockTest->fails, 0, 'No test failed');


$t->comment('The list can contain a mix of different types');

  // fixtures
  $l = new TestExpectationCollection($mockTest = new mock_lime_test());
  // test
  $l->addExpected(1);
  $l->addExpected('Foobar');
  $l->addExpected(new stdClass());
  $l->addActual(1);
  $l->addActual('Foobar');
  $l->addActual(new stdClass());
  $l->verify();
  // assertions
  $t->is($mockTest->passes, 1, 'One test passed');
  $t->is($mockTest->fails, 0, 'No test failed');


$t->comment('By default, values are compared with weak typing');

  // fixtures
  $l = new TestExpectationCollection($mockTest = new mock_lime_test());
  // test
  $l->addExpected(1);
  $l->addActual('1');
  $l->verify();
  // assertions
  $t->is($mockTest->passes, 1, 'One test passed');
  $t->is($mockTest->fails, 0, 'No test failed');


$t->comment('If you call setStrict(), values are compared with strict typing - different types');

  // fixtures
  $l = new TestExpectationCollection($mockTest = new mock_lime_test());
  // test
  $l->setStrict();
  $l->addExpected(1);
  $l->addActual('1');
  $l->verify();
  // assertions
  $t->is($mockTest->passes, 0, 'No test passed');
  $t->is($mockTest->fails, 1, 'One test failed');


$t->comment('If you call setStrict(), values are compared with strict typing - same types');

  // fixtures
  $l = new TestExpectationCollection($mockTest = new mock_lime_test());
  // test
  $l->setStrict();
  $l->addExpected(1);
  $l->addActual(1);
  $l->verify();
  // assertions
  $t->is($mockTest->passes, 1, 'One test passed');
  $t->is($mockTest->fails, 0, 'No test failed');


$t->comment('Calling verify() results in an exception if no test is set');

  // fixtures
  $l = new TestExpectationCollection();
  // test
  try
  {
    $l->verify();
    $t->fail('A "BadMethodCallException" is thrown');
  }
  catch (BadMethodCallException $e)
  {
    $t->pass('A "BadMethodCallException" is thrown');
  }
