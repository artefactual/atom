<?php

/**
 * Sum up a bunch of numbers
 *
 * @author      Joe Stump <joe@joestump.net>
 * @package     Net_Gearman
 */
class Net_Gearman_Job_Sum extends Net_Gearman_Job_Common
{
    /**
     * Run the summing job
     *
     * @access      public
     * @param       array       $arg
     * @return      array
     */
    public function run($arg)
    {
        $sum = 0;
        foreach ($arg as $i) {
            if (is_numeric($i)) {
                $sum += $i;
            }
        }

        return array('sum' => $sum);
    }
}

?>
