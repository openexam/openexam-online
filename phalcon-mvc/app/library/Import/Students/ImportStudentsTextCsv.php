<?php

/*
 * Copyright (C) 2015-2018 The OpenExam Project
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
// File:    ImportStudentsTextCsv.php
// Created: 2015-04-15 00:31:09
// 
// Author:  Anders Lövgren (Nowise Systems)
// 

namespace OpenExam\Library\Import\Students;

use PHPExcel_Reader_CSV;

/**
 * Import students from CSV (comma separated values) file.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class ImportStudentsTextCsv extends ImportStudents
{

        private static $_mimedef = "text/csv";

        public function __construct($accept = "")
        {
                parent::__construct(self::$_mimedef);
        }

        public function open()
        {
                $this->_reader = new PHPExcel_Reader_CSV();
                $this->_reader->setReadDataOnly(true);
        }

}
