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


$t->comment('Expected values don\'t need to be retrieved in the same order');

  // fixtures
  $l = new lime_expectation_list($mockTest = new mock_lime_test());
  // test
  $l->addExpected(1);
  $l->addExpected(3);
  $l->addExpected(2);
  $l->addActual(2);
  $l->addActual(3);
  $l->addActual(1);
  $l->verify();
  // assertions
  $t->is($mockTest->passes, 1, 'One test passed');
  $t->is($mockTest->fails, 0, 'No test failed');


$t->comment('Calling ->setFailOnVerify() results in no exceptions if the order is incorrect');

  // fixtures
  $l = new lime_expectation_list(new mock_lime_test());
  // test
  $l->setFailOnVerify();
  $l->addExpected(1);
  $l->addExpected(2);
  try
  {
    $l->addActual(2);
    $t->pass('No "lime_expectation_exception" is thrown');
  }
  catch (lime_expectation_exception $e)
  {
    $t->fail('No "lime_expectation_exception" is thrown');
  }

