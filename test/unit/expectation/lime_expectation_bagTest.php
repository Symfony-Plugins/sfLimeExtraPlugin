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


$t = new lime_test_simple(6, new lime_output_color());


// @Before

  $mockTest = new mock_lime_test();
  $b = new lime_expectation_bag($mockTest);


// @After

  $mockTest = null;
  $b = null;


// @Test: Expected values can be added in any order

  // test
  $b->addExpected(1);
  $b->addExpected(3);
  $b->addExpected(2);
  $b->addActual(2);
  $b->addActual(3);
  $b->addActual(1);
  $b->verify();
  // assertions
  $t->is($mockTest->passes, 1, 'One test passed');
  $t->is($mockTest->fails, 0, 'No test failed');


// @Test: Exceptions are thrown if unexpected values are added

  // fixtures
  $b->addExpected(1);
  $t->expect('lime_expectation_exception');
  // test
  $b->addActual(2);


// @Test: Exceptions are thrown if expected values are added too often

  // fixtures
  $b->addExpected(1);
  $b->addActual(1);
  $t->expect('lime_expectation_exception');
  // test
  $b->addActual(1);


// @Test: setFailOnVerify() suppresses exceptions

  // test
  $b->setFailOnVerify();
  $b->addExpected(1);
  $b->addActual(1);
  $b->addActual(1);
  $b->verify();
  // assertions
  $t->is($mockTest->passes, 0, 'No test passed');
  $t->is($mockTest->fails, 1, 'One test failed');

