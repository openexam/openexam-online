<?php

// 
// Copyright (C) 2010 Computing Department BMC, 
// Uppsala Biomedical Centre, Uppsala University.
// 
// File:   source/teacher/index.php
// Author: Anders Lövgren
// Date:   2010-04-26
// 
// The teacher index page. This page is unauthorized (but authenticated), so that 
// basic information about the system can be displayed without enforcing the user
// to have at least one of the roles on one or more exams. All other pages should
// have full authorization.
//
// 
// Force logon for unauthenticated users:
// 
$GLOBALS['logon'] = true;

// 
// System check:
// 
if (!file_exists("../../conf/database.conf")) {
        header("location: ../admin/setup.php?reason=database");
}
if (!file_exists("../../conf/config.inc")) {
        header("location: ../admin/setup.php?reason=config");
}

// 
// Append root directory to include path:
// 
set_include_path(get_include_path() . PATH_SEPARATOR . realpath('../..'));

// 
// Include external libraries:
// 
include "MDB2.php";
include "CAS.php";

// 
// Locale and internationalization support:
// 
include "include/locale.inc";

// 
// Include configuration:
// 
include "conf/config.inc";
include "conf/database.conf";

// 
// Include logon and user interface support:
// 
include "include/cas.inc";
include "include/ui.inc";
include "include/error.inc";
include "include/html.inc";

// 
// Include database support:
// 
include "include/database.inc";
include "include/teacher.inc";

// 
// The index page:
// 
class TeacherIndexPage extends BasePage
{

        public function __construct()
        {
                parent::__construct(_("Index"));
        }

        //
        // The main entry point. This is where all processing begins.
        //
        public function printBody()
        {
                $content = new Content();
                $content->addHeader(_("Information for teachers"));
                $content->addParagraph(
                    array(
                            _("The teacher section is for teachers or other people that has been granted the teacher, contributor, examinator or decoder role."),
                            _("It allows people manage examinations and result from online exams.")
                ));
                $content->addParagraph(
                    array(
                            sprintf(_("If you wish to create your own online exam, contact %s to gain access to the system."), CONTACT_STRING),
                            sprintf(_("General and getting started information can be found on the <a href=\"%s\">Help</a> page."), "help.php")
                ));
                $content->output();
        }

}

$page = new TeacherIndexPage();
$page->render();
?>
