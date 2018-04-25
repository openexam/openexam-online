<?php

/*
 * Copyright (C) 2017-2018 The OpenExam Project
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
// File:    Oracle.php
// Created: 2017-01-10 02:12:39
// 
// Author:  Anders Lövgren (Nowise Systems)
// 

namespace OpenExam\Library\Database\Adapter\Deferred;

use OpenExam\Library\Database\Adapter\Factory\AdapterFactory;
use Phalcon\Config;
use Phalcon\Db\Adapter\Pdo\Oracle as OracleAdapter;
use Phalcon\Db\AdapterInterface;
use Phalcon\Db\Dialect\Oracle as OracleDialect;
use Phalcon\Db\DialectInterface;

/**
 * Caching Oracle database adapter with deferred connection.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Oracle extends DeferredAdapter implements AdapterFactory
{

        /**
         * The database dialect.
         * @var DialectInterface 
         */
        private $_dialect;

        /**
         * Constructor.
         * @param array $config The adapter options.
         * @param Config $params The connection parameters.
         */
        public function __construct($config, $params)
        {
                parent::__construct($config, $params);
                $this->_dialect = new OracleDialect();
        }

        /**
         * Get database adapter.
         * 
         * @param array $config The connection options.
         * @param array $params Other parameters (unused).
         * @return AdapterInterface
         */
        public function createAdapter($config, $params = null)
        {
                return new OracleAdapter($config);
        }

        /**
         * Get adapter type.
         * @return string
         */
        public function getType()
        {
                return "oci";
        }

        /**
         * Get database dialect.
         * @return DialectInterface
         */
        public function getDialect()
        {
                return $this->_dialect;
        }

}
