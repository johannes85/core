<?php namespace net\xp_framework\unittest\util;

use util\Properties;
use io\streams\MemoryInputStream;

/**
 * Testcase for util.Properties class.
 *
 * @see   xp://net.xp_framework.unittest.util.AbstractPropertiesTest
 */
class StreamBasedPropertiesTest extends AbstractPropertiesTest {

  /**
   * Create a new properties object from a string source
   *
   * @param   string source
   * @return  util.Properties
   */
  protected function newPropertiesFrom(string $source): Properties {
    return (new Properties())->load(new MemoryInputStream($source));
  }
}
