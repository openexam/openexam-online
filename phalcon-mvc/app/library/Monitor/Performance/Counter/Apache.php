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
// File:    Apache.php
// Created: 2016-05-30 08:16:55
// 
// Author:  Anders Lövgren (Nowise Systems)
// 

namespace OpenExam\Library\Monitor\Performance\Counter;

use OpenExam\Library\Monitor\Performance;
use OpenExam\Library\Monitor\Performance\Counter;

/**
 * Apache performance counter.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Apache extends CounterBase implements Counter
{

        /**
         * The counter type.
         */
        const TYPE = 'apache';
        /**
         * The socket statistics counter.
         */
        const SOCKET = 'socket';
        /**
         * The connection state counter.
         */
        const CONNECT_STATE = 'connect-state';
        /**
         * The connection queue counter.
         */
        const CONNECT_QUEUE = 'connect-state';
        /**
         * The accumulated system load counter.
         */
        const STATUS_LOAD = 'status-load';
        /**
         * The total access and transfer bytes counter.
         */
        const STATUS_TOTAL = 'status-total';
        /**
         * The CPU load counter.
         */
        const STATUS_CPU = 'status-cpu';
        /**
         * Teh request counter.
         */
        const STATUS_REQUEST = 'status-request';
        /**
         * The workers counter.
         */
        const STATUS_WORKERS = 'status-workers';

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
                return $this->i18n->_("Apache");
        }

        /**
         * Get counter title (longer name).
         * @return string
         */
        public function getTitle()
        {
                return $this->i18n->_("Apache Web Server");
        }

        /**
         * Get counter description.
         * @return string
         */
        public function getDescription()
        {
                return $this->i18n->_("Performance counter for the Apache Web Server");
        }

        /**
         * Get translated performance counter keys.
         * @return array
         */
        public function getKeys()
        {
                return array(
                        'label'          => $this->getTitle(),
                        'descr'          => $this->getDescription(),
                        'socket'         => array(
                                'label'  => $this->i18n->_("Sockets"),
                                'descr'  => $this->i18n->_("Total number of socket (inclusing closed) by Apache"),
                                'socket' => array(
                                        'label' => $this->i18n->_("Sockets"),
                                        'descr' => $this->i18n->_("Network or UNIX domain sockets")
                                ),
                                'mutex'  => array(
                                        'label' => $this->i18n->_("Mutex"),
                                        'descr' => $this->i18n->_("File system mutex or lock files")
                                ),
                                'event'  => array(
                                        'label' => $this->i18n->_("Event"),
                                        'descr' => $this->i18n->_("Event poll socket")
                                ),
                                'pipe'   => array(
                                        'label' => $this->i18n->_("Pipe"),
                                        'descr' => $this->i18n->_("File system pipe")
                                ),
                                'file'   => array(
                                        'label' => $this->i18n->_("File"),
                                        'descr' => $this->i18n->_("Standard file or device")
                                ),
                                'total'  => array(
                                        'label' => $this->i18n->_("Total"),
                                        'descr' => $this->i18n->_("Total number of files in used")
                                ),
                        ),
                        'connect-state'  => array(
                                'label'       => $this->i18n->_("State"),
                                'descr'       => $this->i18n->_("Socket connection state"),
                                'established' => array(
                                        'label' => $this->i18n->_("Established"),
                                        'descr' => $this->i18n->_("The socket has an established connection.")
                                ),
                                'syn-sent'    => array(
                                        'label' => $this->i18n->_("Syn Sent"),
                                        'descr' => $this->i18n->_("The socket is actively attempting to establish a connection.")
                                ),
                                'syn-recv'    => array(
                                        'label' => $this->i18n->_("Syn Recv"),
                                        'descr' => $this->i18n->_("A connection request has been received from the network.")
                                ),
                                'fin-wait-1'  => array(
                                        'label' => $this->i18n->_("Fin Wait 1"),
                                        'descr' => $this->i18n->_("The socket is closed, and the connection is shutting down.")
                                ),
                                'fin-wait-2'  => array(
                                        'label' => $this->i18n->_("Fin Wait 2"),
                                        'descr' => $this->i18n->_("Connection is closed, and the socket is waiting for a shutdown from the remote end.")
                                ),
                                'time-wait'   => array(
                                        'label' => $this->i18n->_("Time Wait"),
                                        'descr' => $this->i18n->_("The socket is waiting after close to handle packets still in the network.")
                                ),
                                'closed'      => array(
                                        'label' => $this->i18n->_("Closed"),
                                        'descr' => $this->i18n->_("The socket is not being used.")
                                ),
                                'close-wait'  => array(
                                        'label' => $this->i18n->_("Close Wait"),
                                        'descr' => $this->i18n->_("The remote end has shut down, waiting for the socket to close.")
                                ),
                                'last-ack'    => array(
                                        'label' => $this->i18n->_("Last Ack"),
                                        'descr' => $this->i18n->_("The remote end has shut down, and the socket is closed. Waiting for acknowledgement.")
                                ),
                                'listen'      => array(
                                        'label' => $this->i18n->_("Listen"),
                                        'descr' => $this->i18n->_("The socket is listening for incoming connections.")
                                ),
                                'closing'     => array(
                                        'label' => $this->i18n->_("Closing"),
                                        'descr' => $this->i18n->_("Both sockets are shut down but we still don't have all our data sent.")
                                ),
                        ),
                        'connect-queue'  => array(
                                'label'      => $this->i18n->_("Queue"),
                                'descr'      => $this->i18n->_("Socket I/O pending read/write"),
                                'send-bytes' => array(
                                        'label' => $this->i18n->_("Send"),
                                        'descr' => $this->i18n->_("The number of bytes waiting in output buffers to be sent")
                                ),
                                'recv-bytes' => array(
                                        'label' => $this->i18n->_("Receive"),
                                        'descr' => $this->i18n->_("The number of bytes in input buffers waiting to be received")
                                ),
                        ),
                        'status-load'    => array(
                                'label' => $this->i18n->_("Load"),
                                'descr' => $this->i18n->_("Apache process load"),
                                '1min'  => array(
                                        'label' => $this->i18n->_("Last Minute"),
                                        'descr' => $this->i18n->_("Load since last minute")
                                ),
                                '5min'  => array(
                                        'label' => $this->i18n->_("5 Minutes"),
                                        'descr' => $this->i18n->_("Load during last 5 minutes")
                                ),
                                '15min' => array(
                                        'label' => $this->i18n->_("15 Minutes"),
                                        'descr' => $this->i18n->_("Load during last 15 minutes")
                                ),
                        ),
                        'status-total'   => array(
                                'label'  => $this->i18n->_("Total"),
                                'descr'  => $this->i18n->_("Overall transfer and request statistics"),
                                'access' => array(
                                        'label' => $this->i18n->_("Access"),
                                        'descr' => $this->i18n->_("The number of requests made since last restart")
                                ),
                                'kbytes' => array(
                                        'label' => $this->i18n->_("kBytes"),
                                        'descr' => $this->i18n->_("The number of kilo bytes transfered since last restart")
                                ),
                        ),
                        'status-cpu'     => array(
                                'label'  => $this->i18n->_("CPU"),
                                'descr'  => $this->i18n->_("CPU time consumed by the Apache process"),
                                'user'   => array(
                                        'label' => $this->i18n->_("User"),
                                        'descr' => $this->i18n->_("User space CPU time")
                                ),
                                'system' => array(
                                        'label' => $this->i18n->_("System"),
                                        'descr' => $this->i18n->_("Kernel space CPU time")
                                ),
                                'load'   => array(
                                        'label' => $this->i18n->_("Load"),
                                        'descr' => $this->i18n->_("Avarage system load")
                                ),
                        ),
                        'status-request' => array(
                                'label'     => $this->i18n->_("Requests"),
                                'descr'     => $this->i18n->_("Current request footprint"),
                                'num-sec'   => array(
                                        'label' => $this->i18n->_("#/sec"),
                                        'descr' => $this->i18n->_("Requests per second")
                                ),
                                'bytes-sec' => array(
                                        'label' => $this->i18n->_("kB/sec"),
                                        'descr' => $this->i18n->_("Bytes transfered per second")
                                ),
                                'bytes-req' => array(
                                        'label' => $this->i18n->_("kB/req"),
                                        'descr' => $this->i18n->_("Bytes per request in avarage")
                                ),
                        ),
                        'status-workers' => array(
                                'label' => $this->i18n->_("Workers"),
                                'descr' => $this->i18n->_("Number of workers handling incoming connections"),
                                'busy'  => array(
                                        'label' => $this->i18n->_("Busy"),
                                        'descr' => $this->i18n->_("Number of busy workers (in use)")
                                ),
                                'idle'  => array(
                                        'label' => $this->i18n->_("Idle"),
                                        'descr' => $this->i18n->_("Number of idle workers (waiting)")
                                ),
                        ),
                );
        }

        /**
         * Check if sub counter exist.
         * @param string $type The sub counter name.
         * @return boolean
         */
        public function hasCounter($type)
        {
                return
                    $type == self::CONNECT_QUEUE ||
                    $type == self::CONNECT_STATE ||
                    $type == self::SOCKET ||
                    $type == self::STATUS_CPU ||
                    $type == self::STATUS_LOAD ||
                    $type == self::STATUS_REQUEST ||
                    $type == self::STATUS_TOTAL ||
                    $type == self::STATUS_WORKERS;
        }

}
