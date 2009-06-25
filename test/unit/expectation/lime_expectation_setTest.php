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


$t = new lime_test(6, new lime_output_color());


$t->comment('Expected values can be added in any order');

  // fixtures
  $s = new lime_expectation_set($mockTest = new mock_lime_test());
  // test
  $s->addExpected(1);
  $s->addExpected(3);
  $s->addExpected(2);
  $s->addActual(2);
  $s->addActual(3);
  $s->addActual(1);
  $s->verify();
  // assertions
  $t->is($mockTest->passes, 1, 'One test passed');
  $t->is($mockTest->fails, 0, 'No test failed');


$t->comment('Expected values can be added any number of times');

  // fixtures
  $s = new lime_expectation_set($mockTest = new mock_lime_test());
  // test
  $s->addExpected(1);
  $s->addActual(1);
  $s->addActual(1);
  $s->verify();
  // assertions
  $t->is($mockTest->passes, 1, 'One test passed');
  $t->is($mockTest->fails, 0, 'No test failed');


$t->comment('->setFailOnVerify() results in exceptions if unexpected values are added');

  // fixtures
  $s = new lime_expectation_set(new mock_lime_test());
  // test
  $s->setFailOnVerify();
  $s->addExpected(1);
  try
  {
    $s->addActual(2);
    $t->fail('A "lime_expectation_exception" is thrown');
  }
  catch (lime_expectation_exception $e)
  {
    $t->pass('A "lime_expectation_exception" is thrown');
  }


$t->comment('->setFailOnVerify() results in no exceptions if the order is incorrect');

  // fixtures
  $s = new lime_expectation_set(new mock_lime_test());
  // test
  $s->setFailOnVerify();
  $s->addExpected(1);
  $s->addExpected(2);
  try
  {
    $s->addActual(2);
    $t->pass('No "lime_expectation_exception" is thrown');
  }
  catch (lime_expectation_exception $e)
  {
    $t->fail('No "lime_expectation_exception" is thrown');
  }

