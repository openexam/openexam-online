/* global filter, expand, baseURL, i18n */

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
// File:    index.js
// 
// Author:  Ahsan Shahzad (Medfarm DoIT)
// Author:  Anders Lövgren (Computing Department at BMC, Uppsala University)
// 

// 
// Javascript specific to exam index.
// 

$(document).ready(function () {

    // 
    // Change exams start and end time.
    // 
    $(document).on('click', '.change-time', function () {
        var container = $(this).closest('.list-group-item');
        var exam = $(container).attr('data-id');

        ajax(
                baseURL + 'ajax/core/invigilator/exam/read',
                {
                    id: exam
                },
                function (response) {
                    if (response) {
                        var stime = response.starttime;
                        var etime = response.endtime;

                        if (stime) {
                            stime = stime.replace(/(:\d+):\d{2}$/, '$1');
                        }
                        if (etime) {
                            etime = etime.replace(/(:\d+):\d{2}$/, '$1');
                        }

                        var changer = $("#exam-datetime-changer");

                        changer.find("#exam-starttime").val(stime);
                        changer.find("#exam-endtime").val(etime);

                        $('.datetime-exam').datetimepicker({
                            format: 'YYYY-MM-DD HH:mm'
                        });

                        changer.dialog({
                            autoOpen: true,
                            modal: true,
                            width: 550,
                            buttons: {
                                OK: function () {

                                    stime = changer.find("#exam-starttime").val();
                                    etime = changer.find("#exam-endtime").val();

                                    if (stime.length === 0) {
                                        stime = null;
                                    }
                                    if (etime.length === 0) {
                                        etime = null;
                                    }

                                    ajax(
                                            baseURL + 'ajax/core/invigilator/exam/update',
                                            {
                                                id: exam,
                                                starttime: stime,
                                                endtime: etime
                                            },
                                            function (status) {
                                                if (status) {
                                                    changer.dialog('close');
                                                    readSectionIndex();
                                                }
                                            });
                                },
                                Cancel: function () {
                                    changer.dialog('destroy');
                                }
                            },
                            close: function () {
                                changer.dialog('destroy');
                            }
                        });
                    }
                });
    });

    // 
    // Handle exam progress button click in exam archive.
    //
    $(document).on('click', '.exam-progress', function () {

        if ($(this).hasClass('creator') && $(this).hasClass('upcoming') || $(this).hasClass('running')) {
            var prompt =
                    i18n.gettext("This exam is not yet published. ") +
                    i18n.gettext("If you chose to publish it now, then it will \nshow up as an upcoming exam for student but can't be opened by them \nbefore the exam actually starts.") + "\n\n" +
                    i18n.gettext("Do you want to publish it?");

            var result = confirm(prompt);

            if (result) {
                ajax(
                        baseURL + 'ajax/core/creator/exam/update',
                        {
                            id: $(this).attr('data-id'),
                            published: 1
                        },
                        function (status) {
                            if (status) {
                                readSectionIndex();
                            }
                        }
                );
            }
        }

        if ($(this).hasClass('creator') && $(this).hasClass('published')) {
            var prompt =
                    i18n.gettext("This exam has been published. ") +
                    i18n.gettext("If you chose to revoke the publishing, then it will \nno longer show up as an upcoming exam for students.") + "\n\n" +
                    i18n.gettext("Do you want to unpublish it?");

            var result = confirm(prompt);

            if (result) {
                ajax(
                        baseURL + 'ajax/core/creator/exam/update',
                        {
                            id: $(this).attr('data-id'),
                            published: 0
                        },
                        function (status) {
                            if (status) {
                                readSectionIndex();
                            }
                        }
                );
            }
        }

        if ($(this).hasClass('decoder') && $(this).hasClass('corrected')) {
            var prompt =
                    i18n.gettext("All answers has been corrected on this exam. ") +
                    i18n.gettext("If you chose to continue and \ndecode the exam, then no more correction can be done.") + "\n\n" +
                    i18n.gettext("Do you want to continue and decode it?");

            var result = confirm(prompt);

            if (result) {
                ajax(
                        baseURL + 'ajax/core/decoder/exam/update',
                        {
                            id: $(this).attr('data-id'),
                            decoded: 1
                        },
                        function (status) {
                            if (status) {
                                readSectionIndex();
                            }
                        }
                );
            }
        }

        return false;
    });

    // 
    // Toggle display of exam details.
    // 
    $(document).on('click', '.exam-state-show', function () {
        var exam = $(this).closest('li').attr('data-id');
        var role = $(this).closest('div[section-role]').attr('section-role');
        var target = $(this).closest('li').children('.exam-state-view').children('div');

        target.toggle();

        if (target.is(":visible")) {
            $.ajax({
                type: "POST",
                data: {role: role},
                url: baseURL + 'exam/details/' + exam,
                success: function (response) {
                    target.html(response);
                }
            });
        }

        return false;
    });

    var toggleExamDetails = function (button, target) {
        var container = button.closest('.exam-details');
        container.find('.exam-properties').hide();
        container.find('.exam-properties.' + target).fadeIn(100).show();
    };

    // 
    // Toggle display of phase view in exam details.
    // 
    $(document).on('click', '.exam-state', function () {
        toggleExamDetails($(this), 'state');
    });

    // 
    // Toggle display of staff view in exam details.
    // 
    $(document).on('click', '.exam-staff', function () {
        toggleExamDetails($(this), 'staff');
    });

    // 
    // Toggle display of roles view in exam details.
    // 
    $(document).on('click', '.exam-roles', function () {
        toggleExamDetails($(this), 'roles');
    });

    // 
    // Toggle display of other view in exam details.
    // 
    $(document).on('click', '.exam-other', function () {
        toggleExamDetails($(this), 'other');
    });

    // 
    // Toggle display of grade view in exam details.
    // 
    $(document).on('click', '.exam-grade', function () {
        toggleExamDetails($(this), 'grade');
    });

    // 
    // Show student management dialog:
    // 
    $(document).on('click', '.manage-students', function () {
        $.ajax({
            type: "POST",
            data: {exam_id: $(this).attr('data-id')},
            url: baseURL + 'exam/students/',
            success: function (content) {
                showDialogWindow("#manage-students", content, {width: 780});
            }
        });
    });

    // 
    // Show exam check dialog:
    // 
    $(document).on('click', '.check-exam', function () {
        $.ajax({
            type: "POST",
            data: {
                exam_id: $(this).attr('data-id'),
                readonly: 1
            },
            url: baseURL + 'exam/check',
            success: function (content) {
                showDialogWindow("#exam-check-box", content);
            }
        });
    });

    // 
    // Show render student result dialog:
    // 
    $(document).on('click', '.render-student', function () {
        var examId = $(this).attr('data-id');
        $.ajax({
            type: "POST",
            url: baseURL + 'utility/render/student/' + examId,
            success: function (content) {
                showDialogWindow("#render-student-result", content);
            }
        });
    });

    // 
    // Show render decoder result dialog:
    // 
    $(document).on('click', '.render-decoder', function () {
        var examId = $(this).attr('data-id');
        $.ajax({
            type: "POST",
            url: baseURL + 'utility/render/decoder/' + examId,
            success: function (content) {
                showDialogWindow("#render-decoder-result", content);
            }
        });
    });

    // 
    // Show exam duplicate dialog:
    // 
    $(document).on('click', '.reuse-exam', function () {
        var examId = $(this).closest('.list-group-item').attr('data-id');
        var dialog = $("#reuse-exam-dialog").dialog({
            autoOpen: true,
            modal: true,
            buttons: {
                "OK": function () {

                    var data = {'options[]': []};
                    $('.exam-reuse-opt').filter(':checked').each(function () {
                        data['options[]'].push($(this).val());
                    });
                    $.ajax({
                        type: "POST",
                        data: data,
                        url: baseURL + 'exam/replicate/' + examId,
                        success: function (response) {
                            if (response.status === 'success') {
                                location.href = baseURL + 'exam/update/' + response.exam_id + '/creator';
                            } else {
                                alert(response.message);
                            }
                        },
                        error: function (xhr) {
                            dialog.dialog("option", "buttons", {});
                            dialog.html(xhr.responseText);
                            dialog.show();
                        }
                    });
                },
                Cancel: function () {
                    $(this).dialog('destroy');
                }
            },
            close: function () {
                $(this).dialog('destroy');
            }
        });

    });

    // 
    // Prompt before delete exam:
    // 
    $(document).on('click', '.del-exam', function () {
        var examLine = $(this).closest('.list-group-item');
        var examId = $(examLine).attr('data-id');
        var examName = $(examLine).find('.exam-name').html();

        if (confirm(
                i18n.gettext("Are you sure you want to delete exam: '%exam%'", {
                    exam: jQuery.trim(examName)
                })
                )) {

            ajax(
                    baseURL + 'ajax/core/creator/exam/delete',
                    {id: examId},
                    function (examData) {
                        $(examLine).slideUp(500, function () {
                            $(this).remove();
                            readSectionIndex();
                        });
                    },
                    "POST",
                    true,
                    false
                    );
        }
    });

    // 
    // Simple delay function:
    // 
    var delay = (function () {
        var timer = 0;
        return function (callback, ms) {
            clearTimeout(timer);
            timer = setTimeout(callback, ms);
        };
    })();

    // 
    // On search string input:
    // 
    $(document).on('keyup', '.exam-search-box', function (e) {
        var element = $(this).find('input');
        data.search = $(this).find('input').val().trim();

        delay(function () {
            if (element.val().length > 1 || element.val().length === 0) {

                data.first = 1;
                var source = element.closest('article').parent();

                showSectionIndex(source);
            }
        }, 500);
        return false;
    });

    $(document).on('click', '.search-exam', function () {
        // Show advanced search options.
    });

    // 
    // On order by field changed:
    // 
    $(document).on('change', '.exam-order-by', function () {
        var source = $(this).closest('article').parent();
        data.order = $(this).val();

        showSectionIndex(source);
        return false;
    });

    // 
    // On sort order changed:
    // 
    $(document).on('click', '.exam-sort-order', function () {
        if ($(this).hasClass('fa-arrow-circle-down')) {
            $(this).removeClass('fa-arrow-circle-down').addClass('fa-arrow-circle-up');
            $(this).attr('sort', 'asc');
        } else {
            $(this).removeClass('fa-arrow-circle-up').addClass('fa-arrow-circle-down');
            $(this).attr('sort', 'desc');
        }

        var source = $(this).closest('article').parent();
        data.sort = $(this).attr('sort');

        showSectionIndex(source);
        return false;
    });

    // 
    // On page index clicked:
    // 
    $(document).on('click', '.pagination > li', function () {

        $(this).closest('.pagination').find('li').removeClass('active');
        $(this).addClass('active');

        var source = $(this).closest('article').parent();
        data.first = Number($(this).attr('page'));

        showSectionIndex(source);
        return false;
    });

    // 
    // Filter options for current section.
    // 
    var data = Object.assign({}, filter);

    // 
    // Initialize section data:
    // 
    $('[section-type]').each(function () {
        // 
        // Store filter options in section element:
        // 
        var options = Object.assign({}, filter);

        options.sect = $(this).attr('section-type');
        options.role = $(this).attr('section-role');
        options.state = Number($(this).attr('section-state'));

        $(this).data(options);

        // 
        // Set filter data to last:
        // 
        data = $(this).data();
    });

    // 
    // On accordion tab expanded:
    // 
    $('input[type="radio"]').on('click', function () {
        var parent = $(this).parent();
        var target = parent.find('.exam-listing-area');

        // 
        // Use section filter options:
        // 
        data = parent.data();

        // 
        // Check if already initialized:
        // 
        if (target.children().length > 0) {
            return;
        }

        // 
        // Show this section with delay for animation:
        // 
        delay(function () {
            showSectionIndex(parent);
        }, 200);
    });

    // 
    // Show exam index. The source parameter is the containing div.
    // 
    var showSectionIndex = function (source) {
        var target = source.find('.exam-listing-area');
        var type = source.attr('section-type');

        loadSectionIndex(target, type);
    };

    // 
    // Read exam index. Call this function to reload section using current selected
    // filter options from the data variable.
    // 
    var readSectionIndex = function () {
        if (data.sect) {
            var source = $(document).find('div[section-type="' + data.sect + '"]');
            showSectionIndex(source);
        }
    };

    // 
    // Load exam listing.
    // 
    var loadSectionIndex = function (target, role) {
        // 
        // Send AJAX request:
        // 
        $.ajax({
            type: "POST",
            data: data,
            url: baseURL + 'exam/section/' + role,
            success: function (response) {
                try {
                    target.hide().html(response).fadeIn();
                } catch (Error) {
                    target.html(response);  // IE 11 fix
                }
            }
        });
    };

    // 
    // Load data in all expanded sections:
    // 
    if (expand.length > 0) {
        for (var i = 0; i < expand.length; ++i) {
            var source = '[section-type="' + expand[i] + '"]';
            var parent = $(document).find(source);

            showSectionIndex(parent);
        }
    }

});

