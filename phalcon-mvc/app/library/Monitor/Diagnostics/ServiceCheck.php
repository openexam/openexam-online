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
// File:    ServiceCheck.php
// Created: 2016-06-02 02:43:42
// 
// Author:  Anders Lövgren (Nowise Systems)
// 

namespace OpenExam\Library\Monitor\Diagnostics;

/**
 * Interface for service checks.
 * @author Anders Lövgren (Nowise Systems)
 */
interface ServiceCheck
{

        /**
         * True if last check has failed.
         * @boolean
         */
        function hasFailed();

        /**
         * Check if service is online.
         * @return boolean
         */
        function isOnline();

        /**
         * Check if service is working.
         * @return boolean
         */
        function isWorking();

        /**
         * Get check result.
         * @return array
         */
        function getResult();
}
