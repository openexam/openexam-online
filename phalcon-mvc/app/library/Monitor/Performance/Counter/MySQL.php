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
// File:    MySQL.php
// Created: 2016-05-30 18:39:27
// 
// Author:  Anders Lövgren (Nowise Systems)
// 

namespace OpenExam\Library\Monitor\Performance\Counter;

use OpenExam\Library\Monitor\Performance;
use OpenExam\Library\Monitor\Performance\Counter;

/**
 * MySQL performance counter.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class MySQL extends CounterBase implements Counter
{

        /**
         * The counter type.
         */
        const TYPE = 'mysql';
        /**
         * The aborted counter.
         */
        const ABORTED = 'aborted';
        /**
         * The transfer statistics counter.
         */
        const TRANSFER = 'transfer';
        /**
         * The queries summary counter.
         */
        const QUERIES = 'queries';
        /**
         * The transaction summary counter.
         */
        const TRANSACTION = 'transaction';
        /**
         * The connections counter.
         */
        const CONNECTIONS = 'connections';
        /**
         * The threads counter.
         */
        const THREADS = 'threads';

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
                return $this->tr->_("MySQL");
        }

        /**
         * Get counter title (longer name).
         * @return string
         */
        public function getTitle()
        {
                return $this->tr->_("MySQL Database Server");
        }

        /**
         * Get counter description.
         * @return string
         */
        public function getDescription()
        {
                return $this->tr->_("Performance counter for MySQL");
        }

        /**
         * Get translated performance counter keys.
         * @return array
         */
        public function getKeys()
        {
                return array(
                        'label'       => $this->getTitle(),
                        'descr'       => $this->getDescription(),
                        'queries'     => array(
                                'label'  => $this->tr->_("Queries"),
                                'descr'  => $this->tr->_("SQL command execution counters"),
                                'delete' => array(
                                        'label' => $this->tr->_("Delete"),
                                        'descr' => $this->tr->_("Number of DELETE statements executed")
                                ),
                                'insert' => array(
                                        'label' => $this->tr->_("Insert"),
                                        'descr' => $this->tr->_("Number of INSERT statements executed")
                                ),
                                'select' => array(
                                        'label' => $this->tr->_("Select"),
                                        'descr' => $this->tr->_("Number of SELECT queries runned")
                                ),
                                'update' => array(
                                        'label' => $this->tr->_("Update"),
                                        'descr' => $this->tr->_("Number of UPDATE statements executed")
                                ),
                                'slow'   => array(
                                        'label' => $this->tr->_("Slow"),
                                        'descr' => $this->tr->_("The number of queries that have taken more than the predefined number of seconds.")
                                ),
                        ),
                        'transaction' => array(
                                'label'    => $this->tr->_("Transactions"),
                                'descr'    => $this->tr->_("SQL transaction statistics"),
                                'begin'    => array(
                                        'label' => $this->tr->_("Begin"),
                                        'descr' => $this->tr->_("Number of BEGIN statements issued")
                                ),
                                'commit'   => array(
                                        'label' => $this->tr->_("Commit"),
                                        'descr' => $this->tr->_("Number of COMMIT statements issued")
                                ),
                                'rollback' => array(
                                        'label' => $this->tr->_("Rollback"),
                                        'descr' => $this->tr->_("Number of ROLLBACK statements issued")
                                ),
                        ),
                        'threads'     => array(
                                'label'     => $this->tr->_("Threads"),
                                'descr'     => $this->tr->_("Server thread statistics"),
                                'cached'    => array(
                                        'label' => $this->tr->_("Cached"),
                                        'descr' => $this->tr->_("The number of threads in the thread cache.")
                                ),
                                'connected' => array(
                                        'label' => $this->tr->_("Connected"),
                                        'descr' => $this->tr->_("The number of currently open connections.")
                                ),
                                'created'   => array(
                                        'label' => $this->tr->_("Created"),
                                        'descr' => $this->tr->_("The number of threads created to handle connections.")
                                ),
                                'running'   => array(
                                        'label' => $this->tr->_("Running"),
                                        'descr' => $this->tr->_("The number of threads that are not sleeping.")
                                ),
                        ),
                        'connections' => array(
                                'label'    => $this->tr->_("Connections"),
                                'descr'    => $this->tr->_("Network connection statistics"),
                                'total'    => array(
                                        'label' => $this->tr->_("Total"),
                                        'descr' => $this->tr->_("The number of connection attempts (successful or not) to the MySQL server.")
                                ),
                                'max-used' => array(
                                        'label' => $this->tr->_("Max Used"),
                                        'descr' => $this->tr->_("The maximum number of connections that have been in use simultaneously since the server started.")
                                ),
                        ),
                        'transfer'    => array(
                                'label'      => $this->tr->_("Transfer"),
                                'descr'      => $this->tr->_("Network I/O transfer statistics"),
                                'bytes-recv' => array(
                                        'label' => $this->tr->_("Bytes Received"),
                                        'descr' => $this->tr->_("The number of bytes received from all clients.")
                                ),
                                'bytes-sent' => array(
                                        'label' => $this->tr->_("Bytes Sent"),
                                        'descr' => $this->tr->_("The number of bytes sent to all clients.")
                                ),
                        ),
                        'aborted'     => array(
                                'label'    => $this->tr->_("Aborted"),
                                'descr'    => $this->tr->_("Connection aborted statistics"),
                                'clients'  => array(
                                        'label' => $this->tr->_("Clients"),
                                        'descr' => $this->tr->_("The number of connections that were aborted because the client died without closing the connection properly.")
                                ),
                                'connects' => array(
                                        'label' => $this->tr->_("Connects"),
                                        'descr' => $this->tr->_("The number of failed attempts to connect to the MySQL server.")
                                ),
                        )
                );
        }

        /**
         * Check if sub counter exist.
         * @param string $type The sub counter name.
         * @return boolean
         */
        public function hasCounter($type)
        {
                
        }

}
