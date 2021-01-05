<?php
/**
 * @package        mod_db_data_changer
 *
 * @author         Marco Rensch <support@nx-designs.ch>
 * @copyright      ©2020 nx-designs
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           http://www.nx-designs.ch
 */

defined('_JEXEC') or die;

class JFormFieldDbTableSelect extends JFormFieldList
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.7.0
     */
    protected $type = 'DbTableSelect';

    /**
     * Method to get the list of files for the field options.
     * Specify the target directory with a directory attribute
     * Attributes allow an exclude mask and stripping of extensions from file name.
     * Default attribute may optionally be set to null (no file) or -1 (use a default).
     *
     * @return  array  The field option objects.
     *
     * @since   1.0.0
     */
    protected function getOptions()
    {
        $options = array();

        $app = JFactory::getApplication();
        $prefix = $app->getCfg('dbprefix');

        $tables= array();
        $table_labels = JFactory::getDbo()->setQuery('SHOW TABLES')->loadColumn();
        foreach ($table_labels as $table_label){
            $table_value = str_replace($prefix,'#__', $table_label);
            $tables[] = array('value'=> $table_value, 'label' => $table_label);
        }
        if($tables){
            foreach ($tables as $table){
                $options[] = JHtml::_('select.option', $table['value'], $table['value']);
            }
        }
        return $options;
    }
}