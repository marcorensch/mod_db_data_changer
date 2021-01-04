<?php
/**
 * @package    mod_db_data_changer
 *
 * @author     Marco Rensch <support@nx-designs.ch>
 * @copyright  Copyright 2021 by nx-designs
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://your.url.com
 */

defined('_JEXEC') or die;

// Show something if enabled

if($params->get('show_messages',1) && isset($responses)):
foreach ($responses as $response):
?>
<div class="status_<?php if(array_key_exists('status',$response)) echo $response['status'];?>">
    <span><?php if(array_key_exists('status_text',$response)) echo $response['status_text'];?></span>
</div>

<?php
endforeach;
endif; ?>
