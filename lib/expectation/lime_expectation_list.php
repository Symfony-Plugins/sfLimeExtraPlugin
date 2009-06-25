<?php

/*
 * This file is part of the symfony package.
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * An ordered expectation collection where the amount of elements is important.
 *
 * This implementation of lime_expectation_collection compares expected
 * and actual values taking their order into account. It is also important how
 * often a value was expected and added.
 *
 * The following example will not verify successfully:
 *
 * <code>
 *   $list = new lime_expectationList($t);
 *   $list->addExpected(1);
 *   $list->addExpected(2);
 *   $list->addActual(2);
 *   $list->addActual(1);
 *   $list->verify();
 * </code>
 *
 * The following other example will not verify either:
 *
 * <code>
 *   $list = new lime_expectationList($t);
 *   $list->addExpected(1);
 *   $list->addActual(1);
 *   $list->addActual(1);
 *   $list->verify();
 * </code>
 *
 * @package    sfLimeExtraPlugin
 * @author     Bernhard Schussek <bschussek@gmail.com>
 * @version    SVN: $Id$
 */
class lime_expectation_list extends lime_expectation_collection
{

  /**
   * The cursor pointing on the currently expected value
   * @var integer
   */
  protected $cursor = 0;

  /**
   * (non-PHPdoc)
   * @see lib/expectation/lime_expectation_collection#isExpected($value)
   */
  protected function isExpected($value)
  {
    return $this->expected[$this->cursor++] == $value;
  }

}