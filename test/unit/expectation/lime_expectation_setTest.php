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


$t = new lime_test(3, new lime_output_color());


$t->comment('Expected values need to be retrieved in the same order');

  // fixtures
  $l = new lime_expectation_set($mockTest = new mock_lime_test());
  // test
  $l->addExpected(1);
  $l->addExpected(2);
  $l->addActual(2);
  $l->addActual(1);
  $l->verify();
  // assertions
  $t->is($mockTest->passes, 0, 'No test passed');
  $t->is($mockTest->fails, 1, 'One test failed');


$t->comment('Calling ->setFailOnVerify() results in exceptions once invalid values are added');

  // fixtures
  $l = new lime_expectation_set(new mock_lime_test());
  // test
  $l->setFailOnVerify();
  $l->addExpected(1);
  $l->addExpected(2);
  try
  {
    $l->addActual(2);
    $t->fail('A "lime_expectation_exception" is thrown');
  }
  catch (lime_expectation_exception $e)
  {
    $t->pass('A "lime_expectation_exception" is thrown');
  }
