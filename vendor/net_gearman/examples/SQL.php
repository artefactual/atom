<?php

require_once 'DB.php';

class Net_Gearman_Job_SQL extends Net_Gearman_Job_Common
{
    public function run($arg)
    {
        if (!isset($arg->sql) || !strlen($arg->sql)) {
            throw new Net_Gearman_Job_Exception;
        }

        $db = DB::connect('mysql://testing:testing@192.168.243.20/testing');
        $db->setFetchMode(DB_FETCHMODE_ASSOC);
        $res = $db->getAll($arg->sql);
        return $res;
    }
}

?>
