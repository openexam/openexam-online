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
// File:    Connection.php
// Created: 2014-10-22 04:21:36
// 
// Author:  Anders Lövgren (Nowise Systems)
// 

namespace OpenExam\Library\Catalog\Service\Adapter\Ldap;

use OpenExam\Library\Catalog\Exception;
use OpenExam\Library\Catalog\Service\Connection as ServiceConnection;

/**
 * LDAP server connection class.
 * 
 * @property-read resource $handle The LDAP server connection.
 * @author Anders Lövgren (Nowise Systems)
 */
class Connection implements ServiceConnection
{

        /**
         * The LDAP connection.
         * @var resource 
         */
        private $_handle;
        /**
         * The LDAP server hostname.
         * @var string 
         */
        private $_host;
        /**
         * The LDAP server port.
         * @var int 
         */
        private $_port;
        /**
         * The LDAP bind username.
         * @var string 
         */
        private $_user;
        /**
         * The LDAP bind password.
         * @var string 
         */
        private $_pass;
        /**
         * LDAP_OPT_XXX options.
         * @var array 
         */
        private $_options;

        /**
         * Constructor.
         * @param string $host The LDAP server hostname.
         * @param string $port The LDAP server port.
         * @param string $user The LDAP bind username.
         * @param string $pass The LDAP bind password.
         * @param array $options Array of LDAP_OPT_XXX options.
         */
        public function __construct($host, $port = 636, $user = null, $pass = null, $options = array())
        {
                $this->_host = $host;
                $this->_port = $port;
                $this->_user = $user;
                $this->_pass = $pass;
                $this->_options = $options;
        }

        /**
         * Destructor.
         */
        public function __destruct()
        {
                unset($this->_handle);

                unset($this->_host);
                unset($this->_port);
                unset($this->_user);
                unset($this->_pass);

                unset($this->_options);
        }

        public function __get($name)
        {
                if ($name == 'handle') {
                        return $this->getConnection();
                }
        }

        /**
         * Get LDAP connection.
         */
        public function getConnection()
        {
                if (!$this->connected()) {
                        $this->open();
                }
                return $this->_handle;
        }

        /**
         * Set LDAP connection option.
         * @param string $name The LDAP_OPT_XXX constant.
         * @param mixed $value The option value.
         */
        public function setOption($name, $value)
        {
                $this->_options[$name] = $value;
        }

        /**
         * Open connection to LDAP server.
         */
        public function open()
        {
                if (($this->_handle = ldap_connect($this->_host, $this->_port)) == false) {
                        throw new Exception(sprintf(
                            "Failed connect to LDAP server %s:%d", $this->_host, $this->_port
                        ));
                }

                foreach ($this->_options as $name => $value) {
                        if (ldap_set_option($this->_handle, $name, $value) == false) {
                                throw new Exception(ldap_error($this->_handle), ldap_errno($this->_handle));
                        }
                }

                if (@ldap_bind($this->_handle, $this->_user, $this->_pass) == false) {
                        throw new Exception(ldap_error($this->_handle), ldap_errno($this->_handle));
                }

                return true;
        }

        /**
         * Close connection to LDAP server.
         */
        public function close()
        {
                if (ldap_unbind($this->_handle) == false) {
                        throw new Exception(ldap_error($this->_handle), ldap_errno($this->_handle));
                }
        }

        /**
         * Check if connected to LDAP server.
         * @return bool
         */
        public function connected()
        {
                return is_resource($this->_handle);
        }

        /**
         * Get connection hostname.
         * @return string
         */
        public function hostname()
        {
                return $this->_host;
        }

        /**
         * Get connection port.
         * @return int
         */
        public function port()
        {
                return $this->_port;
        }

}
