<?php

/*
 * This file is part of the symfony package.
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * An unordered expectation collection where the amount of elements is important.
 *
 * This implementation of lime_expectation_collection compares expected
 * and actual values ignoring their order. It is important though how often
 * a value was expected and added.
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
 * The following other example will not verify:
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
class lime_expectation_bag extends lime_expectation_collection
{

  /**
   * (non-PHPdoc)
   * @see lib/expectation/lime_expectation_collection#addActual($value)
   */
  public function addActual($value)
  {
    if ($this->failOnVerify)
    {
      $actual = $this->count($value, $this->actual);
      $expected = $this->count($value, $this->expected);

      if ($expected == 0 || $actual >= $expected)
      {
        throw new lime_expectation_exception('Unexpected value "'.$value.'"');
      }
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

  /**
   * Counts how often the given value occurs in the given array.
   * @param array $array
   * @param $value
   * @return unknown_type
   */
  private function count($value, array $array)
  {
    $amount = 0;

    for ($i = 0; $i < count($array); ++$i)
    {
      if ($this->strict ? $array[$i] === $value : $array[$i] == $value)
      {
        ++$amount;
      }
    }

    return $amount;
  }

}