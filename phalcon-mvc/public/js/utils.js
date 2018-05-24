/* global Opentip, Function */

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
// File:    utils.js
// 
// Author:  Ahsan Shahzad (Medfarm DoIT)
// Author:  Anders Lövgren (Computing Department at BMC, Uppsala University)
// 

// 
// Application level utility functions.
// 

/**
 * Custom AJAX function.
 * 
 * @param {String} url The target URL.
 * @param {Json} data The request data.
 * @param {string} target [e.g: undefined, 'return', '#id-of-element', '.class-name']
 * @param {type} type [POST, GET] The request method.
 */
var ajax = function (url, data, callback, type, async, showSuccessMsg) {

    type = typeof type !== 'undefined' ? type : 'POST';
    async = typeof async !== 'undefined' ? async : true;
    showSuccessMsg = typeof showSuccessMsg !== 'undefined' ? showSuccessMsg : true;

    var request = $.ajax({
        url: url,
        type: type,
        data: data,
        dataType: "json",
        async: async
    });

    request.done(function (response) {
        // 
        // Check response status:
        // 
        if (typeof response.failed !== "undefined") {
            showMessage(response.failed.return, 'error');
        } else if (typeof response.success !== "undefined") {

            callback(response.success.return);

            if (showSuccessMsg) {
                if (response.success.action === 'create') {
                    showMessage("Data has been inserted.", 'success');
                } else if (response.success.action === 'update') {
                    showMessage("Updated successfully.", 'success');
                } else if (response.success.action === 'delete') {
                    showMessage("Record has been successfully deleted.", 'success');
                }
            }
        } else {
            showMessage('Request failed. Please contact system administrators.', 'error');
        }
    });

    request.fail(function (jqXHR, textStatus) {
        showMessage('Request failed. Please contact system administrators: ' + textStatus, 'error');
        $("#ajax-loader").hide();
    });
};

/**
 * Shows a message for 3 seconds
 * 
 * @param {String} message
 * @param {String} message
 * @returns 
 */
var showMessage = function (message, type) {

    if (type === undefined) {
        type = 'info';
    } else if (type === 'error') {
        type = 'danger';
    }

    if (type === 'success') {
        $('#ajax-message-content').html(message);
        $('#ajax-message')
                .attr('class', 'alert alert-' + type)
                .slideDown(300)
                .delay(1000)
                .slideUp(300);
        $('#ajax-message > a').hide();
    } else {
        $('#ajax-message-content').html(message);
        $('#ajax-message')
                .attr('class', 'alert alert-' + type + ' alert-dismissable')
                .slideDown(300);
        $('#ajax-message > a').show();
    }
};

/**
 * Close/hide opentip dialogs.
 */
var closeToolTips = function ()
{
    for (var i = 0; i < Opentip.tips.length; i++) {
        Opentip.tips[i].hide();
    }
};

/**
 * Open dialog window.
 * 
 * Use dialog on desktop sized windows and fancybox on screens having 
 * width smaller than 750 pixels.
 * 
 * @param {String} target The target selector.
 * @param {String} content The HTML content.
 * @param {Object} options Optional options.
 */
var showDialogWindow = function (target, content, options) {
    var useDialog = $(window).width() > 750;
    var defaults = {
        autoOpen: true,
        width: false,
        height: "auto",
        modal: true
    };

    if (options === undefined) {
        options = defaults;
    } else {
        options = Object.assign({}, defaults, options);
    }

    // 
    // Limit dialog width:
    // 
    if (useDialog && options.width === false) {
        options.width = '600px';
    }

    // 
    // Wrap content in div for fancybox:
    // 
    if (content !== undefined) {
        if (useDialog) {
            $(target).html(content);
        } else {
            $(target).html('<div>' + content + '</div>');
        }
    }

    if (useDialog) {
        $(target).attr('dialog-type', 'jquery-dialog');
        $(target).dialog(options);
    } else {
        $(target).attr('dialog-type', 'fancybox');
        $(target).css('min-height', $(window).height() - 88);
//        $(target).find('.oe-dialog-buttons').addClass('oe-center-bottom');
        $.fancybox.open({
            src: target,
            type: 'inline'
        });
    }
};

/**
 * Close dialog window.
 * 
 * @param {String} target The target selector.
 */
var closeDialogWindow = function (target) {
    if ($(target).attr('dialog-type') === 'jquery-dialog') {
        $(target).dialog('destroy');
    } else {
        $.fancybox.close(true);
    }
};

/**
 * Returns length of JSON object
 * 
 * @param {json} object
 * @returns {Number}
 */
function objectLength(object)
{
    var length = 0;
    for (var key in object) {
        if (object.hasOwnProperty(key)) {
            ++length;
        }
    }
    return length;
}

/**
 * Global loading and events handlers
 * @param {type} param
 */
$(document).ready(function () {

    // 
    // Global function for preventing javascript from running twice.
    // 
    if (Function.prototype.oe_module_loaded === undefined) {
        oe_module_loaded = (function () {
            var modules = [];

            return function (module) {
                if (modules.indexOf(module) === -1) {
                    modules.push(module);
                    return false;
                } else {
                    return true;
                }
            };
        })();
    }

    if (oe_module_loaded("js-utils")) {
        return;
    }

    // 
    // Called on AJAX request start:
    // 
    $(document).ajaxStart(function () {
        $('#ajax-loader').show();
    });

    // 
    // Called on AJAX request stop:
    // 
    $(document).ajaxStop(function () {
        $('#ajax-loader').hide();
    });

    // 
    // Attach fancy box on elements:
    // 
    $('.fancybox').fancybox({
        autoHeight: true,
        autoWidth: true,
        helpers: {
            overlay: {
                closeClick: false
            }
        }
    });

    // 
    // Prevent event bubbling:
    // 
    $(document).on('click', '.prevent', function () {
        return false;
    });

    // 
    // Support for localize float point numbers. Uses the language setting in
    // browser to set locale/language for formatting.
    // 
    if (String.prototype.parsefloat === undefined) {
        String.prototype.parsefloat = function () {
            var input = this.replace(',', '.');
            if (Number.parseFloat !== undefined) {
                return Number.parseFloat(input);
            } else {
                return parseFloat(input);
            }
        };
    }

    if (Number.prototype.parsefloat === undefined) {
        Number.prototype.parsefloat = function () {
            var input = String(this).replace(',', '.');
            if (Number.parseFloat !== undefined) {
                return Number.parseFloat(input);
            } else {
                return parseFloat(input);
            }
        };
    }

    if (String.prototype.floatval === undefined) {
        String.prototype.floatval = function () {
            if (navigator.languages !== undefined) {
                return Number(this).toLocaleString(navigator.languages[0]);
            } else if (navigator.language !== undefined) {
                return Number(this).toLocaleString(navigator.language);
            } else {
                return this;
            }
        };
    }

    if (Number.prototype.floatval === undefined) {
        Number.prototype.floatval = function () {
            if (navigator.languages !== undefined) {
                return Number(this).toLocaleString(navigator.languages[0]);
            } else if (navigator.language !== undefined) {
                return Number(this).toLocaleString(navigator.language);
            } else {
                return this;
            }
        };
    }

    // 
    // Popup print dialog:
    // 
    if (window.location.hash && window.location.hash === '#print') {
        window.print();
    }

    // 
    // Set cookie. To delete a cookie, pass exdays == 0. Create a session cookie by
    // using exdays == -1 (default).
    // 
    function setCookie(cname, cvalue, exdays) {
        exdays = exdays || -1;

        if (exdays < 0) {
            document.cookie = cname + "=" + cvalue + ";path=/";
            return;
        }

        var d = new Date();
        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));

        var expires = "expires=" + d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    }

    // 
    // Get cookie.
    // 
    function getCookie(cname) {
        var name = cname + "=";
        var ca = document.cookie.split(';');

        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) === ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) === 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }

    // 
    // Set high contrast theme.
    // 
    var setHighContrast = function (enable) {
        if (enable) {
            $(document).find('body').addClass("high-contrast");
            setCookie("theme", "high-contrast");
        } else {
            $(document).find('body').removeClass("high-contrast");
            setCookie("theme", "", 0);
        }
    };

    // 
    // Handle high contrast theme toggle on/off:
    // 
    $(document).on('click', '#theme-high-contrast', function () {
        if ($(this).parent().hasClass("active")) {
            setHighContrast(false);
            $(this).parent().removeClass("active");
        } else {
            setHighContrast(true);
            $(this).parent().addClass("active");
        }

        return false;
    });

    // 
    // Handle show about dialog:
    // 
    $(document).on('click', '#help-about', function () {
        $.ajax({
            type: "POST",
            url: baseURL + 'help/about',
            success: function (content) {
                showDialogWindow("#help-about-dialog", content);
            },
            error: function (error) {
                showDialogWindow("#help-about-dialog", error.responseText);
            }
        });
    });

    $(document).on('click', '#about-close', function () {
        closeDialogWindow('#help-about-dialog');
    });

    // 
    // Select locale and language:
    // 
    $(document).on('click', '.lang-select', function () {
        setCookie($(this).data('request'), $(this).data('lang'), -1);
        window.location.reload();
    });

    // 
    // Animate closing bootstrap alert:
    // 
    $('body').on('close.bs.alert', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(e.target).slideUp();
    });

});
