<?php

/*
 * This file is part of the symfony package.
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

include dirname(__FILE__).'/../../bootstrap/unit.php';


class Test extends lime_test
{

  public function is_output($actual, $expected, $method='is')
  {
    $this->$method(trim($actual), trim($expected), 'The test file returns the expected output');
  }
}


$t = new Test(19, new lime_output_color());

// misuse harness to find PHP cli
$h = new lime_harness(new lime_output_silent());
$cli = $h->php_cli.' ';


function execute($file)
{
  global $cli;

  ob_start();
  passthru($cli.' '.dirname(__FILE__).'/fixture/'.$file, $result);
  $content = ob_get_clean();

  return array($result, $content);
}


$t->comment('Code annotated with @Before is executed once before every test');

  // test
  list($result, $actual) = execute('test_before.php');
  // assertion
  $expected = <<<EOF
1..0
Before
Test 1
Before
Test 2
 Looks like everything went fine.
EOF;
  $t->is($result, 0, 'The file returned exit status 0 (success)');
  $t->is_output($actual, $expected);


$t->comment('Code annotated with @After is executed once after every test');

  // test
  list($result, $actual) = execute('test_after.php');
  // assertion
  $expected = <<<EOF
1..0
Test 1
After
Test 2
After
 Looks like everything went fine.
EOF;
  $t->is($result, 0, 'The file returned exit status 0 (success)');
  $t->is_output($actual, $expected);


$t->comment('Code annotated with @BeforeAll is executed once before the test suite');

  // test
  list($result, $actual) = execute('test_before_all.php');
  // assertion
  $expected = <<<EOF
1..0
Before All
Test 1
Test 2
 Looks like everything went fine.
EOF;
  $t->is($result, 0, 'The file returned exit status 0 (success)');
  $t->is_output($actual, $expected);


$t->comment('Code annotated with @AfterAll is executed once after the test suite');

  // test
  list($result, $actual) = execute('test_after_all.php');
  // assertion
  $expected = <<<EOF
1..0
Test 1
Test 2
After All
 Looks like everything went fine.
EOF;
  $t->is($result, 0, 'The file returned exit status 0 (success)');
  $t->is_output($actual, $expected);


$t->comment('Code before the first annotations is executed normally');

  // test
  list($result, $actual) = execute('test_code_before_annotations.php');
  // assertion
  $expected = <<<EOF
1..0
Before annotation
Before
Test
 Looks like everything went fine.
EOF;
  $t->is($result, 0, 'The file returned exit status 0 (success)');
  $t->is_output($actual, $expected);


$t->comment('Unknown annotations result in exceptions');

  // test
  list($result, $actual) = execute('test_ignore_unknown.php');
  // assertion
  $t->is($result, 255, 'The file returned exit status 255 (dubious)');


$t->comment('Variables from the @Before scope are available in all other scopes');

  // test
  list($result, $actual) = execute('test_scope_before.php');
  // assertion
  $expected = <<<EOF
1..0
Before
BeforeTest
BeforeTestAfter
 Looks like everything went fine.
EOF;
  $t->is($result, 0, 'The file returned exit status 0 (success)');
  $t->is_output($actual, $expected);


$t->comment('Variables from the global scope are available in all other scopes');

  // test
  list($result, $actual) = execute('test_scope_global.php');
  // assertion
  $expected = <<<EOF
1..0
Global
GlobalBefore
GlobalBeforeTest
GlobalBeforeTestAfter
 Looks like everything went fine.
EOF;
  $t->is($result, 0, 'The file returned exit status 0 (success)');
  $t->is_output($actual, $expected);


$t->comment('Tests annotated with @Test may have comments');

  // test
  list($result, $actual) = execute('test_comments.php');
  // assertion
  $expected = <<<EOF
1..0
Test 1
# This test is commented
Test 2
 Looks like everything went fine.
EOF;
  $t->is($result, 0, 'The file returned exit status 0 (success)');
  $t->is_output($actual, $expected);


$t->comment('Exceptions can be expected');

  // test
  list($result, $actual) = execute('test_expect.php');
  // assertion
  $expected = '/'.str_replace(array('%ANY%', '%EXCEPTION%'), array('.*', '"?RuntimeException"?'), preg_quote(<<<EOF
1..2
Test 1
not ok 1 - A %EXCEPTION% was thrown
#     Failed test (%ANY%)
#            got: NULL
#       expected: 'RuntimeException'
Test 2
ok 2 - A %EXCEPTION% was thrown
 Looks like you failed 1 tests of 2.
EOF
)).'/';
  $t->is($result, 0, 'The file returned exit status 0 (success)');
  $t->is_output($actual, $expected, 'like');



