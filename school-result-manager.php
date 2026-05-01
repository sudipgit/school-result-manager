<?php
/**
 * Plugin Name: School Result Manager
 * Description: Manage SSC/Bangladesh secondary school results, generate marksheets, and PDFs.
 * Version: 1.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) exit;
define('SRM_PLUGIN_FILE', __FILE__);
define('SRM_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('SRM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SRM_PLUGIN_BASENAME', plugin_basename(__FILE__));


// Includes
require_once SRM_PLUGIN_PATH . 'includes/db.php';
require_once SRM_PLUGIN_PATH . 'includes/roles.php';
require_once SRM_PLUGIN_PATH . 'includes/menu.php';
require_once SRM_PLUGIN_PATH . 'includes/result-calc.php';
require_once SRM_PLUGIN_PATH . 'includes/pdf.php';
require_once SRM_PLUGIN_PATH . 'includes/functions.php';
require_once SRM_PLUGIN_PATH . 'includes/functions-result.php';
require_once SRM_PLUGIN_PATH . 'includes/functions-addinfo.php';
require_once SRM_PLUGIN_PATH . 'includes/functions-management.php';
require_once SRM_PLUGIN_PATH . 'includes/shortcodes.php';
require_once SRM_PLUGIN_PATH . 'includes/shortcodes-management.php';
require_once SRM_PLUGIN_PATH . 'includes/shortcodes-addinfo.php';
// Activation Hook
register_activation_hook( SRM_PLUGIN_FILE, 'srm_install_tables');


add_filter('login_redirect', 'srm_teacher_login_redirect', 10, 3);
function srm_teacher_login_redirect($redirect_to, $request, $user) {
    // Make sure we have a WP_User object
    if (isset($user->roles) && is_array($user->roles)) {

        // Check if user has the 'teacher' role
        if (in_array('teacher', $user->roles)) {
            // Change this to the URL you want
            return site_url('/teacher-profile/');
        }
    }

    // Default redirect (admin/dashboard)
    return $redirect_to;
}


// Admin Pages
add_action('admin_menu', 'srm_admin_menu');
register_activation_hook( SRM_PLUGIN_FILE, 'srm_add_roles');
