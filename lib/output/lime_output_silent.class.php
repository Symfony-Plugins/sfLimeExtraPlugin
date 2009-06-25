<?php

/*
 * This file is part of the symfony package.
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class lime_output_silent extends lime_output
{

  public function diag() {}

  public function comment($message) {}

  public function info($message) {}

  public function error($message) {}

  public function echoln($message) {}

  public function green_bar($message) {}

  public function red_bar($message) {}

}