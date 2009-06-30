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


$t = new lime_test_simple(7, new lime_output_color());


// @Before

  $mockTest = new mock_lime_test();
  $s = new lime_expectation_set($mockTest);


// @After

  $mockTest = null;
  $s = null;


// @Test: Expected values can be added in any order

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


// @Test: Expected values can be added any number of times

  // test
  $s->addExpected(1);
  $s->addActual(1);
  $s->addActual(1);
  $s->verify();
  // assertions
  $t->is($mockTest->passes, 1, 'One test passed');
  $t->is($mockTest->fails, 0, 'No test failed');


// @Test: Exceptions are thrown if unexpected values are added

  // fixture
  $s->addExpected(1);
  $t->expect('lime_expectation_exception');
  // test
  $s->addActual(2);


// @Test: setFailOnVerify() suppresses exceptions

  // test
  $s->setFailOnVerify();
  $s->addExpected(1);
  $s->addActual(2);
  $s->verify();
  // assertions
  $t->is($mockTest->passes, 0, 'No test passed');
  $t->is($mockTest->fails, 1, 'One test failed');


