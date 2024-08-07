<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 5                                                  |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2020                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 * This file handles integration of KCFinder with wysiwyg editors
 * supported by CiviCRM
 * Ckeditor and tinyMCE
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2020
 * $Id$
 *
 */


function checkAuthentication() {
  static $authenticated;
  if ( !isset( $authenticated ) ) {
    $authenticated = false;

    // used to chdir at the end of this function - not sure if necessary?
    $current_cwd = getcwd();

    function findConfigFile(string $search_path): string {
      while ($search_path) {
        foreach(['civicrm.config.php', 'civicrm.standalone.php'] as $config_filename) {
          $config_file_candidate = $search_path . DIRECTORY_SEPARATOR . $config_filename;

          if (file_exists($config_file_candidate)) {
            return $config_file_candidate;
          }
        }

        $search_path = dirname($search_path);
      }

      throw new \Exception('KCFinder couldn\'t find civicrm.config.php or civicrm.standalone.php to check authentication');
    }

    require_once findConfigFile(__DIR__);
    require_once 'CRM/Core/Config.php';

    $config = CRM_Core_Config::singleton();

    if ( !isset($_SESSION['KCFINDER'] ) ) {
      $_SESSION['KCFINDER'] = array();
    }

    $auth_function = null;
    switch ($config->userFramework) {
    case 'Drupal':
    case 'Drupal6':
      $auth_function = 'authenticate_drupal';
      break;
    case 'Backdrop':
      $auth_function = 'authenticate_backdrop';
      break;
    case 'Joomla':
      $auth_function = 'authenticate_joomla';
      break;
    case 'WordPress':
      $auth_function = 'authenticate_wordpress';
      break;
    case 'Drupal8':
      $auth_function = 'authenticate_drupal8';
      break;
    case 'Standalone':
      $auth_function = 'authenticate_standalone';
      break;
    }
    if(!$auth_function($config)) {
      throw new CRM_Core_Exception(ts("You must be logged in with proper permissions to edit, add, or delete uploaded images."));
    }

    $_SESSION['KCFINDER']['disabled'] = false;
    $_SESSION['KCFINDER']['uploadURL'] = $config->imageUploadURL;
    $_SESSION['KCFINDER']['uploadDir'] = $config->imageUploadDir;

    $authenticated = true;
    chdir( $current_cwd );
  }
}


function authenticate_drupal8($config) {
  CRM_Utils_System::loadBootStrap(CRM_Core_DAO::$_nullArray, true, false);

  // https://drupal.stackexchange.com/questions/231710/how-does-drupal-verify-sessions-from-the-cookie-value
  foreach ($_COOKIE as $key => $val) {
    if (substr($key, 0, 5) == "SSESS" || substr($key, 0, 4) == 'SESS') {
      $session = $val;
    }
  }

  if ($session) {
    $connection = \Drupal::database();
    $query = $connection->query("SELECT uid FROM {sessions} WHERE sid = :sid", array(":sid" => \Drupal\Component\Utility\Crypt::hashBase64($session)));
    if (($uid = $query->fetchField()) > 0) {
      $username = \Drupal\user\Entity\User::load($uid)->getAccountName();
      if ($username) {
        $config->userSystem->loadUser($username);
      }
    }
  }

  // Start Drupal's own session now, so changes to $_SESSION won't get overwritten later
  \Drupal::service('session')->start();

  // check if user has access permission...
  if (CRM_Core_Permission::check('access CiviCRM')) {
    return true;
  }
  return false;

}

/**
 * If the user is already logged into Drupal, bootstrap
 * drupal with this user's permissions. Thanks to integrate/drupal.php
 * script for hints on how to do this.
 **/
function authenticate_drupal($config) {
  global $base_url;
  $base_root = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https' : 'http';
  $base_url = $base_root .= '://'. preg_replace('/[^a-z0-9-:._]/i', '', $_SERVER['HTTP_HOST']);

  if ($dir = trim(dirname($_SERVER['SCRIPT_NAME']), '\,/')) {
    $base_path = "/$dir";
    $base_url .= $base_path;
  }

  // correct base_url so it points to Drupal root
  $pos = strpos($base_url, '/sites/');
  if ($pos === FALSE) {
    $pos = strpos($base_url, '/profiles/');
  }
  $base_url = substr($base_url, 0, $pos); // drupal root absolute url

  CRM_Utils_System::loadBootStrap(CRM_Core_DAO::$_nullArray,true,false);

  // check if user has access permission...
  if (CRM_Core_Permission::check('access CiviCRM')) {
    return true;
  }
  return false;
}

/**
 * If the user is already logged into Backdrop, bootstrap
 * Backdrop with this user's permissions.
 */
function authenticate_backdrop($config) {
  global $base_url;
  $base_root = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https' : 'http';
  $base_url = $base_root .= '://'. preg_replace('/[^a-z0-9-:._]/i', '', $_SERVER['HTTP_HOST']);

  if ($dir = trim(dirname($_SERVER['SCRIPT_NAME']), '\,/')) {
    $base_path = "/$dir";
    $base_url .= $base_path;
  }

  // Correct base_url so it points to Backdrop root.
  $pos = strpos($base_url, '/sites/');
  if ($pos === FALSE) {
    $pos = strpos($base_url, '/profiles/');
  }
  if ($pos === FALSE) {
    $pos = strpos($base_url, '/modules/');
  }
  $base_url = substr($base_url, 0, $pos); // Backdrop root absolute url

  CRM_Utils_System::loadBootStrap(CRM_Core_DAO::$_nullArray, true, false);

  // Check if user has access permission.
  if (CRM_Core_Permission::check('access CiviCRM')) {
    return true;
  }
  return false;
}

function authenticate_wordpress($config) {
  // make sure user has access to civicrm
  CRM_Utils_System::loadBootStrap();
  require_once "CRM/Core/Permission.php";
  if (CRM_Core_Permission::check('access CiviCRM')) {
    return true;
  }
  return false;
}

function authenticate_standalone($config) {
  return CRM_Core_Permission::check('access CiviCRM');
}

function authenticate_joomla($config) {
  // make sure only logged in user can see upload / view images
  $joomlaBase = dirname(dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))));

  define( '_JEXEC', 1 );
  define('JPATH_BASE', $joomlaBase);
  define( 'DS', DIRECTORY_SEPARATOR );
  require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
  require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );

  if (version_compare(JVERSION, '4.0.0', 'lt')) {
    $mainframe = JFactory::getApplication('administrator');
    $mainframe->initialise();
    
    $user_id = JFactory::getUser()->id;
  } else {
    // Boot the DI container.
    $container = \Joomla\CMS\Factory::getContainer();

    // Alias the session service key to the web session service.
    $container->alias(\Joomla\Session\SessionInterface::class, 'session.web.site');

    // Get the application.
    $app = $container->get(\Joomla\CMS\Application\AdministratorApplication::class);
    
    $user_id = Joomla\CMS\Factory::getUser()->id;
  }

  if ($user_id == 0) {
    return false;
  }
  return true;
}

checkAuthentication( );

//spl_autoload_register('__autoload');

?>
