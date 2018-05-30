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
// File:    FileSystem.php
// Created: 2016-06-01 15:18:37
// 
// Author:  Anders Lövgren (Nowise Systems)
// 

namespace OpenExam\Library\Monitor\Performance\Counter;

use OpenExam\Library\Monitor\Performance;
use OpenExam\Library\Monitor\Performance\Counter;

/**
 * File system performance counter.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class FileSystem extends CounterBase implements Counter
{

        /**
         * The counter name.
         */
        const TYPE = 'fs';
        /**
         * The usage counter.
         */
        const USAGE = 'usage';

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
                return $this->i18n->_("File System");
        }

        /**
         * Get counter title (longer name).
         * @return string
         */
        public function getTitle()
        {
                return $this->i18n->_("File System %name%", array(
                            'name' => $this->_performance->getSource()
                ));
        }

        /**
         * Get counter description.
         * @return string
         */
        public function getDescription()
        {
                return $this->i18n->_("Usage information for mounted file system.");
        }

        /**
         * Get translated performance counter keys.
         * @return array
         */
        public function getKeys()
        {
                return array(
                        'label' => $this->getTitle(),
                        'descr' => $this->getDescription(),
                        'usage' => array(
                                'label' => $this->i18n->_("Usage"),
                                'descr' => $this->i18n->_("Total size and used space of file system."),
                                'total' => array(
                                        'label' => $this->i18n->_("Total"),
                                        'descr' => $this->i18n->_("The total size of file system (MB).")
                                ),
                                'used'  => array(
                                        'label' => $this->i18n->_("Used"),
                                        'descr' => $this->i18n->_("The used space in file system (MB).")
                                ),
                                'free'  => array(
                                        'label' => $this->i18n->_("Free"),
                                        'descr' => $this->i18n->_("The free space in file system (MB).")
                                )
                        )
                );
        }

        /**
         * Check if sub counter exist.
         * 
         * @param string $type The sub counter name.
         * @return boolean
         */
        public function hasCounter($type)
        {
                return $type == self::USAGE;
        }

        /**
         * Check if counter uses source field.
         * 
         * The partition performance counter supports multiple sources. The 
         * returned list is a variable length list of all partition names.
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
         * The partition performance counter supports multiple sources and will 
         * always return true.
         * 
         * @return boolean
         */
        public function hasSource()
        {
                return true;
        }

}
