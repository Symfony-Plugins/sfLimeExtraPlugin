<?php

/*
 * This file is part of the symfony package.
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class lime_mock_template
{
  private $parameters = array();
  private $filename = '';

  public function __construct($filename)
  {
    $this->filename = $filename;
  }

  public function render(array $parameters)
  {
    ob_start();
    extract($parameters);
    include $this->filename;

    return ob_get_clean();
  }

}