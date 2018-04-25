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
// File:    Sqlite.php
// Created: 2017-01-16 02:22:18
// 
// Author:  Anders Lövgren (Nowise Systems)
// 

namespace OpenExam\Library\Database\Adapter\Factory;

use OpenExam\Library\Database\Adapter\Deferred\Sqlite as SqliteAdapterDeferred;
use Phalcon\Config;
use Phalcon\Db\Adapter\Pdo\Sqlite as SqliteAdapterStandard;
use Phalcon\Db\AdapterInterface;

/**
 * SQLite database adapter factory.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Sqlite implements AdapterFactory
{

        /**
         * Get database adapter.
         * 
         * @param Config $config The adapter configuration.
         * @param Config $params The connection parameters.
         * @return AdapterInterface 
         */
        public function createAdapter($config, $params = null)
        {
                if (isset($params)) {
                        return new SqliteAdapterDeferred($config->toArray(), $params);
                } else {
                        return new SqliteAdapterStandard($config->toArray());
                }
        }

}
