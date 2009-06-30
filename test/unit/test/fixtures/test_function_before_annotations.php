<?php

/*
 * This file is part of the symfony package.
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

include dirname(__FILE__).'/../../../bootstrap/unit.php';

function testFunctionDefinition($param1, $param2 = null)
{
  function testNestedFunctionDefinition() {}
}

function testFunctionDefinitionInOneLine() {}

$t = new lime_test_simple(0);

// @Test
echo "Test\n";
