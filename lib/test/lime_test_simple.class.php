<?php

/*
 * This file is part of the symfony package.
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Extends lime_test to support annotations in test files.
 *
 * With this extension of lime_test, you can write very simple test files that
 * support more features than regular lime, such as code executed before
 * or after each test, code executed before or after the whole test suite
 * or expected exceptions.
 *
 * A test file can be written like this with lime_test_simple:
 *
 * <code>
 * <?php
 *
 * include dirname(__FILE__).'/../bootstrap/unit.php';
 *
 * $t = new lime_test_simple(2, new lime_output_color());
 *
 * // @Before
 * $r = new Record();
 *
 * // @Test
 * $r->setValue('Bumblebee');
 * $t->is($r->getValue(), 'Bumblebee', 'The setter works');
 *
 * // @Test
 * $t->is($r->getValue(), 'Foobar', 'The value is "Foobar" by default');
 * </code>
 *
 * The available annotations are:
 *
 *   * @BeforeAll  Executed before the whole test suite
 *   * @Before     Executed before each test
 *   * @After      Executed after each test
 *   * @AfterAll   Executed after the whole test suite
 *   * @Test       A test case
 *
 * You can add comments to the annotations that will be printed in the console:
 *
 * <code>
 * // @Test: The record supports setValue()
 * $r->setValue('Bumblebee')
 * // etc.
 * </code>
 *
 * You can also automatically test that certain exceptions are thrown from
 * within a test. To do that, you must call the method ->expect() on the
 * lime_test_simple object '''before''' executing the test that should throw
 * an exception.
 *
 * <code>
 * // @Test
 * $r->expect('RuntimeException');
 * throw new RuntimeException();
 *
 * // results in a passed test
 * </code>
 *
 * @package    sfLimeExtraPlugin
 * @author     Bernhard Schussek <bschussek@gmail.com>
 * @version    SVN: $Id: lime_mock.class.php 19555 2009-06-25 15:24:14Z bschussek $
 */
class lime_test_simple extends lime_test
{
  /**
   * The path of the executed test script
   * @var string
   */
  protected $path;

  /**
   * The path where the backup of the test script is stored
   * @var string
   */
  protected $backupPath;

  /**
   * The functions annotated with @BeforeAll
   * @var array
   */
  protected $beforeAllFunctions = array();

  /**
   * The functions annotated with @Before
   * @var array
   */
  protected $beforeFunctions = array();

  /**
   * The functions annotated with @Test
   * @var array
   */
  protected $testFunctions = array();

  /**
   * The functions annotated with @After
   * @var array
   */
  protected $afterFunctions = array();

  /**
   * The functions annotated with @AfterAll
   * @var array
   */
  protected $afterAllFunctions = array();

  /**
   * All variables known to the test file
   * @var array
   */
  protected $variables = array();

  /**
   * The variable pointing to the instance of lime_test_simple
   * @var string
   */
  protected $testVariable = '';

  /**
   * The expected exception of the currently executed test method
   * @var string
   */
  protected $expectedException = null;

  /**
   * Constructor.
   *
   * @see lime_test::__construct()
   */
  public function __construct($plan = null, $output_instance = null)
  {
    parent::__construct($plan, $output_instance);

    register_shutdown_function(array($this, 'shutdown'));

    $this->run();

    exit;
  }

  /**
   * Transforms and runs the annotated test script.
   */
  protected function run()
  {
    $this->path = $this->getScriptPath();
    $this->backupPath = $this->path.'.bak';

    rename($this->path, $this->backupPath);

    $this->parse();
    $this->execute();
  }

  /**
   * Transforms the annotated test script into an executable script.
   *
   * All code wrapped in annotations is wrapped into functions automatically.
   */
  protected function parse()
  {
    $content = file_get_contents($this->backupPath);
    $file = fopen($this->path, 'w');
    $inFunctionBlock = false;

    // collect variables
    if (preg_match_all('/\$\w+/', $content, $matches))
    {
      $this->variables = array_diff(array_unique(array_merge($this->variables, $matches[0])), array('$this'));
    }

    // comment classes, interfaces and functions out
    if (preg_match_all('/(((abstract\s+)?class|interface)\s[\w\s]+\s*|function\s+\w+\s*\([^)]*\)\s*)(\{([^{}]*|(?4))*\})/si', $content, $matches))
    {
      foreach ($matches[0] as $block)
      {
        $content = str_replace($block, '/*'.$block.'*/', $content);
      }
    }

    // remove multiline comments
    if (preg_match_all('/\/\*.+\*\//sU', $content, $matches))
    {
      foreach ($matches[0] as $block)
      {
        // we need to preserve line breaks
        $newBlock = preg_replace('/[^\n]+/', '', $block);
        $content = str_replace($block, $newBlock, $content);
      }
    }

    // process lines
    foreach (explode("\n", $content) as $line)
    {
      // annotation
      if (preg_match('/^\s*\/\/\s*@(\w+)([:\s]+(.*))?\s*$/', $line, $matches))
      {
        $unknownAnnotation = false;
        $annotation = $matches[1];
        $data = count($matches) > 3 ? trim($matches[3]) : '';

        switch ($annotation)
        {
          case 'Before':
            $function = $this->registerBeforeFunction();
            break;

          case 'Test':
            $function = $this->registerTestFunction();
            break;

          case 'After':
            $function = $this->registerAfterFunction();
            break;

          case 'AfterAll':
            $function = $this->registerAfterAllFunction();
            break;

          case 'BeforeAll':
            $function = $this->registerBeforeAllFunction();
            break;

          default:
            throw new LogicException(sprintf('The annotation "%s" is not valid', $annotation));
        }

        if ($inFunctionBlock)
        {
          fwrite($file, '} ');
        }

        $variables = implode(', ', $this->variables);
        $line = "function $function() { global $variables;";
        $inFunctionBlock = true;
        if (!empty($data))
        {
          $line .= ' '.$this->testVariable.'->comment("'.$data.'");';
        }
      }
      // tester instantiation
      else if (strpos($line, 'new lime_test_simple') !== false)
      {
        // register tester
        if (!preg_match('/(\$\w+)\s*=\s*new lime_test_simple/', $line, $matches))
        {
          throw new RuntimeException('The "lime_test_simple" class must be assigned to a variable');
        }

        $this->testVariable = $matches[1];

        // initialize variables instead
        $variables = $this->variables;
        foreach ($variables as $key => $variable)
        {
          $variables[$key] .= ' = null';
        }
        $line = 'global '.implode(', ', $this->variables).'; '.$this->testVariable." = \$this;";
      }

      fwrite($file, $line."\n");
    }

    if ($inFunctionBlock)
    {
      fwrite($file, '} ');
    }

    fclose($file);
  }

  /**
   * Registers a new function for a @Before annotation.
   *
   * @return string  The name of the registered function
   */
  protected function registerBeforeFunction()
  {
    $this->beforeFunctions[] = $name = '__lime_before_'.(count($this->beforeFunctions)+1);

    return $name;
  }

  /**
   * Registers a new function for a @After annotation.
   *
   * @return string  The name of the registered function
   */
  protected function registerAfterFunction()
  {
    $this->afterFunctions[] = $name = '__lime_after_'.(count($this->afterFunctions)+1);

    return $name;
  }

  /**
   * Registers a new function for a @AfterAll annotation.
   *
   * @return string  The name of the registered function
   */
  protected function registerAfterAllFunction()
  {
    $this->afterAllFunctions[] = $name = '__lime_after_all_'.(count($this->afterAllFunctions)+1);

    return $name;
  }

  /**
   * Registers a new function for a @BeforeAll annotation.
   *
   * @return string  The name of the registered function
   */
  protected function registerBeforeAllFunction()
  {
    $this->beforeAllFunctions[] = $name = '__lime_before_all_'.(count($this->beforeAllFunctions)+1);

    return $name;
  }

  /**
   * Registers a new function for a @Test annotation.
   *
   * @return string  The name of the registered function
   */
  protected function registerTestFunction()
  {
    $this->testFunctions[] = $name = '__lime_test_'.(count($this->testFunctions)+1);

    return $name;
  }

  /**
   * Executes the transformed test script.
   */
  protected function execute()
  {
//    var_dump(file_get_contents($this->path));
    include $this->path;

    foreach ($this->beforeAllFunctions as $beforeAllFunction)
    {
      $beforeAllFunction();
    }

    foreach ($this->testFunctions as $testFunction)
    {
      foreach ($this->beforeFunctions as $beforeFunction)
      {
        $beforeFunction();
      }

      $this->expectedException = null;
      $thrownException = null;

      try
      {
        $testFunction();
      }
      catch (Exception $e)
      {
        if (!is_null($this->expectedException))
        {
          $thrownException = get_class($e);
        }
        else
        {
          throw $e;
        }
      }

      if (!is_null($this->expectedException))
      {
        $this->is($thrownException, $this->expectedException, sprintf('A "%s" was thrown', $this->expectedException));
      }

      foreach ($this->afterFunctions as $afterFunction)
      {
        $afterFunction();
      }
    }

    foreach ($this->afterAllFunctions as $afterAllFunction)
    {
      $afterAllFunction();
    }
  }

  /**
   * Tells the test to expect a specific exception for the current @Test
   * annotation
   *
   * @param  string $exception  The exception class name
   */
  public function expect($exception)
  {
    $this->expectedException = $exception;
  }

  /**
   * Removes the transformed test script and restores the original test script.
   */
  public function shutdown()
  {
    if (file_exists($this->path) && file_exists($this->backupPath))
    {
      unlink($this->path);
      rename($this->backupPath, $this->path);
    }
  }

  /**
   * Returns the file path of the executed test script
   *
   * @return string  The file path
   */
  protected function getScriptPath()
  {
    $script = null;

    if (array_key_exists('PHP_SELF', $_SERVER) && $_SERVER['PHP_SELF'])
    {
      $script = $_SERVER['PHP_SELF'];
    }
    else if (array_key_exists('SCRIPT_NAME', $_SERVER) && $_SERVER['SCRIPT_NAME'])
    {
      $script = $_SERVER['SCRIPT_NAME'];
    }
    else if (array_key_exists('SCRIPT_FILENAME', $_SERVER) && $_SERVER['SCRIPT_FILENAME'])
    {
      $script = $_SERVER['SCRIPT_FILENAME'];
    }
    else
    {
      throw new RuntimeException('The name of the running script is not available!');
    }

    if (!is_file($script))
    {
      throw new RuntimeException('The script name returned by $_SERVER is not valid: '.$script);
    }

    return $script;
  }
}