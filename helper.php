<?php

defined('_JEXEC') or die;

/**
* @package     mod_db_data_changer
*
* @copyright   Copyright (C) 2019 - 2020 nx-designs, Inc. All rights reserved.
* @license     GNU General Public License version 2 or later; see LICENSE.txt
*/

class ModDbDataChanger {
    public static function prepareTask($params){
        $responses = array();
        foreach ($params->get('tasks',array()) as $task){
            $table = $task->table;
            // Conditions for which records should be updated.
            $conditions = array();
            $updates = false;

            if(count((array)$task->where)){
                foreach ($task->where as $where){
                    if(strlen($where->w_column) && strlen($where->w_col_val)){
                        $conditions[] = array(
                            'column' => $where->w_column,
                            'value' => $where->w_col_val,
                            'operator' => $where->operator,
                            'type' => $where->type
                        );
                    }

                }
                if($task->task_type === 'update' && count((array)$task->update)){
                    $updates = array();
                    foreach ($task->update as $update){
                        $updates[] = array('column' => $update->u_column, 'value' => $update->u_col_val, 'is_date' => $update->is_date);
                    }
                }
            }
            if(count($conditions) || !$params->get('prevent_full_del',1)){
                $responses[] = self::doTask($task->task_type, $table, $conditions, $updates);
            }else{
                $responses[] = array('status'=>'danger','status_text'=> 'Task Canceled, no conditions where set & "prevent full deletion" is enabled');
            }
        }
        return $responses;
    }

    public static function doTask($task, $table, $conditionsSet, $updatesSet){
        try {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            // Fields to update.
            $fields = array();

            // Conditions for which records should be updated.
            $conditions = array();
            if($updatesSet){
                foreach ($updatesSet as $update) {
                    if($update['is_date']){
                        $value = JFactory::getDate()->toSql();
                        $value = $db->quote($value);
                    }else{
                        $value = $db->quote($update['value']);
                    }
                    $fields[] = $db->quoteName($update['column']) . ' = ' . $value;
                }
            }

            // do the job
            if($task === 'update') {
                $query->update($db->quoteName($table));
                $query->set($fields);
            }elseif ($task === 'remove'){
                $query->delete($db->quoteName($table));
            }

            foreach ($conditionsSet as $condition){
                $value = $db->quote($condition['value']);
                switch($condition['operator']){
                    case 'is':
                    default:
                        $op = ' = ';
                        break;
                    case 'is_not':
                        $op = ' != ';
                        break;
                    case 'larger':
                        $op = ' > ';
                        break;
                    case 'larger_equal':
                        $op = ' >= ';
                        break;
                    case 'smaller':
                        $op = ' < ';
                        break;
                    case 'smaller_equal':
                        $op = ' <= ';
                        break;
                    case 'in':
                        $op = ' IN ';
                        $value = '(' . $condition['value'] . ')';
                }
                $type = $condition['type'] === 'and' ? 'where' : 'orWhere';
                $query->$type($db->quoteName($condition['column']) . $op . $value);
                //$conditions[] = $db->quoteName($condition['column']) . ' = ' . $db->quote($condition['value']);
            }

            $db->setQuery($query);

            $status = $db->execute();

            if($status){
                $rows = $db->getAffectedRows();
                if($rows == 0){
                    $state = array('dump'=>$query->dump(), 'status'=>'error','status_text'=>'Attention! Where clause did not matched any row.<br/>This doesn\'t have to be an error, the module just couldn\'t find any entries based on your filter criteria that needs to be '.$task.'d.');
                }else{
                    $state = array('dump'=>$query->dump(), 'status'=>'success','status_text'=> 'Task Successfull, ' . $rows . ' rows got '.$task.'d');
                }
                return $state;
            }
        }catch( Exception $e){
            $msg = $e->getMessage();
            return array('dump'=>$query->dump(), 'status'=>'error','status_text'=>$msg);
        }
    }
}
