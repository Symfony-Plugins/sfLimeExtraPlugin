<?php

/*
 * This file is part of the symfony package.
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * An ordered expectation collection where the amount of elements is not
 * important.
 *
 * This implementation of lime_expectation_collection compares expected
 * and actual values ignoring their order. This class also does not care
 * if a value is added more than once.
 *
 * The following example will verify successfully:
 *
 * <code>
 *   $list = new lime_expectationList($t);
 *   $list->addExpected(1);
 *   $list->addExpected(2);
 *   $list->addActual(2);
 *   $list->addActual(1);
 *   $list->addActual(1);
 *   $list->verify();
 * </code>
 *
 * @package    sfLimeExtraPlugin
 * @author     Bernhard Schussek <bschussek@gmail.com>
 * @version    SVN: $Id$
 */
class lime_expectation_set extends lime_expectation_collection
{

  /**
   * (non-PHPdoc)
   * @see lib/expectation/lime_expectation_collection#isExpected($value)
   */
  protected function isExpected($value)
  {
    return in_array($value, $this->expected);
  }

  /**
   * (non-PHPdoc)
   * @see lib/expectation/lime_expectation_collection#verify()
   */
  public function verify()
  {
    sort($this->actual);
    sort($this->expected);

    $this->actual = array_unique($this->actual);
    $this->expected = array_unique($this->expected);

    parent::verify();
  }

}