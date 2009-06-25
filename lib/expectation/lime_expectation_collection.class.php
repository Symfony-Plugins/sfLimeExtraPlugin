<?php

/*
 * This file is part of the symfony package.
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Abstract base type for all expectation collections.
 *
 * You can use expectation collections to test whether certain expectations
 * have been met. You can "feed" the collection with expectations by calling
 * addExpected(). By calling addActual(), you can inform the collection about
 * the actual values. By calling verify() you can check whether the actual
 * values matched the expected ones.
 *
 * By default, this class compares the expected and actual values type
 * insensitive. You can change this behaviour by calling setStrict().
 *
 * Usually you only find out whether any expectations have not been met once
 * you call verify(). For the sake of easier debugging, you can enable this
 * class to throw an exception immediately when an unexpected value is
 * retrieved. You do this by calling setFailOnVerify().
 *
 * In your application you must use one of the concrete subclasses to make
 * use of this functionality.
 *
 * @package    sfLimeExtraPlugin
 * @author     Bernhard Schussek <bschussek@gmail.com>
 * @version    SVN: $Id$
 */
abstract class lime_expectation_collection implements lime_verifiable
{

  /**
   * A reference to a lime_test instance
   * @var lime_test
   */
  protected $test = null;

  /**
   * The array of actual values
   * @var array
   */
  protected $actual = array();

  /**
   * The array of expected values
   * @var array
   */
  protected $expected = array();

  /**
   * Whether the class should fail when it retrieves unexpected values
   * @var bool
   */
  protected $failOnVerify = false;

  /**
   * Whether the comparison between expected and actual values should be
   * type-safe.
   * @var bool
   */
  protected $strict = false;

  public function __construct(lime_test $test = null)
  {
    $this->test = $test;
  }

  /**
   * (non-PHPdoc)
   * @see lib/lime_verifiable#setFailOnVerify()
   */
  public function setFailOnVerify()
  {
    if (is_null($this->test))
    {
      throw new BadMethodCallException("A lime_test object is required for verification");
    }

    $this->failOnVerify = true;
  }

  /**
   * (non-PHPdoc)
   * @see lib/lime_verifiable#setStrict()
   */
  public function setStrict()
  {
    $this->strict = true;
  }

  /**
   * (non-PHPdoc)
   * @see lib/lime_verifiable#verify()
   */
  public function verify()
  {
    if (is_null($this->test))
    {
      throw new BadMethodCallException("A lime_test object is required for verification");
    }

    if ($this->strict)
    {
      $this->test->ok($this->actual === $this->expected, 'The expected values have been set');
    }
    else
    {
      $this->test->ok($this->actual == $this->expected, 'The expected values have been set');
    }
  }

  /**
   * Adds an expected value to the collection.
   *
   * @param $value
   */
  public function addExpected($value)
  {
    $this->expected[] = $value;
  }

  /**
   * Adds an actual value to the collection.
   *
   * @param $value
   */
  public function addActual($value)
  {
    $this->actual[] = $value;
  }

}