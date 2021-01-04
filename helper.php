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
                    $conditions[] = array('column' => $where->w_column, 'value' => $where->w_col_val);
                }
                if($task->task_type === 'update' && count((array)$task->update)){
                    $updates = array();
                    foreach ($task->update as $update){
                        $updates[] = array('column' => $update->u_column, 'value' => $update->u_col_val, 'is_date' => $update->is_date);
                    }
                }
            }

            $responses[] = self::doTask($task->task_type, $table, $conditions, $updates);
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

            foreach ($conditionsSet as $condition){
                $conditions[] = $db->quoteName($condition['column']) . ' = ' . $db->quote($condition['value']);
            }

            // do the job
            if($task === 'update') {
                $query->update($db->quoteName($table))->set($fields)->where($conditions);
            }elseif ($task === 'remove'){
                $query->delete($db->quoteName($table))->where($conditions);
            }

            $db->setQuery($query);

            $status = $db->execute();

            if($status){
                $rows = $db->getAffectedRows();
                if($rows == 0){
                    $state = array('status'=>'error','status_text'=>'Attention! Where clause did not matched any row.<br/>This doesn\'t have to be an error, the module just couldn\'t find any entries based on your filter criteria that needs to be '.$task.'d.');
                }else{
                    $state = array('status'=>'success','status_text'=> 'Task Successfull, ' . $rows . ' rows got '.$task.'d');
                }
                return $state;
            }
        }catch( Exception $e){
            $msg = $e->getMessage();
            return array('status'=>'error','status_text'=>$msg);
        }
    }
}
