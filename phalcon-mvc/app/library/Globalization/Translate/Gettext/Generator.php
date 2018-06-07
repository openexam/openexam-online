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
 * Created: 2018-06-06 16:00:17
 * File:    Generator.php
 * 
 * Author:  Anders Lövgren (Nowise Systems)
 */

namespace OpenExam\Library\Globalization\Translate\Gettext;

/**
 * Interface for language binding generators.
 * @author Anders Lövgren (Nowise Systems)
 */
interface Generator
{

        /**
         * Process locale for module.
         * 
         * @param string $locale The locale name.
         * @param string $module The module name.
         */
        function process($locale, $module);
}
