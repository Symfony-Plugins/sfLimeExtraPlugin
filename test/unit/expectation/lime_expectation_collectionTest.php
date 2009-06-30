<?php

/*
 * This file is part of the symfony package.
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

include dirname(__FILE__).'/../../bootstrap/unit.php';
require_once dirname(__FILE__).'/../mock_lime_test.class.php';


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


$t = new lime_test_simple(21, new lime_output_color());


// @Before

  $mockTest = new mock_lime_test();
  $l = new TestExpectationCollection($mockTest);


// @After

  $mockTest = null;
  $l = null;


// @Test: No value expected, no value retrieved

  // test
  $l->verify();
  // assertions
  $t->is($mockTest->passes, 1, 'One test passed');
  $t->is($mockTest->fails, 0, 'No test failed');


// @Test: One value expected, no value retrieved

  // test
  $l->addExpected(1);
  $l->verify();
  // assertions
  $t->is($mockTest->passes, 0, 'No test passed');
  $t->is($mockTest->fails, 1, 'One test failed');


// @Test: One value expected, one different value retrieved

  // test
  $l->addExpected(1);
  $l->addActual(2);
  $l->verify();
  // assertions
  $t->is($mockTest->passes, 0, 'No test passed');
  $t->is($mockTest->fails, 1, 'One test failed');


// @Test: No expectations are set, added values are ignored

  // test
  $l->addActual(1);
  $l->verify();
  // assertions
  $t->is($mockTest->passes, 1, 'One test passed');
  $t->is($mockTest->fails, 0, 'No test failed');


// @Test: An exception is thrown if an unexpected value is added

  // fixtures
  $l = new TestExpectationCollection(new mock_lime_test(), false);
  $l->addExpected('Foo');
  $t->expect('lime_expectation_exception');
  // test
  $l->addActual('Bar');


// @Test: Exactly no values are expected

  // fixture
  $l = new TestExpectationCollection(new mock_lime_test(), false);
  $l->setExpectNothing();
  $t->expect('lime_expectation_exception');
  // test
  $l->addActual('Bar');


// @Test: The expected value was added

  // test
  $l->addExpected(1);
  $l->addActual(1);
  $l->verify();
  // assertions
  $t->is($mockTest->passes, 1, 'One test passed');
  $t->is($mockTest->fails, 0, 'No test failed');


// @Test: The list can contain a mix of different types

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


// @Test: By default, values are compared with weak typing

  // fixtures
  $l = new TestExpectationCollection($mockTest = new mock_lime_test());
  // test
  $l->addExpected(1);
  $l->addActual('1');
  $l->verify();
  // assertions
  $t->is($mockTest->passes, 1, 'One test passed');
  $t->is($mockTest->fails, 0, 'No test failed');


// @Test: If you call setStrict(), values are compared with strict typing - different types

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


// @Test: If you call setStrict(), values are compared with strict typing - same types

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


// @Test: Calling verify() results in an exception if no test is set

  // fixtures
  $l = new TestExpectationCollection();
  $t->expect('BadMethodCallException');
  // test
  $l->verify();
