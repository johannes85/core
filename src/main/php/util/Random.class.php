<?php namespace util;

use io\IOException;
use lang\IllegalArgumentException;

/**
 * This random generator uses PHP's `random_bytes()` and `random_int()`
 * functions if available (PHP >= 7.0) and provides alternatives if
 * necessary.
 *
 * _Note_: This RNG prefers secure pseudo-random sources. This may be
 * slow; for considerably faster results, use Random::MTRAND (which
 * uses the Mersenne Twister algorithm)
 *
 * @see   http://www.math.sci.hiroshima-u.ac.jp/~m-mat/MT/emt.html
 * @see   http://sockpuppet.org/blog/2014/02/25/safely-generate-random-numbers/
 * @test  xp://net.xp_framework.unittest.util.RandomTest
 */
class Random {
  const DEFAULT = null;
  const OPENSSL = 'openssl';
  const MCRYPT  = 'mcrypt';
  const URANDOM = 'urandom';
  const MTRAND  = 'mtrand';

  private static $default;
  private $bytes, $ints;

  static function __static() {
    if (function_exists('random_bytes')) {
      self::$default= ['bytes' => 'random_bytes', 'ints' => 'random_int'];
    } else if (function_exists('openssl_random_pseudo_bytes')) {
      self::$default= ['bytes' => [__CLASS__, self::OPENSSL], 'ints' => null];
    } else if (function_exists('mcrypt_create_iv')) {
      self::$default= ['bytes' => [__CLASS__, self::MCRYPT], 'ints' => null];
    } else if (is_readable('/dev/urandom')) {
      self::$default= ['bytes' => [__CLASS__, self::URANDOM], 'ints' => null];
    } else {
      self::$default= ['bytes' => [__CLASS__, self::MTRAND], 'ints' => 'mt_rand'];
    }
  }

  /**
   * Creates a new random
   *
   * @param  string $source Optionally select source: DEFAULT, OPENSSL, MCRYPT, URANDOM, MTRAND
   */
  public function __construct($source= self::DEFAULT) {
    if ($source) {
      $this->bytes= [__CLASS__, $source];
      $this->ints= [$this, 'random'];
    } else {
      $this->bytes= self::$default['bytes'];
      $this->ints= self::$default['ints'] ?: [$this, 'random'];
    }
  }

  /**
   * Implementation using OpenSSL
   *
   * @param  int $limit
   * @return string $bytes
   */
  private static function openssl($limit) {
    return openssl_random_pseudo_bytes($limit);
  }

  /**
   * Implementation using MCrypt
   *
   * @param  int $limit
   * @return string $bytes
   */
  private static function mcrypt($limit) {
    return mcrypt_create_iv($limit, MCRYPT_DEV_URANDOM);
  }

  /**
   * Implementation reading from `/dev/urandom`
   *
   * @param  int $limit
   * @return string $bytes
   */
  private static function urandom($limit) {
    if (!($f= fopen('/dev/urandom', 'r'))) {
      $e= new IOException('Cannot access /dev/urandom');
      \xp::gc(__FILE__);
      throw $e;
    }

    // HHVM does not have stream_set_read_buffer()!
    function_exists('stream_set_read_buffer') && stream_set_read_buffer($f, 0);
    $bytes= fread($f, $limit);
    fclose($f);
    return $bytes;
  }

  /**
   * Implementation using `mt_rand()`
   *
   * @param  int $limit
   * @return string $bytes
   */
  private static function mtrand($limit) {
    $bytes= '';
    for ($i= 0; $i < $limit; $i++) {
      $bytes.= chr((mt_rand() ^ mt_rand()) % 0xFF);
    }
    return $bytes;
  }

  /**
   * Uses source to fetch random bytes and calculates a random int from
   * that within the given minimum and maximum limits.
   *
   * @param  int $min
   * @param  int $max
   * @return int
   */
  private function random($min, $max) {
    $range= $max - $min;

    // How many bytes do we need to represent the range?
    $bits= (int)ceil(log($range, 2));
    $bytes= (int)ceil($bits / 8);
    $mask= 2 ** $bits - 1;

    do {
      for ($random= $this->bytes($bytes), $result= 0, $i= 0; $i < $bytes; $i++) {
        $result |= ord($random{$i}) << ($i * 8);
      }

      // Wrap around if negative
      $result &= $mask;
    } while ($result > $range);

    return $result + $min;
  }

  /**
   * Returns a number of random bytes
   *
   * @param  int $amount
   * @return util.Bytes
   * @throws lang.IllegalArgumentException
   */
  public function bytes($amount) {
    if ($amount <= 0) {
      throw new IllegalArgumentException('Amount must be greater than 0');
    }
    $f= $this->bytes;
    return new Bytes($f($amount));
  }

  /**
   * Returns a random integer between the given min and max, both inclusive
   *
   * @param  int $min
   * @param  int $max
   * @return int
   * @throws lang.IllegalArgumentException
   */
  public function int($min= 0, $max= PHP_INT_MAX) {
    if ($min >= $max) {
      throw new IllegalArgumentException('Minimum value must be lower than max');
    }
    $f= $this->ints;
    return $f($min, $max);
  }
}