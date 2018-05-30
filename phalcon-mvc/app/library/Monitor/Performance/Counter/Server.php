<?php

/*
 * Copyright (C) 2016-2018 The OpenExam Project
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

// 
// File:    Server.php
// Created: 2016-05-24 02:23:31
// 
// Author:  Anders Lövgren (Nowise Systems)
// 

namespace OpenExam\Library\Monitor\Performance\Counter;

use OpenExam\Library\Monitor\Performance;
use OpenExam\Library\Monitor\Performance\Counter;

/**
 * Server performance counter.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Server extends CounterBase implements Counter
{

        /**
         * The counter name.
         */
        const TYPE = 'server';
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
         * @param Performance $performance The performance object.
         */
        public function __construct($performance)
        {
                parent::__construct(self::TYPE, $performance);
        }

        /**
         * Get counter name (short name).
         * @return string
         */
        public function getName()
        {
                return $this->i18n->_("Server");
        }

        /**
         * Get counter title (longer name).
         * @return string
         */
        public function getTitle()
        {
                return $this->i18n->_("Virtual Memory Counters");
        }

        /**
         * Get counter description.
         * @return string
         */
        public function getDescription()
        {
                return $this->i18n->_("Information about processes, memory, paging, block I/O and CPU activity.");
        }

        /**
         * Get translated performance counter keys.
         * @return array
         */
        public function getKeys()
        {
                return array(
                        'label'   => $this->getTitle(),
                        'descr'   => $this->getDescription(),
                        'cpu'     => array(
                                'label'  => $this->i18n->_("Processor (CPU)"),
                                'descr'  => $this->i18n->_("These are percentages of total CPU time."),
                                'user'   => array(
                                        'label' => $this->i18n->_("User Mode"),
                                        'descr' => $this->i18n->_("Time spent running non-kernel code."),
                                ),
                                'system' => array(
                                        'label' => $this->i18n->_("Kernel Mode"),
                                        'descr' => $this->i18n->_("Time spent running kernel code."),
                                ),
                                'idle'   => array(
                                        'label' => $this->i18n->_("Idle"),
                                        'descr' => $this->i18n->_("Time spent idle."),
                                ),
                                'wait'   => array(
                                        'label' => $this->i18n->_("I/O Wait"),
                                        'descr' => $this->i18n->_("Time spent waiting for IO."),
                                ),
                                'stolen' => array(
                                        'label' => $this->i18n->_("Stolen"),
                                        'descr' => $this->i18n->_("Time stolen from a virtual machine."),
                                )
                        ),
                        'memory'  => array(
                                'label'  => $this->i18n->_("Memory"),
                                'descr'  => $this->i18n->_("RAM and swap used, including disk cache and buffers."),
                                'swap'   => array(
                                        'label' => $this->i18n->_("Swap"),
                                        'descr' => $this->i18n->_("The amount of virtual memory used."),
                                ),
                                'free'   => array(
                                        'label' => $this->i18n->_("Free Memory"),
                                        'descr' => $this->i18n->_("The amount of idle (unused) memory."),
                                ),
                                'buffer' => array(
                                        'label' => $this->i18n->_("Buffered"),
                                        'descr' => $this->i18n->_("The amount of memory used as buffers."),
                                ),
                                'cached' => array(
                                        'label' => $this->i18n->_("Cached"),
                                        'descr' => $this->i18n->_("The amount of memory used as cache."),
                                )
                        ),
                        'swap'    => array(
                                'label' => $this->i18n->_("Swap"),
                                'descr' => $this->i18n->_("Usage of disk paging."),
                                'in'    => array(
                                        'label' => $this->i18n->_("Pages In"),
                                        'descr' => $this->i18n->_("Amount of memory swapped in from disk (/s)."),
                                ),
                                'out'   => array(
                                        'label' => $this->i18n->_("Pages Out"),
                                        'descr' => $this->i18n->_("Amount of memory swapped to disk (/s)."),
                                )
                        ),
                        'process' => array(
                                'label'    => $this->i18n->_("Processes"),
                                'descr'    => $this->i18n->_("Information about running processes."),
                                'runnable' => array(
                                        'label' => $this->i18n->_("Runnable"),
                                        'descr' => $this->i18n->_("The number of runnable processes (running or waiting for run time)."),
                                ),
                                'sleeping' => array(
                                        'label' => $this->i18n->_("Sleeping"),
                                        'descr' => $this->i18n->_("The number of processes in uninterruptible sleep."),
                                )
                        ),
                        'io'      => array(
                                'label' => $this->i18n->_("Block I/O"),
                                'descr' => $this->i18n->_("Read/write for block devices."),
                                'in'    => array(
                                        'label' => $this->i18n->_("Block In"),
                                        'descr' => $this->i18n->_("Blocks received from a block device (blocks/s)."),
                                ),
                                'out'   => array(
                                        'label' => $this->i18n->_("Block Out"),
                                        'descr' => $this->i18n->_("Blocks sent to a block device (blocks/s)."),
                                )
                        ),
                        'system'  => array(
                                'label'      => $this->i18n->_("System"),
                                'descr'      => $this->i18n->_("IRQ and context switches."),
                                'interrupts' => array(
                                        'label' => $this->i18n->_("Interrupts"),
                                        'descr' => $this->i18n->_("The number of interrupts per second, including the clock."),
                                ),
                                'context'    => array(
                                        'label' => $this->i18n->_("Context Switches"),
                                        'descr' => $this->i18n->_("The number of context switches per second."),
                                )
                        )
                );
        }

        /**
         * Check if sub counter type exist.
         * @param string $type The sub counter type.
         * @return boolean
         */
        public function hasCounter($type)
        {
                return
                    $type == self::CPU ||
                    $type == self::IO ||
                    $type == self::MEMORY ||
                    $type == self::PROCESS ||
                    $type == self::SWAP ||
                    $type == self::SYSTEM;
        }

}
