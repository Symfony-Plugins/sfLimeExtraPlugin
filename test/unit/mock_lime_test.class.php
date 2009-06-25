<?php

/*
 * This file is part of the symfony package.
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Mimics the behaviour of lime_test for testing.
 *
 * The public properties $fails and $passes give you information about how
 * often a fail/pass was reported to this test instance.
 *
 * @package    sfLimeExtraPlugin
 * @author     Bernhard Schussek <bschussek@gmail.com>
 * @version    SVN: $Id$
 */
class mock_lime_test extends lime_test
{
  /**
   * The number of reported failing tests
   * @var integer
   */
  public $fails = 0;

  /**
   * The number of reported passing tests
   * @var integer
   */
  public $passes = 0;

  /**
   * Constructor.
   */
  public function __construct()
  {
    parent::__construct(0, new lime_output_silent());
  }

  /**
   * @see parent::ok()
   */
  public function ok($condition)
  {
    if (!$condition)
    {
      ++$this->fails;
    }
    else
    {
      ++$this->passes;
    }
  }

}
