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
// File:    AuditTarget.php
// Created: 2016-04-26 22:27:45
// 
// Author:  Anders Lövgren (Nowise Systems)
// 

namespace OpenExam\Library\Model\Audit\Target;

/**
 * Audit target interface.
 * @author Anders Lövgren (Nowise Systems)
 */
interface AuditTarget
{

        /**
         * Save model changes.
         * @param array $changes The model changes.
         * @return int The number of bytes written.
         */
        function write($changes);
}
