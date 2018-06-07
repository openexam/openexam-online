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
// File:    GettextTask.php
// Created: 2014-09-19 05:14:31
// 
// Author:  Anders Lövgren (Nowise Systems)
// 

namespace OpenExam\Console\Tasks;

use OpenExam\Library\Globalization\Translate\Gettext\Setup;

/**
 * GNU gettext task.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class GettextTask extends MainTask implements TaskInterface
{

        /**
         * Command line options.
         * @var array 
         */
        private $_options;
        /**
         * The gettext setup.
         * @var OpenExam\Library\Locale\Gettext\Setup
         */
        private $_setup;

        /**
         * Initializer hook.
         */
        public function initialize()
        {
                if (!extension_loaded('gettext')) {
                        throw new Exception('The GNU gettext extension is not loaded.');
                }
        }

        public static function getUsage()
        {
                return array(
                        'header'   => 'GNU gettext task (I18N).',
                        'action'   => '--gettext',
                        'usage'    => array(
                                '[--update] [--merge] [--compile] [--generate] | [--all] [--module=name]',
                                '--initialize --locale=name [--module=name] [--lang=str]',
                                '--list',
                                '--clean'
                        ),
                        'options'  => array(
                                '--update'      => 'Extract gettext strings from source.',
                                '--merge'       => 'Merge message catalog and template.',
                                '--compile'     => 'Compile message catalog to binary format.',
                                '--clean'       => 'Clean compiled binary files.',
                                '--initialize'  => 'Initialize new locale directory.',
                                '--generate'    => 'Generate language bindings.',
                                '--list'        => 'Show modules defined in configuration.',
                                '--locale=name' => 'The locale name (e.g. sv_SE).',
                                '--module=name' => 'Set module for current task.',
                                '--lang=str'    => 'Parse files as this language (default to PHP).',
                                '--force'       => 'Force action even if already applied.',
                                '--verbose'     => 'Be more verbose.',
                                '--dry-run'     => 'Just print whats going to be done.'
                        ),
                        'aliases'  => array(
                                '--all' => 'Same as --update --merge --compile together.'
                        ),
                        'examples' => array(
                                array(
                                        'descr'   => 'Force update, merge and compile in one step',
                                        'command' => '--all --force'
                                ),
                                array(
                                        'descr'   => 'Same as above',
                                        'command' => '--update --merge --compile --force'
                                ),
                                array(
                                        'descr'   => 'Initialize swedish locale for the student module',
                                        'command' => '--initialize --module=student --locale=sv_SE.UTF-8'
                                ),
                                array(
                                        'descr'   => 'Initialize english (US) locale for the js module',
                                        'command' => '--initialize --module=js --locale=en_US --lang=javascript'
                                )
                        )
                );
        }

        /**
         * Display usage information.
         */
        public function helpAction()
        {
                parent::showUsage(self::getUsage());
        }

        public function allAction($params = array())
        {
                $this->setOptions($params, 'all');
                $this->_options['update'] = true;
                $this->_options['merge'] = true;
                $this->_options['compile'] = true;
                $this->_options['generate'] = true;
                $this->perform();
        }

        public function cleanAction($params = array())
        {
                $this->setOptions($params, 'clean');
                $this->perform();
        }

        public function compileAction($params = array())
        {
                $this->setOptions($params, 'compile');
                $this->perform();
        }

        public function initializeAction($params = array())
        {
                $this->setOptions($params, 'initialize');
                $this->perform();
        }

        public function listAction($params = array())
        {
                $this->setOptions($params, 'list');
                $this->perform();
        }

        public function mergeAction($params = array())
        {
                $this->setOptions($params, 'merge');
                $this->perform();
        }

        public function updateAction($params = array())
        {
                $this->setOptions($params, 'update');
                $this->perform();
        }

        public function generateAction($params = array())
        {
                $this->setOptions($params, 'generate');
                $this->perform();
        }

        /**
         * Perform all actions.
         */
        private function perform()
        {
                $translate = $this->config->translate;

                if ($this->_options['list']) {
                        foreach ($translate as $module => $params) {
                                $this->flash->notice(sprintf("%s (module)", $module));
                                if ($this->_options['verbose']) {
                                        foreach ($params as $type => $data) {
                                                $this->flash->notice(sprintf("\t+-- %s", $type));
                                                foreach ($data as $path) {
                                                        $this->flash->notice(sprintf("\t\t+-- %s", $path));
                                                }
                                        }
                                }
                        }
                }

                if ($this->_options['initialize']) {
                        if (!$this->_options['locale']) {
                                throw new Exception("Missing --locale argument");
                        }
                        if ($this->_options['module'] && $translate->get($this->_options['module']) == null) {
                                throw new Exception(sprintf("Unknown module %s, see --list", $this->_options['module']));
                        }
                }

                $commands = array();
                $modules = array();

                if ($this->_options['initialize']) {
                        $commands[] = 'initialize';
                }
                if ($this->_options['clean']) {
                        $commands[] = 'clean';
                }
                if ($this->_options['update']) {
                        $commands[] = 'update';
                }
                if ($this->_options['merge']) {
                        $commands[] = 'merge';
                }
                if ($this->_options['compile']) {
                        $commands[] = 'compile';
                }
                if ($this->_options['generate']) {
                        $commands[] = 'generate';
                }

                if ($this->_options['module']) {
                        $modules[] = $this->_options['module'];
                } else {
                        foreach (array_keys($translate->toArray()) as $module) {
                                $modules[] = $module;
                        }
                }

                $setup = new Setup($this, $this->_options);

                foreach ($commands as $command) {
                        foreach ($modules as $module) {
                                $setup->setDomain($module);
                                $setup->$command();
                        }
                }
        }

        /**
         * Set options from task action parameters.
         * @param array $params The task action parameters.
         * @param string $action The calling action.
         */
        private function setOptions($params, $action = null)
        {
                // 
                // Default options.
                // 
                $this->_options = array('verbose' => false, 'force' => false, 'dry-run' => false);

                // 
                // Supported options.
                // 
                $options = array('verbose', 'force', 'dry-run', 'lang', 'all', 'clean', 'compile', 'merge', 'update', 'initialize', 'list', 'locale', 'module');
                $current = $action;

                // 
                // Set defaults.
                // 
                foreach ($options as $option) {
                        $this->_options[$option] = false;
                }

                // 
                // Include action in options (for multitarget actions).
                // 
                if (isset($action)) {
                        $this->_options[$action] = true;
                }

                // 
                // Scan params for both --key and --key=val options.
                // 
                while (($option = array_shift($params))) {
                        if (in_array($option, $options)) {
                                $this->_options[$option] = true;
                                $current = $option;
                        } elseif (in_array($current, $options)) {
                                $this->_options[$current] = $option;
                        } else {
                                throw new Exception("Unknown task action/parameters '$option'");
                        }
                }

                // 
                // Set language if missing or implicit defined by module:
                // 
                if ($this->_options['module'] == 'js') {
                        $this->_options['lang'] = 'javascript';
                }
                if (!$this->_options['lang']) {
                        $this->_options['lang'] = 'php';
                }
        }

}
