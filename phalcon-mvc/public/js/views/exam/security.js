/* global examId, baseURL, i18n */

/*
 * Copyright (C) 2015-2018 The OpenExam Project
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
// File:    security.js
// Created: 2015-04-07 22:56:58
// 
// Author:  Anders Lövgren (Nowise Systems)
// 

// 
// Exam security management.
// 

$(document).ready(function () {

    // 
    // Show dialog:
    // 
    $('.exam-security').click(function () {
        $.ajax({
            type: "POST",
            data: {exam_id: examId},
            url: baseURL + 'exam/security',
            success: function (content) {
                showDialogWindow("#exam-security-box", content);
            }
        });

    });

    // 
    // Enable/disable lockdown:
    // 
    $('body').on('click', "#lockdown-enable", function () {
        var container = $(this).closest('.exam-security-box');

        if ($(this).is(":checked")) {
            container.find('.locations').removeClass('oe-disabled');
            container.find('.lockdown-method').removeClass('oe-disabled');
        } else {
            container.find('.locations').addClass('oe-disabled');
            container.find('.lockdown-method').addClass('oe-disabled');
        }
    });

    // 
    // Remove location entry from list:
    // 
    $('body').on('click', '#location-remove', function () {
        $(this).closest('tr').attr('entry', 'remove');
        $(this).closest('tr').hide();
    });

    // 
    // Add (insert) location entry in list from popup:
    // 
    $('body').on('click', '#location-add', function () {
        var button = "<span id='location-remove' class='btn btn-success' style='padding:2px; font-size:11px; min-width: 6em'><i class='fa fa-cut'></i><span>" + i18n.gettext("Remove") + "</span></span>";
        var item = $(this).closest('li');
        $('body').find('#locations-table').append("<tr entry='new'><td contenteditable='true'>" + item.attr('disp') + "</td><td contenteditable='true'>" + item.attr('addr').replace(';', '<br/>') + "</td><td>" + button + "</td></tr>");
    });

    // 
    // Create new empty location entry in list:
    // 
    $('body').on('click', '#location-new', function () {
        var button = "<span id='location-remove' class='btn btn-success' style='padding:2px; font-size:11px; min-width: 6em'><i class='fa fa-cut'></i><span>" + i18n.gettext("Remove") + "</span></span>";
        $('body').find('#locations-table').append("<tr entry='new'><td contenteditable='true' placeholder='" + i18n.gettext("Write location name") + "'></td><td contenteditable='true' placeholder='" + i18n.gettext("Replace with IP-address") + "'></td><td>" + button + "</td></tr>");
    });

    // 
    // Display insert entry popup:
    // 
    $('body').on('click', '#location-insert', function () {
        showDialogWindow('#locations-list');
    });

    // 
    // Display details in entry popup:
    // 
    $('body').on('click', '#locations-details', function () {
        $('.location-addresses').toggle('fast');
    });

    // 
    // Save settings (if requested) and close dialog:
    // 
    $('body').on('click', "#close-security", function () {
        if ($(this).hasClass("save")) {
            var container = $(this).closest('.exam-security-box');

            // 
            // Save lockdown parameters:
            // 
            var lockdown = {
                enable: container.find('#lockdown-enable').is(':checked'),
                method: container.find('#lockdown-method').val()
            };

            ajax(
                    baseURL + 'ajax/core/creator/exam/update',
                    {
                        id: examId,
                        lockdown: JSON.stringify(lockdown)
                    }, function () {
            }, 'POST', true, false);

            // 
            // Save access list (from table):
            // 
            var add = [], update = [], remove = [];
            container.find('#locations-table tr').each(function () {
                var item = $(this);
                switch (item.attr('entry')) {
                    case 'new':
                        add.push({
                            exam_id: examId,
                            name: item.find('td:eq(0)').text().replace(/\s*\-\>\s*/g, ';'),
                            addr: item.find('td:eq(1)').html().replace(/\<br\/?\>/g, ';')
                        });
                        break;
                    case 'update':
                        update.push({
                            id: item.attr('id'),
                            name: item.find('td:eq(0)').text().replace(/\s*\-\>\s*/g, ';'),
                            addr: item.find('td:eq(1)').html().replace(/\<br\/?\>/g, ';')
                        });
                        break;
                    case 'remove':
                        remove.push({
                            id: item.attr('id')
                        });
                        break;
                }
            });

            if (add.length > 0) {
                ajax(
                        baseURL + 'ajax/core/creator/access/create',
                        JSON.stringify(add), function () {
                }, 'POST', true, false);
            }
            if (update.length) {
                ajax(
                        baseURL + 'ajax/core/creator/access/update',
                        JSON.stringify(update), function () {
                }, 'POST', true, false);
            }
            if (remove.length) {
                ajax(
                        baseURL + 'ajax/core/creator/access/delete',
                        JSON.stringify(remove), function () {
                }, 'POST', true, false);
            }

            showMessage(i18n.gettext('Security settings updated successful'), 'success');
        }

        closeDialogWindow("#exam-security-box");
    });

});
