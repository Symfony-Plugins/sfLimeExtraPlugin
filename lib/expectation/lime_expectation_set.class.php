<?php

/*
 * This file is part of the symfony package.
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * An ordered expectation collection.
 *
 * This implementation of lime_expectation_collection compares expected
 * and actual values taking their order into account.
 *
 * The following example will NOT verify successfully:
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
 * @package    sfLimeExtraPlugin
 * @author     Bernhard Schussek <bschussek@gmail.com>
 * @version    SVN: $Id$
 */
class lime_expectation_set extends lime_expectation_collection
{

  /**
   * The cursor pointing on the currently expected value
   * @var integer
   */
  protected $cursor = 0;

  /**
   * (non-PHPdoc)
   * @see lib/expectation/lime_expectation_collection#addActual($value)
   */
  public function addActual($value)
  {
    if ($this->failOnVerify && $this->expected[$this->cursor] != $value)
    {
      throw new lime_expectation_exception('Unexpected value "'.$value.'"');
    }

    ++$this->cursor;

    parent::addActual($value);
  }

}