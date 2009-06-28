<?php

/*
 * This file is part of the symfony package.
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

include dirname(__FILE__).'/../../../bootstrap/unit.php';

$t = new lime_test_simple(0);

$var = "Global";
echo $var."\n";

// @Before
$var .= "Before";
echo $var."\n";

// @Test
$var .= "Test";
echo $var."\n";

// @After
$var .= "After";
echo $var."\n";