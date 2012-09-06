#!/bin/bash

root=$(dirname $(dirname $(readlink -f $0)))

$root/fixtures/project/symfony test:coverage --detailed ../../unit/sfGearmanTest.php ../../../lib/sfGearman.class.php
$root/fixtures/project/symfony test:coverage --detailed ../../unit/sfGearmanClientTest.php ../../../lib/sfGearmanClient.class.php
$root/fixtures/project/symfony test:coverage --detailed ../../unit/sfGearmanWorkerTest.php ../../../lib/sfGearmanWorker.class.php
$root/fixtures/project/symfony test:coverage --detailed ../../unit/sfGearmanQueueTest.php ../../../lib/sfGearmanQueue.class.php

