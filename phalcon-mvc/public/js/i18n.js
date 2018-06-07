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
// The I18N module. The message strings follows the same parameter
// expansion as used by the PHP translate class.
// 
// Usage:   i18n._("hello");        // Ugly ;-(
//          i18n.gettext("hello");
//          i18n.gettext("hello, %name%", { name: "anders" });
// 

function i18n_translate(m) {

    var _messages = m;

    function _substitute(msgid, params) {
        if (msgid.includes("%") === false) {
            return msgid;
        }

        for (var param in params) {
            msgid = msgid.split("%" + param + "%").join(params[param]);
        }

        return msgid;
    }

    function _gettext(msgid, params) {
        if (_messages[msgid]) {
            return _substitute(_messages[msgid], params);
        } else {
            return _substitute(msgid, params);
        }
    }

    // 
    // Public interface:
    // 
    return {
        // 
        // Translate string.
        // 
        gettext: function (msgid, params) {
            return _gettext(msgid, params);
        },
        // 
        // Check if key exist.
        // 
        exist: function (msgid) {
            return _messages[msgid] !== undefined;
        },
        // 
        // Query translation string.
        // 
        query: function (msgid, params) {
            return _gettext(msgid, params);
        },
        // 
        // Compatibility function.
        // 
        _: function (msgid, params) {
            return _gettext(msgid, params);
        }
    };

}
;
