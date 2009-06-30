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


$t = new lime_test_simple(4, new lime_output_color());


// @Before

  $mockTest = new mock_lime_test();
  $l = new lime_expectation_list($mockTest);


// @After

  $mockTest = null;
  $l = null;


// @Test: Exceptions are thrown if unexpected values are added

  // fixture
  $l->addExpected(1);
  $l->addExpected(2);
  $t->expect('lime_expectation_exception');
  // test
  $l->addActual(2);


// @Test: Exceptions are thrown if expected values are added too often

  // fixture
  $l->addExpected(1);
  $l->addActual(1);
  $t->expect('lime_expectation_exception');
  // test
  $l->addActual(1);


// @Test: setFailOnVerify() suppresses exceptions

  // test
  $l->setFailOnVerify();
  $l->addExpected(1);
  $l->addActual(1);
  $l->addActual(1);
  $l->verify();
  // assertions
  $t->is($mockTest->passes, 0, 'No test passed');
  $t->is($mockTest->fails, 1, 'One test failed');
