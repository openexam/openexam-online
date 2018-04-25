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
// File:    Import.php
// Created: 2015-04-14 23:48:19
// 
// Author:  Anders Lövgren (Nowise Systems)
// 

namespace OpenExam\Library\Import;

/**
 * The interface for concrete import classes. 
 * @author Anders Lövgren (Nowise Systems)
 */
interface Import
{

        /**
         * Include project properties in import.
         */
        const OPENEXAM_IMPORT_INCLUDE_PROJECT = 1;
        /**
         * Include topics in import.
         */
        const OPENEXAM_IMPORT_INCLUDE_TOPICS = 2;
        /**
         * Include questions in import.
         */
        const OPENEXAM_IMPORT_INCLUDE_QUESTIONS = 4;
        /**
         * Include answers in import.
         */
        const OPENEXAM_IMPORT_INCLUDE_ANSWERS = 8;
        /**
         * Include result in import.
         */
        const OPENEXAM_IMPORT_INCLUDE_RESULTS = 16;
        /**
         * Include roles in import.
         */
        const OPENEXAM_IMPORT_INCLUDE_ROLES = 32;
        /**
         * Include students in import.
         */
        const OPENEXAM_IMPORT_INCLUDE_STUDENTS = 64;
        /**
         * Include resources in import.
         */
        const OPENEXAM_IMPORT_INCLUDE_RESOURCES = 128;
        /**
         * Include access rules in import.
         */
        const OPENEXAM_IMPORT_INCLUDE_ACCESS = 256;
        /**
         * Default include options (project, topics and questions) in import.
         */
        const OPENEXAM_IMPORT_INCLUDE_DEFAULT = 7;
        /**
         * Include all options in import.
         */
        const OPENEXAM_IMPORT_INCLUDE_ALL = 511;
        /**
         * Supported versions of native export/import formats.
         */
        const OPENEXAM_IMPORT_FORMAT_VERSION = '6073';

        /**
         * Prepare import.
         * 
         * This function is called by the import service consumer before 
         * calling read() to start the actual import. Override in child class
         * if house keeping need to be done.
         */
        function open();

        /**
         * Parse current opened import file and save data to exam.
         */
        function read();

        /**
         * Delegate insert to inserter object.
         * @param ImportInsert $inserter The inserter object.
         */
        function insert($inserter);

        /**
         * Finish import.
         * 
         * This function is called by the import service consumer when
         * read() has finixhed the import. Override in child class if house
         * keeping need to be done.
         */
        function close();

        /**
         * Get import data.
         * @return ImportData
         */
        function getData();
}
