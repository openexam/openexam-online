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
// File:    Counter.php
// Created: 2016-05-24 02:16:19
// 
// Author:  Anders Lövgren (Nowise Systems)
// 

namespace OpenExam\Library\Monitor\Performance;

/**
 * Interface for performance counters.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
interface Counter
{

        /**
         * Get counter type.
         * @return string
         */
        function getType();

        /**
         * Get counter name.
         * @return string
         */
        function getName();

        /**
         * Get counter title.
         * @return string
         */
        function getTitle();

        /**
         * Get counter description.
         * @return string
         */
        function getDescription();

        /**
         * Get performance data.
         * @return array
         */
        function getData();

        /**
         * Get performance counter keys.
         * @return array
         */
        function getKeys();

        /**
         * Check if performance counter for sub type exists.
         * @param string $type The counter sub type.
         * @return boolean
         */
        function hasCounter($type);

        /**
         * Get sub type counter.
         * @param string $type The counter type.
         * @return Counter 
         */
        function getCounter($type);

        /**
         * Check if counter uses source field.
         * 
         * The returned value is an array containing all available source
         * names (from the source field). If counter don't support source,
         * then they should return null.
         * 
         * @return boolean
         */
        function hasSource();

        /**
         * Get all source names for this counter.
         * @return array
         */
        function getSources();

        /**
         * Get all addresses for this counter grouped by address and hostname.
         * @return array
         */
        function getAddresses();
}
