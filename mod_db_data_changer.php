<?php
/**
 * @package    db_data_changer
 *
 * @author     marco <your@email.com>
 * @copyright  A copyright
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://your.url.com
 */

use Joomla\CMS\Helper\ModuleHelper;

defined('_JEXEC') or die;

require_once __DIR__ . '/helper.php';

if(array_key_exists('sectoken', $_GET) && htmlspecialchars($_GET['sectoken']) === htmlspecialchars($params->get('token',''))){
    if($params->get('debug',1)) echo 'Security Token accepted!';
    // Security Token is fine so lets do it
    $responses = ModDbDataChanger::prepareTask($params);
}else{
    if($params->get('debug',1)) {
        if (!array_key_exists('sectoken', $_GET) || array_key_exists('sectoken', $_GET) && !strlen(htmlspecialchars($_GET['sectoken']))) {
            echo '<p class="alert alert-warning">Security Token not given!</p>';
        } else {
            echo '<p class="alert alert-danger">Security Token is wrong! - Check DB data changer module with ID: '.$module->id.' "'.$module->title.'"</p>';
        }
        echo '<div class="alert alert-message">Hint: To hide this message you have to deactivate the debug mode of the module '.$module->title.'.</div>';
    }
}





// The below line is no longer used in Joomla 4
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

require ModuleHelper::getLayoutPath('mod_db_data_changer', $params->get('layout', 'default'));
