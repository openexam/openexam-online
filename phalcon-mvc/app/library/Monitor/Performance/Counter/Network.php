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
// File:    Network.php
// Created: 2016-05-31 21:30:45
// 
// Author:  Anders Lövgren (Nowise Systems)
// 

namespace OpenExam\Library\Monitor\Performance\Counter;

use OpenExam\Library\Monitor\Performance;
use OpenExam\Library\Monitor\Performance\Counter;

/**
 * Network performance counter.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Network extends CounterBase implements Counter
{

        /**
         * The counter name.
         */
        const TYPE = 'net';
        /**
         * The receive counter.
         */
        const RECEIVED = 'receive';
        /**
         * The transmit counter.
         */
        const TRANSMIT = 'transmit';

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
                return $this->i18n->_("Network");
        }

        /**
         * Get counter title (longer name).
         * @return string
         */
        public function getTitle()
        {
                return $this->i18n->_("Network Interface (%nic%)", array(
                            'nic' => $this->_performance->getSource()
                ));
        }

        /**
         * Get counter description.
         * @return string
         */
        public function getDescription()
        {
                return $this->i18n->_("Performance counter for network interface");
        }

        /**
         * Get translated performance counter keys.
         * @return array
         */
        public function getKeys()
        {
                return array(
                        'label'    => $this->getTitle(),
                        'descr'    => $this->getDescription(),
                        'receive'  => array(
                                'label'      => $this->i18n->_("Receive"),
                                'descr'      => $this->i18n->_("Information about incoming traffic on network interface"),
                                'bytes'      => array(
                                        'label' => $this->i18n->_("Bytes"),
                                        'descr' => $this->i18n->_("The total number of bytes of data received by the interface")
                                ),
                                'packets'    => array(
                                        'label' => $this->i18n->_("Packets"),
                                        'descr' => $this->i18n->_("The total number of packets of data received by the interface")
                                ),
                                'errs'       => array(
                                        'label' => $this->i18n->_("Errors"),
                                        'descr' => $this->i18n->_("The total number of receive errors detected by the device driver")
                                ),
                                'drop'       => array(
                                        'label' => $this->i18n->_("Dropped"),
                                        'descr' => $this->i18n->_("The total number of packets dropped by the device driver")
                                ),
                                'fifo'       => array(
                                        'label' => $this->i18n->_("FIFO"),
                                        'descr' => $this->i18n->_("The number of FIFO buffer errors (underruns)")
                                ),
                                'frame'      => array(
                                        'label' => $this->i18n->_("Frame"),
                                        'descr' => $this->i18n->_("The number of packet framing errors (e.g. too-long, ring-buffer overflow, CRC or alignment)")
                                ),
                                'compressed' => array(
                                        'label' => $this->i18n->_("Compressed"),
                                        'descr' => $this->i18n->_("The number of compressed packets received by the device driver")
                                ),
                                'multicast'  => array(
                                        'label' => $this->i18n->_("Multicast"),
                                        'descr' => $this->i18n->_("The number of multicast frames transmitted or received by the device driver")
                                ),
                        ),
                        'transmit' => array(
                                'label'      => $this->i18n->_("Transmit"),
                                'descr'      => $this->i18n->_("Information about outgoing traffic on network interface"),
                                'bytes'      => array(
                                        'label' => $this->i18n->_("Bytes"),
                                        'descr' => $this->i18n->_("The total number of bytes of data transmitted by the interface")
                                ),
                                'packets'    => array(
                                        'label' => $this->i18n->_("Packets"),
                                        'descr' => $this->i18n->_("The total number of packets of data transmitted by the interface")
                                ),
                                'errs'       => array(
                                        'label' => $this->i18n->_("Errors"),
                                        'descr' => $this->i18n->_("The total number of transmit errors detected by the device driver")
                                ),
                                'drop'       => array(
                                        'label' => $this->i18n->_("Dropped"),
                                        'descr' => $this->i18n->_("The total number of packets dropped by the device driver")
                                ),
                                'fifo'       => array(
                                        'label' => $this->i18n->_("FIFO"),
                                        'descr' => $this->i18n->_("The number of FIFO buffer errors (overruns)")
                                ),
                                'colls'      => array(
                                        'label' => $this->i18n->_("Collisions"),
                                        'descr' => $this->i18n->_("The number of collisions detected on the interface")
                                ),
                                'carrier'    => array(
                                        'label' => $this->i18n->_("Carrier"),
                                        'descr' => $this->i18n->_("The number of carrier losses detected by the device driver (incl. aborted, window size and heartbeat errors)")
                                ),
                                'compressed' => array(
                                        'label' => $this->i18n->_("Compressed"),
                                        'descr' => $this->i18n->_("The number of compressed packets transmitted by the device driver")
                                )
                        ),
                );
        }

        /**
         * Check if sub counter type exist.
         * @param string $type The sub counter type.
         * @return boolean
         */
        public function hasCounter($type)
        {
                return $type == self::RECEIVED || $type == self::TRANSMIT;
        }

        /**
         * Check if counter uses source field.
         * 
         * The network performance counter supports multiple sources. The 
         * returned list is a variable length list of all interfaces that has 
         * performance data.
         * 
         * @return array
         */
        public function getSources()
        {
                return CounterQuery::getSources($this->getType());
        }

        /**
         * Check if counter uses source field.
         * 
         * The network performance  counter supports multiple sources and will 
         * always return true.
         * 
         * @return boolean
         */
        public function hasSource()
        {
                return true;
        }

}
