<?php

// 
// The source code is copyrighted, with equal shared rights, between the
// authors (see the file AUTHORS) and the OpenExam project, Uppsala University 
// unless otherwise explicit stated elsewhere.
// 
// File:    VirtualMemoryCounter.php
// Created: 2016-05-24 02:23:31
// 
// Author:  Anders Lövgren (QNET/BMC CompDept)
// 

namespace OpenExam\Library\Monitor\Performance\Counter\Server;

use OpenExam\Library\Monitor\Performance\Counter;
use OpenExam\Library\Monitor\Performance\Counter\PerformanceCounter;

/**
 * Virtual memory performance counter.
 *
 * @author Anders Lövgren (QNET/BMC CompDept)
 */
class VirtualMemoryCounter extends PerformanceCounter implements Counter
{

        /**
         * The process counter.
         */
        const PROCESS = 'process';
        /**
         * The memory counter.
         */
        const MEMORY = 'memory';
        /**
         * The swap counter.
         */
        const SWAP = 'swap';
        /**
         * The I/O counter.
         */
        const IO = 'io';
        /**
         * The system counter.
         */
        const SYSTEM = 'system';
        /**
         * The CPU counter.
         */
        const CPU = 'cpu';

        /**
         * Constructor.
         * @param array $data The performance data.
         */
        public function __construct($data = null)
        {
                parent::__construct($data, array(
                        'label'   => $this->tr->_("Virtual Memory Counters"),
                        'descr'   => $this->tr->_("Information about processes, memory, paging, block I/O and CPU activity."),
                        'cpu'     => array(
                                'label'  => $this->tr->_("Processor (CPU)"),
                                'descr'  => $this->tr->_("These are percentages of total CPU time."),
                                'user'   => array(
                                        'label' => $this->tr->_("User Mode"),
                                        'descr' => $this->tr->_("Time spent running non-kernel code."),
                                ),
                                'system' => array(
                                        'label' => $this->tr->_("Kernel Mode"),
                                        'descr' => $this->tr->_("Time spent running kernel code."),
                                ),
                                'idle'   => array(
                                        'label' => $this->tr->_("Idle"),
                                        'descr' => $this->tr->_("Time spent idle."),
                                ),
                                'wait'   => array(
                                        'label' => $this->tr->_("I/O Wait"),
                                        'descr' => $this->tr->_("Time spent waiting for IO."),
                                ),
                                'stolen' => array(
                                        'label' => $this->tr->_("Stolen"),
                                        'descr' => $this->tr->_("Time stolen from a virtual machine."),
                                )
                        ),
                        'memory'  => array(
                                'label'  => $this->tr->_("Memory"),
                                'descr'  => $this->tr->_("RAM and swap used, including disk cache and buffers."),
                                'swap'   => array(
                                        'label' => $this->tr->_("Swap"),
                                        'descr' => $this->tr->_("The amount of virtual memory used."),
                                ),
                                'free'   => array(
                                        'label' => $this->tr->_("Free Memory"),
                                        'descr' => $this->tr->_("The amount of idle (unused) memory."),
                                ),
                                'buffer' => array(
                                        'label' => $this->tr->_("Buffered"),
                                        'descr' => $this->tr->_("The amount of memory used as buffers."),
                                ),
                                'cached' => array(
                                        'label' => $this->tr->_("Cached"),
                                        'descr' => $this->tr->_("The amount of memory used as cache."),
                                )
                        ),
                        'swap'    => array(
                                'label' => $this->tr->_("Swap"),
                                'descr' => $this->tr->_("Usage of disk paging."),
                                'in'    => array(
                                        'label' => $this->tr->_("Pages In"),
                                        'descr' => $this->tr->_("Amount of memory swapped in from disk (/s)."),
                                ),
                                'out'   => array(
                                        'label' => $this->tr->_("Pages Out"),
                                        'descr' => $this->tr->_("Amount of memory swapped to disk (/s)."),
                                )
                        ),
                        'process' => array(
                                'label'    => $this->tr->_("Processes"),
                                'descr'    => $this->tr->_("Information about running processes."),
                                'runnable' => array(
                                        'label' => $this->tr->_("Runnable"),
                                        'descr' => $this->tr->_("The number of runnable processes (running or waiting for run time)."),
                                ),
                                'sleeping' => array(
                                        'label' => $this->tr->_("Sleeping"),
                                        'descr' => $this->tr->_("The number of processes in uninterruptible sleep."),
                                )
                        ),
                        'io'      => array(
                                'label' => $this->tr->_("Block I/O"),
                                'descr' => $this->tr->_("Read/write for block devices."),
                                'in'    => array(
                                        'label' => $this->tr->_("Block In"),
                                        'descr' => $this->tr->_("Blocks received from a block device (blocks/s)."),
                                ),
                                'out'   => array(
                                        'label' => $this->tr->_("Block Out"),
                                        'descr' => $this->tr->_("Blocks sent to a block device (blocks/s)."),
                                )
                        ),
                        'system'  => array(
                                'label'      => $this->tr->_("System"),
                                'descr'      => $this->tr->_("IRQ and context switches."),
                                'interrupts' => array(
                                        'label' => $this->tr->_("Interrupts"),
                                        'descr' => $this->tr->_("The number of interrupts per second, including the clock."),
                                ),
                                'context'    => array(
                                        'label' => $this->tr->_("Context Switches"),
                                        'descr' => $this->tr->_("The number of context switches per second."),
                                )
                        )
                    )
                );
        }

        /**
         * Get CPU counter.
         * @return array
         */
        public function getCpuCounter()
        {
                return parent::getCounter(self::CPU);
        }

        /**
         * Get I/O counter.
         * @return array
         */
        public function getIOCounter()
        {
                return parent::getCounter(self::IO);
        }

        /**
         * Get memory counter.
         * @return array
         */
        public function getMemoryCounter()
        {
                return parent::getCounter(self::MEMORY);
        }

        /**
         * Get process counter.
         * @return array
         */
        public function getProcessCounter()
        {
                return parent::getCounter(self::PROCESS);
        }

        /**
         * Get swap counter.
         * @return array
         */
        public function getSwapCounter()
        {
                return parent::getCounter(self::SWAP);
        }

        /**
         * Get system counter.
         * @return array
         */
        public function getSystemCounter()
        {
                return parent::getCounter(self::SYSTEM);
        }

}
