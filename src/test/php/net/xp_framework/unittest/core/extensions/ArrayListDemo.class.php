<?php namespace net\xp_framework\unittest\core\extensions;

use util\cmd\Console;
use lang\types\ArrayList;
new \import('net.xp_framework.unittest.core.extensions.ArrayListExtensions');

/**
 * Demo class that uses the ArrayList extension methods
 *
 * @see   xp://net.xp_framework.unittest.core.extensions.ArrayListExtensions
 */
class ArrayListDemo extends \lang\Object {
  
  /**
   * Main method
   *
   * @param   string[] args
   */
  public static function main($args) {
    $sorted= ArrayList::newInstance($args)->sorted();
    Console::writeLine('(new ArrayList(array(', implode(', ', $args), ')))->sorted()= ', $sorted);
  }
}