<?php

namespace Net\Gearman\Tests;

class ManagerTest extends \PHPUnit\Framework\TestCase {

    /**
     * @dataProvider workerResponseData
     */
    public function testWorkerResponse($input, $expected) {
        $mgr = new \Net_Gearman_Manager();
        $workers = $mgr->parseWorkersResponse($input);
        $this->assertEquals(
            $expected,
            $workers
        );
    }

    public function workerResponseData() {
        return [
            [
                "41 172.17.0.7 pid_3493_5b7aca9571e4a : Job1 Job2 Job3\n".
                "37 172.17.0.7 pid_3489_5b7aca9083c2e : Job4 Job5 Job6",
                [
                    [
                        "fd" => "41",
                        "ip" => "172.17.0.7",
                        "id" => "pid_3493_5b7aca9571e4a",
                        "abilities" => [
                            "Job1",
                            "Job2",
                            "Job3"
                        ]
                    ],
                    [
                        "fd" => "37",
                        "ip" => "172.17.0.7",
                        "id" => "pid_3489_5b7aca9083c2e",
                        "abilities" => [
                            "Job4",
                            "Job5",
                            "Job6"
                        ]
                    ]
                ]
            ],
            [
                // Invalid IPv6 data see bug https://bugs.launchpad.net/gearmand/+bug/1319250
                "34 ::3530:3539:3500:0%2134665296 - :",
                [
                    [
                        "fd" => "34",
                        "ip" => "::3530:3539:3500:0%2134665296",
                        "id" => "-",
                        "abilities" => []
                    ],
                ]
            ]
        ];
    }
}
