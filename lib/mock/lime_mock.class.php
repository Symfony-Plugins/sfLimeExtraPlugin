<?php

/*
 * This file is part of the symfony package.
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Generates mock objects
 *
 * This class generates configurable mock objects based on existing interfaces,
 * classes or virtual (non-existing) class names. You can use it to create
 * objects of classes that you have not implemented yet, or to substitute
 * a class in a test.
 *
 * A mock object is created with the create() method:
 *
 * <code>
 *   $mock = lime_mock::create('MyClass');
 * </code>
 *
 * Initially the mock is in recording mode. In this mode you just make the
 * expected method calls with the expected parameters. You can use modifiers
 * to configure return values or exceptions that should be thrown.
 *
 * <code>
 *   // method "someMethod()" returns "return value" when called with "parameter"
 *   $mock->someMethod('parameter')->returns('return value');
 * </code>
 *
 * Currently the following method modifiers are supported:
 *
 *   * returns(): The value that should be returned by the method
 *   * throws():  The exception name that should be thrown by the method
 *   * times():   The number of times the method should be called. Can be
 *                combined with the other modifiers
 *
 * <code>
 *   // method "add" will be called 5 times and return 3 every time
 *   $mock->add(1, 2)->returns(3)->times(5);
 * </code>
 *
 * Once the recording is over, you must call the method replay() on the mock.
 * After the call to this method, the mock is in replay mode. In this mode, it
 * listens for method calls and returns the results configured before.
 *
 * <code>
 *   $mock = lime_mock::create('MyClass');
 *   $mock->add(1, 2)->returns(3);
 *   $mock->replay();
 *
 *   echo $mock->add(1, 2);
 *   // returns 3
 * </code>
 *
 * This functionality is perfect to substitute real classes by fake
 * implementations.
 *
 * You also have the possibility to find out whether all the configured
 * methods have been called with the right parameters while in replay mode
 * by calling verify(). This method requires a lime_test object to store
 * the results of the tests. The lime_test object must be passed to create()
 * when creating the new mock.
 *
 * <code>
 *   $mock = lime_mock::create('MyClass', $limeTest);
 *   $mock->add(1,2);
 *   $mock->reply();
 *   $mock->add(1);
 *   $mock->verify();
 *
 *   // results in a failing test
 * </code>
 *
 * Usually, configured and actual method parameters are compared with PHP's
 * usual weak typing. If you want to enforce strict typing, you must call
 * the method setStrict() on the mock.
 *
 * <code>
 *   $mock = lime_mock::create('MyClass', $limeTest);
 *   $mock->setStrict();
 *   $mock->doSomething(1);
 *   $mock->replay();
 *   $mock->doSomething('1');
 *   $mock->verify();
 *
 *   // results in a failing test
 * </code>
 *
 * If an unexpected method is called, you usually find that out in the call
 * to verify() that compares all expected method calls with actual method calls.
 * If you want to debug were a certain unexpected method call comes from, you
 * should call setFailOnVerify() on the mock. In this mode an exception is
 * thrown once an unconfigured method is called while in replay mode.
 *
 * As for verify(), setFailOnVerify() requires a lime_test instance to be
 * present.
 *
 * <code>
 *   $mock = lime_mock::create('MyClass', $limeTest);
 *   $mock->doSomething();
 *   $mock->replay();
 *   $mock->doSomethingElse(); // throws a lime_expectation_exception
 * </code>
 *
 * As you have seen, mock objects offer a few methods that cannot be mocked
 * by default. Those are:
 *
 *   * verify()
 *   * replay()
 *   * setStrict()
 *   * setFailOnVerify()
 *
 * If you need to mock any of these methods, you need to set the third
 * parameter $generateMethods to false when calling create(). Instead of calling
 * these methods on the mock, you will need to call them statically in lime_mock
 * and need to pass the mock as first argument.
 *
 * <code>
 *   $mock = lime_mock::create('MyClass', $limeTest, false);
 *   $mock->replay()->returns('Response of replay()');
 *   lime_mock::replay($mock);
 *
 *   echo $mock->replay();
 *   // echos "Response of replay()"
 * </code>
 *
 * @package    sfLimeExtraPlugin
 * @author     Bernhard Schussek <bschussek@gmail.com>
 * @version    SVN: $Id$
 *
 */
class lime_mock
{

  /**
   * A template for overridden abstract methods in base classes/interfaces.
   * @var string
   */
  protected static $methodTemplate = '%s function %s() { $args = func_get_args(); return $this->__call(%s, $args); }';

  /**
   * Creates a new mock object for the given class or interface name.
   *
   * The class/interface does not necessarily have to exist. Each mock object
   * generated with lime_mock::create($class) fulfills the condition
   * ($mock instanceof $class).
   *
   * If you want to verify this object, you need to pass a lime_test instance
   * as well. Use the third parameter $generateMethods to suppress the generation
   * of the magic methods replay(), verify() etc. See the description of this
   * class for more information.
   *
   * @param  string     $classOrInterface  The (non-)existing class/interface
   *                                       you want to mock
   * @param  lime_test  $test              The test instance
   * @param  bool       $generateMethods   Whether magic methods should be generated
   * @return lime_mock_interface           The mock object
   */
  public static function create($classOrInterface, lime_test $test = null, $generateMethods = true)
  {
    $methods = '';

    if (!class_exists($classOrInterface, false) && !interface_exists($classOrInterface))
    {
      eval(sprintf('interface %s {}', $classOrInterface));
    }

    $class = new ReflectionClass($classOrInterface);
    foreach ($class->getMethods() as $method)
    {
      /* @var $method ReflectionMethod */
      $modifiers = Reflection::getModifierNames($method->getModifiers());
      $modifiers = array_diff($modifiers, array('abstract'));
      $modifiers = implode(' ', $modifiers);
      $methods .= sprintf(self::$methodTemplate, $modifiers, $method->getName(), $method->getName());
    }

    $interfaces = array();

    $name = self::generateName($class->getName());

    $declaration = 'class '.$name;

    if ($class->isInterface())
    {
      $interfaces[] = $class->getName();
    }
    else
    {
      $declaration .= ' extends '.$class->getName();
    }

    if ($generateMethods)
    {
      $interfaces[] = 'lime_mock_interface';
    }

    if (count($interfaces) > 0)
    {
      $declaration .= ' implements '.implode(', ', $interfaces);
    }

    $template = new lime_mock_template(dirname(__FILE__).'/template/mocked_class.tpl');

    eval($template->render(array(
      'class_declaration'   =>  $declaration,
      'methods'             =>  $methods,
      'generate_methods'    =>  $generateMethods,
    )));

    return new $name($test);
  }

  /**
   * Generates a mock class name for the given original class/interface name.
   * @param  string $originalName
   * @return string
   */
  protected static function generateName($originalName)
  {
    while (!isset($name) || class_exists($name, false))
    {
      // inspired by PHPUnit_Framework_MockObject_Generator
      $name = 'Mock_'.$originalName.'_'.substr(md5(microtime()), 0, 8);
    }

    return $name;
  }

  /**
   * Turns the given mock into replay mode.
   * @param  $mock
   */
  public static function replay($mock)
  {
    return $mock->__lime_getControl()->replay();
  }

  /**
   * Sets the given mock to compare method parameters with strict typing.
   * @param  $mock
   */
  public static function setStrict($mock)
  {
    return $mock->__lime_getControl()->setStrict();
  }

  /**
   * Configures the mock to throw an exception when an unexpected method call
   * is made.
   *
   * @param  $mock                       The mock bject
   * @throws lime_expectation_exception  When an unexpected method is called
   */
  public static function setFailOnVerify($mock)
  {
    return $mock->__lime_getControl()->setFailOnVerify();
  }

  /**
   * Configures the mock to expect no method call.
   */
  public static function setExpectNothing()
  {
    return $mock->__lime_getControl()->setExpectNothing();
  }

}


