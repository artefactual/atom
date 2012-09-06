<?php

class TestWorker
{
  public static function md5($job, $worker)
  {
    $worker->notifyEventJob($job);

    return md5_file($job->workload());
  }
}

