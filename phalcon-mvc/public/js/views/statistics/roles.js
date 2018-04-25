/* global baseURL */

/*
 * Copyright (C) 2016-2018 The OpenExam Project
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
// File:    roles.js
// Created: 2016-04-28 19:18:16
// 
// Author:  Anders Lövgren (Nowise Systems)
// 

$(document).ready(function () {

    // 
    // The roles object.
    // 
    var roles = (function () {

        // 
        // Current division or null if handling the organization.
        // 
        var division = null;

        // 
        // Show summary.
        // 
        function showSummary(content) {
            showRoles(content.data.roles);
            showExams(content.data.exams);
            showUsers(content.data.users);
        }

        // 
        // Show summary for all roles.
        // 
        function showRoles(content) {
            var dest = $("#organization-roles-list");
            var data = content.data;
            var size = content.count;
            var name = content.label;

            dest.empty();
            dest.append("<h2>" + name + "</h2>");

            for (var r in data) {
                var id = "organization-role-" + r;
                dest.append("<a href='#'><dt id=\"" + id + "\">" + data[r].label + "</dt></a><dd>" + data[r].count + "</dd>");
                $("#" + id).bind("click", function (e, r) {
                    var role = e.target.id.split('-')[2];
                    loadRoles(role);
                    return false;
                });
            }
        }

        // 
        // Show summary for all exams.
        // 
        function showExams(data) {
            var dest = $("#organization-exams-list");

            dest.empty();
            dest.append("<h2>" + data.label + "</h2>");

            var id = "organization-exam";
            dest.append("<a href='#'><dt id=\"" + id + "\">" + data.label + "</dt></a><dd>" + data.count + "</dd>");
            $("#" + id).bind("click", function (e, r) {
                loadExams();
                return false;
            });
        }

        // 
        // Show summary for all users.
        // 
        function showUsers(data) {
            var dest = $("#organization-users-list");

            dest.empty();
            dest.append("<h2>" + data.label + "</h2>");

            delete data.label;

            for (var r in data) {
                if (r === 'label') {
                    continue;
                }
                var id = "organization-user-" + r;
                dest.append("<a href='#'><dt id=\"" + id + "\">" + data[r].label + "</dt></a><dd>" + data[r].count + "</dd>");
                $("#" + id).bind("click", function (e, r) {
                    var type = e.target.id.split('-')[2];
                    loadUsers(type);
                    return false;
                });
            }
        }

        // 
        // Show list of all users. This might be the result from fetching data
        // from one particular role.
        // 
        function showUserList(content) {
            var dest = $("#statistics-data");
            var data = content.data;
            var size = content.size;

            dest.empty();
            dest.append("<hr/>");
            dest.append("<h2>" + content.name + "</h2>");
            dest.append("<table>");
            for (var r in data) {
                if (data[r].mail === undefined) {
                    data[r].mail = "#";
                }
                dest.append("<tr>");
                dest.append("<td><a href='mailto:" + data[r].mail + "'><span class='btn btn-success btn-sm'><i class='fa fa-envelope'></i><span> Contact</span></span></a></td>");
                dest.append("<td class=\"name\">" + data[r].name + "</td>");
                dest.append("<td class=\"user\">" + data[r].user + "</td>");
                dest.append("<td class=\"type\">" + data[r].type + "</td>");
                dest.append("</tr>");
            }
            dest.append("</table>");
        }

        function showExamList(content) {
            var dest = $("#statistics-data");
            var data = content.data;
            var size = content.size;

            dest.empty();
            dest.append("<hr/>");
            dest.append("<table>");
            for (var r in data) {
                if (data[r].starttime === null) {
                    continue;
                }
                dest.append("<tr>");
                dest.append("<td><span class='btn btn-success' style='padding:10px; font-size:11px'><i class='fa fa-users'></i><span> Staff</span></span></td>");
                dest.append("<td class=\"name\">" + data[r].name + "<br/>");
                dest.append("<td class=\"datetime\">" + data[r].starttime + "</td>");
                dest.append("<td class=\"datetime\">" + data[r].endtime + "</td>");
                dest.append("<td class=\"division\">" + data[r].division + "</td>");
                dest.append("</tr>");
            }
            dest.append("</table>");
        }

        // 
        // Get summary data making an AJAX request.
        // 
        function loadSummary() {
            $.ajax({
                type: "GET",
                url: getUrl('summary'),
                success: function (content) {
                    division = null;
                    showSummary(content);
                }
            });
        }

        // 
        // Get division data making an AJAX request.
        // 
        function loadDivision(name) {
            $.ajax({
                type: "GET",
                url: getUrl('summary', name),
                success: function (content) {
                    division = name;
                    showSummary(content);
                }
            });
        }

        // 
        // Get roles data making an AJAX request.
        // 
        function loadRoles(role) {
            $.ajax({
                type: "GET",
                url: getUrl('role', role),
                success: function (content) {
                    showUserList(content);
                }
            });
        }

        // 
        // Get exams data making an AJAX request.
        // 
        function loadExams() {
            $.ajax({
                type: "GET",
                url: getUrl('exams'),
                success: function (content) {
                    showExamList(content);
                }
            });
        }

        // 
        // Get users data making an AJAX request.
        // 
        function loadUsers(type) {
            if (type === 'total') {
                $.ajax({
                    type: "GET",
                    url: getUrl('users'),
                    success: function (content) {
                        showUserList(content);
                    }
                });
            } else {
                $.ajax({
                    type: "GET",
                    url: getUrl(type),
                    success: function (content) {
                        showUserList(content);
                    }
                });
            }

        }

        // 
        // URL builder.
        // 
        function getUrl(action, arg) {
            var url = baseURL + 'utility/statistics/' + action;

            if (arg !== undefined) {
                url += '/' + arg;
            }
            if (division !== null) {
                url += '/' + division;
            }

            return url;
        }

        // 
        // The exported interface (public methods).
        // 
        return {
            displaySummary: function () {
                loadSummary();
            },
            displayDivision: function (name) {
                loadDivision(name);
            }
        };

    }());

    (function () {
        $("#statistics-organization").on("change", function (e, title) {
            roles.displayDivision(title);
        });
        roles.displaySummary();
    }());

});
