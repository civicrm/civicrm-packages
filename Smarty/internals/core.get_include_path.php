<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Get path to file from include_path
 * CRM-19116: Rewrite to improve performance
 * stream_resolve_include_path exists in PHP >= 5.3.2.
 *
 * @param string[] $params
 * @param object $smarty
 *
 * @return boolean
 */
function smarty_core_get_include_path(&$params, &$smarty) {

  $newpath = stream_resolve_include_path($params['file_path']);
  if (@is_readable($newpath)) {
    $params['new_file_path'] = $newpath;
    return TRUE;
  }
}

/* vim: set expandtab: */

?>
