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


$t = new lime_test(5, new lime_output_color());


$t->comment('Expected values can be added in any order');

  // fixtures
  $b = new lime_expectation_bag($mockTest = new mock_lime_test());
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


$t->comment('->setFailOnVerify() results in no exceptions if the order is incorrect');

  // fixtures
  $b = new lime_expectation_bag(new mock_lime_test());
  // test
  $b->setFailOnVerify();
  $b->addExpected(1);
  $b->addExpected(2);
  try
  {
    $b->addActual(2);
    $t->pass('No "lime_expectation_exception" is thrown');
  }
  catch (lime_expectation_exception $e)
  {
    $t->fail('No "lime_expectation_exception" is thrown');
  }


$t->comment('->setFailOnVerify() results in exceptions if unexpected values are added');

  // fixtures
  $b = new lime_expectation_bag(new mock_lime_test());
  // test
  $b->setFailOnVerify();
  $b->addExpected(1);
  try
  {
    $b->addActual(2);
    $t->fail('A "lime_expectation_exception" is thrown');
  }
  catch (lime_expectation_exception $e)
  {
    $t->pass('A "lime_expectation_exception" is thrown');
  }


$t->comment('->setFailOnVerify() results in exceptions if expected values are added too often');

  // fixtures
  $b = new lime_expectation_bag(new mock_lime_test());
  // test
  $b->setFailOnVerify();
  $b->addExpected(1);
  $b->addActual(1);
  try
  {
    $b->addActual(1);
    $t->fail('A "lime_expectation_exception" is thrown');
  }
  catch (lime_expectation_exception $e)
  {
    $t->pass('A "lime_expectation_exception" is thrown');
  }

