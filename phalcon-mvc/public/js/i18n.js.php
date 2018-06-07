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
 * Created: 2018-06-04 18:30:19
 * File:    i18n.js
 * 
 * Author:  Anders Lövgren (Nowise Systems)
 */

// 
// Provides gettext translation capabilities to javascript similar to the translate 
// class for PHP. Pass the lang GET parameter to this script to select correct JSON 
// message catalog:
// 
//      i18n.js.pgh?lang=sv&rev=1
//      i18n.js.pgh?lang=en&rev=1
//      i18n.js.pgh?lang=en-us&rev=1
//      
// See i18n.js for usage examples and module API. We are sending cache control
// headers to refrain ponding the server on each request. Either disable cache
// or touch message catalog to force cache update in browsers.
// 
// Increment the rev=num parameter if the module internal API has been changed. 
// 

if (filter_has_var(INPUT_GET, 'lang')) {
        $filename = sprintf("gettext/%s.json", filter_input(INPUT_GET, 'lang'));
} else {
        $filename = sprintf("gettext/%s.json", "en");
}

if (file_exists($filename)) {
        $messages = file_get_contents($filename);
        $filetime = md5(filemtime($filename));
} else {
        $messages = '{"header":{},"messages":{}}';
        $filetime = md5($messages);
}

if (($data = json_decode($messages))) {
        $messages = json_encode($data->messages, JSON_HEX_APOS);
} else {
        $messages = json_encode(array());
}

// 
// Cache content for maximum one week:
// 
header("Cache-Control: max-age=604800");
header("ETag: $filetime");

// 
// Compose the module API:
// 
readfile("i18n.js");
printf("var i18n = i18n_translate(JSON.parse('%s'));\n", $messages);
