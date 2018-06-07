<?php

/*
 * Copyright (C) 2018 The OpenExam Project
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

/*
 * Created: 2018-06-06 15:46:56
 * File:    Generate.php
 * 
 * Author:  Anders Lövgren (Nowise Systems)
 */

namespace OpenExam\Library\Globalization\Translate\Gettext\Command;

use OpenExam\Library\Globalization\Exception;
use OpenExam\Library\Globalization\Translate\Gettext\Generator;

/**
 * Generate language bindings.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Generate extends Command
{

        /**
         * The bindings generators.
         * @var array 
         */
        private $_generators = array();

        /**
         * Register a language binding generator.
         * 
         * @param string $module The translation module name.
         * @param Generator $generator The language binding generator.
         */
        public function registerBinding($module, $generator)
        {
                if (!isset($this->_generators[$module])) {
                        $this->_generators[$module] = array();
                }
                
                $this->_generators[$module][] = $generator;
        }

        /**
         * Generate language binding for PO-file.
         * @throws Exception
         */
        public function process()
        {
                foreach ($this->getLocales() as $locale) {
                        $this->processLocale($locale);
                }
        }

        private function processLocale($locale)
        {
                foreach ($this->getModules() as $module) {
                        $this->processModule($locale, $module);
                }
        }

        private function processModule($locale, $module)
        {
                if (isset($this->_generators[$module])) {
                        foreach ($this->_generators[$module] as $generator) {
                                $generator->process($locale, $module);
                        }
                }
                if (isset($this->_generators["*"])) {
                        foreach ($this->_generators["*"] as $generator) {
                                $generator->process($locale, $module);
                        }
                }
        }

}
