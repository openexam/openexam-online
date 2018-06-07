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
 * Created: 2018-06-06 14:34:54
 * File:    JavaScript.php
 * 
 * Author:  Anders Lövgren (Nowise Systems)
 */

namespace OpenExam\Library\Globalization\Translate\Gettext\Generator;

use OpenExam\Library\Globalization\Exception;
use OpenExam\Library\Globalization\Translate\Gettext\Generator;
use Phalcon\Mvc\User\Component;
use const BASE_DIR;

/**
 * JavaScript message file class.
 * 
 * This class takes an gettext (PO-file) as input and generates a JSON file 
 * with the following structure:
 * 
 * <code>
 * { "headers": { ... }, "messages": { ... } };
 * </code>
 * 
 * The headers section contains the ordinary headers from the PO-file with keys
 * in lower case. The messages contains the translation messages where the keys
 * are from the POT-file.
 * 
 * Notice that we don't really care about pluralis as the translation interface 
 * from don't. The gettext class adds pluralis extension, but until we really
 * need them, we stick with simple messages.
 * 
 * Also notice that phalcon uses its own way of expansing parameters during 
 * translation, wheras gettext uses %pos.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class JavaScript extends Component implements Generator
{

        /**
         * Process locale for module.
         * 
         * @param string $locale The locale name.
         * @param string $module The module name.
         */
        public function process($locale, $module)
        {
                $lang = $this->locale->getIdentifier(false, $locale);

                $topdir = $this->config->application->localeDir;
                $locdir = sprintf("%s/%s/LC_MESSAGES", $topdir, $locale);

                $pofile = sprintf("%s/%s.po", $locdir, $module);
                $jsfile = sprintf("%s/public/js/gettext/%s.json", BASE_DIR, $lang);

                $podata = $this->read($pofile);
                $this->write($jsfile, $podata);
        }

        private function read($pofile)
        {
                $data = array(
                        'headers'  => array(),
                        'messages' => array()
                );

                if (!($handle = fopen($pofile, "r"))) {
                        throw new Exception("Failed open $pofile");
                }

                $this->parse($data, $handle);

                if (!fclose($handle)) {
                        throw new Exception("Failed close $pofile");
                }

                return $data;
        }

        private function write($jsfile, $data)
        {
                if (file_put_contents($jsfile, json_encode($data, JSON_HEX_APOS)) === false) {
                        throw new Exception("Failed write $jsfile");
                }
        }

        private function parse(&$data, $handle)
        {
                $matches = array();

                while (($line = fgets($handle))) {
                        $line = trim($line);

                        if ($line[0] == '#' || strlen($line) == 0) {
                                continue;
                        } elseif (preg_match('/"(.*): (.*)\\\n"/', $line, $matches)) {
                                $this->setHeader($data, $matches[1], $matches[2]);
                        } elseif (preg_match('/msgid "(.*)"/', $line, $matches)) {
                                $message = $matches[1];
                        } elseif (preg_match('/msgstr "(.*)"/', $line, $matches)) {
                                $this->addMessage($data, $message, $matches[1]);
                        }
                }
        }

        private function setHeader(&$data, $key, $val)
        {
                $key = strtolower($key);
                $val = trim($val);

                $data['headers'][$key] = $val;
        }

        private function addMessage(&$data, $key, $val)
        {
                if (strlen($key) == 0 || strlen($val) == 0) {
                        return false;
                }

                $data['messages'][$key] = $val;
        }

}
