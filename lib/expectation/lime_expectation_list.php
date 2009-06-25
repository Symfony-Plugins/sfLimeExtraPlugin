<?php

/*
 * This file is part of the symfony package.
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * An unordered expectation collection.
 *
 * This implementation of lime_expectation_collection compares expected
 * and actual values regardless of their order.
 *
 * The following example will verify successfully:
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
class lime_expectation_list extends lime_expectation_collection
{

  /**
   * (non-PHPdoc)
   * @see lib/expectation/lime_expectation_collection#addActual($value)
   */
  public function addActual($value)
  {
    if ($this->failOnVerify && !in_array($value, $this->expected))
    {
      throw new lime_expectation_exception('Unexpected value "'.$value.'"');
    }

    parent::addActual($value);
  }

  /**
   * (non-PHPdoc)
   * @see lib/expectation/lime_expectation_collection#verify()
   */
  public function verify()
  {
    sort($this->actual);
    sort($this->expected);

    parent::verify();
  }

}