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
// File:    Mysql.php
// Created: 2017-01-16 02:13:16
// 
// Author:  Anders Lövgren (QNET/BMC CompDept)
// 

namespace OpenExam\Library\Database\Adapter\Factory;

use OpenExam\Library\Database\Adapter\Deferred\Mysql as MysqlAdapterDeferred;
use Phalcon\Config;
use Phalcon\Db\Adapter\Pdo\Mysql as MysqlAdapterStandard;
use Phalcon\Db\AdapterInterface;

/**
 * MySQL database adapter factory.
 *
 * @author Anders Lövgren (QNET/BMC CompDept)
 */
class Mysql implements AdapterFactory
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
                        return new MysqlAdapterDeferred($config->toArray(), $params);
                } else {
                        return new MysqlAdapterStandard($config->toArray());
                }
        }

}