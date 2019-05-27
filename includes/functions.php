<?php
/**
 * Created by PhpStorm.
 * User: Exlord
 * Date: 5/21/2019
 * Time: 11:22 AM
 */

/**
 * @param $haystack
 * @param $needle
 * @return bool
 */
function startsWith($haystack, $needle) {
  $length = strlen($needle);
  return (substr($haystack, 0, $length) === $needle);
}

/**
 * @param $haystack
 * @param $needle
 * @return bool
 */
function endsWith($haystack, $needle) {
  $length = strlen($needle);
  if ($length == 0) {
    return true;
  }

  return (substr($haystack, -$length) === $needle);
}
