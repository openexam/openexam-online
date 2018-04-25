<?php

/*
 * Copyright (C) 2014-2018 The OpenExam Project
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
// File:    ServiceDescription.php
// Created: 2014-10-10 03:38:57
// 
// Author:  Anders Lövgren (Nowise Systems)
// 

namespace OpenExam\Library\WebService\Wsdl;

/**
 * SOAP service description (WSDL).
 * @author Anders Lövgren (Nowise Systems)
 */
class ServiceDescription
{

        /**
         * Format output as HTML.
         */
        const FORMAT_HTML = 'html';
        /**
         * Format output as XML.
         */
        const FORMAT_XML = 'xml';

        /**
         * @var string 
         */
        private $_class;
        /**
         * @var string 
         */
        private $_location;
        /**
         * @var string 
         */
        private $_namespace;
        /**
         * The WSDL generator.
         * @var Generator 
         */
        private $_generator;

        /**
         * Constructor.
         * @param string $class The SOAP service class.
         * @param string $location Service location (endpoint).
         * @param string $namespace Service namespace.
         */
        public function __construct($class, $location = null, $namespace = null)
        {
                $this->_class = $class;
                $this->_location = $location;
                $this->_namespace = $namespace;
        }

        /**
         * Set SOAP service location (endpoint).
         * @param string $location
         */
        public function setServiceLocation($location)
        {
                $this->_location = $location;
        }

        /**
         * Set SOAP service namespace.
         * @param string $namespace
         */
        public function setNamespace($namespace)
        {
                $this->_namespace = $namespace;
        }

        /**
         * Get SOAP service location (endpoint)
         * @return string
         */
        public function getServiceLocation()
        {
                return $this->_location;
        }

        /**
         * Get service description generator.
         * @return Generator
         */
        public function getGenerator()
        {
                if (!isset($this->_generator)) {
                        $this->_generator = new Generator($this->_class, $this->_location, $this->_namespace);

                        $this->_generator->addClassPath('OpenExam\Models');
                        $this->_generator->addClassPath('OpenExam\Library\Security');
                        $this->_generator->addClassPath('OpenExam\Library\WebService\Soap\Types');

                        $this->_generator->discover();
                }
                return $this->_generator;
        }

        /**
         * Get service description (WSDL).
         * @param string $format The output format (html or xml).
         * @return string
         */
        private function getDescription($format)
        {
                $generator = $this->getGenerator();

                switch ($format) {
                        case self::FORMAT_HTML:
                                return $generator->getDocument()->saveHTML();
                        case self::FORMAT_XML:
                                return $generator->getDocument()->saveXML();
                }
        }

        /**
         * Get service description (WSDL).
         * @param string $format The output format (html or xml).
         * @return string
         */
        public function dump($format = self::FORMAT_XML)
        {
                return $this->getDescription($format);
        }

        /**
         * Send service description (WSDL) to stdout.
         * @param string $format The output format (html or xml).
         */
        public function send($format = self::FORMAT_HTML)
        {
                echo $this->getDescription($format);
        }

        /**
         * Save service description (WSDL) to file.
         * @param string $filename The destination file.
         * @param string $format The output format (html or xml).
         */
        public function save($filename, $format = self::FORMAT_XML)
        {
                file_put_contents($filename, $this->getDescription($format));
        }

}
