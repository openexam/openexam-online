<?php

// 
// The source code is copyrighted, with equal shared rights, between the
// authors (see the file AUTHORS) and the OpenExam project, Uppsala University 
// unless otherwise explicit stated elsewhere.
// 
// File:    VirtualMemoryCollector.php
// Created: 2016-05-23 01:35:44
// 
// Author:  Anders Lövgren (QNET/BMC CompDept)
// 

namespace OpenExam\Library\Monitor\Performance\Collector\Server;

use OpenExam\Library\Console\Process;
use OpenExam\Library\Monitor\Performance\Collector\PerformanceCollector;
use OpenExam\Models\Performance as PerformanceModel;

/**
 * Performance collector for virtual memory.
 *
 * @author Anders Lövgren (QNET/BMC CompDept)
 */
class VirtualMemoryCollector extends PerformanceCollector
{

        /**
         * The command to execute.
         */
        const COMMAND = "vmstat -S M -n %d";

        /**
         * Constructor.
         * @param int $rate The sample rate.
         */
        public function __construct($rate = 5)
        {
                $command = sprintf(self::COMMAND, $rate);
                parent::__construct(new Process($command));
        }

        /**
         * Save performance data.
         * @return boolean
         */
        protected function save()
        {
                $line = $this->_process->read();
                $vals = preg_split("/\s+/", trim($line));

                if (count($vals) != 17) {
                        return false;
                }

                $data = array(
                        'process' => array(
                                'runnable' => $vals[0],
                                'sleeping' => $vals[1]
                        ),
                        'memory'  => array(
                                'swap'   => $vals[2],
                                'free'   => $vals[3],
                                'buffer' => $vals[4],
                                'cached' => $vals[5]
                        ),
                        'swap'    => array(
                                'in'  => $vals[6],
                                'out' => $vals[7]
                        ),
                        'io'      => array(
                                'in'  => $vals[8],
                                'out' => $vals[9]
                        ),
                        'system'  => array(
                                'interrupts' => $vals[10],
                                'context'    => $vals[11]
                        ),
                        'cpu'     => array(
                                'user'   => $vals[12],
                                'system' => $vals[13],
                                'idle'   => $vals[14],
                                'wait'   => $vals[15],
                                'stolen' => $vals[16]
                        )
                );

                $model = new PerformanceModel();
                $model->data = $data;
                $model->mode = PerformanceModel::MODE_VM;
                $model->host = $this->_host;
                $model->addr = $this->_addr;

                if (!$model->save()) {
                        foreach ($model->getMessages() as $message) {
                                trigger_error($message, E_USER_ERROR);
                        }
                        return false;
                }

                return true;
        }

}
