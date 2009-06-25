<?php

/*
 * This file is part of the symfony package.
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

interface lime_verifiable
{

  public function setStrict();

  public function setFailOnVerify();

  public function verify();

}