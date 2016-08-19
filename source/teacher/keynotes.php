<?php

// 
// Copyright (C) 2009-2012 Computing Department BMC, 
// Uppsala Biomedical Centre, Uppsala University.
// 
// File:   source/teacher/keynotes.php
// Author: Anders Lövgren
// Date:   2012-03-06
// 
// Show some importand notes.
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
// Include configuration files:
// 
include "conf/config.inc";
include "conf/database.conf";

// 
// Include required files:
// 
include "include/cas.inc";
include "include/ui.inc";
include "include/error.inc";

// 
// Include database support:
// 
include "include/database.inc";

// 
// Required for top menu support:
// 
include "include/teacher.inc";

class NotesPage extends BasePage
{

        public function __construct()
        {
                parent::__construct(_("Keynotes"));
        }

        public function printBody()
        {
                printf("<h3>" . _("Keynotes") . "</h3>\n");

                printf("<h5>" . _("Roles") . "</h5>\n");
                printf("<p>");
                printf(_("This system allows the creator of the examination to delegate (grant) permissions to other people. Its called giving roles to other people."));
                printf("</p>\n");
                printf("<p>");
                printf(_("These are the roles that can be delegated:"));
                printf("<dl>\n");
                printf("<dt>Contributor:</dt>\n");
                printf("<dd>%s</dd>\n", _("A contributor can add questions to the examination. A person that contributes a question is also (implicit) defined as the person that should correct the answers to that question."));
                printf("<dt>Corrector:</dt>\n");
                printf("<dd>%s</dd>\n", _("A corrector is a person that has been assigned the task of correcting one or more question answers on an exam. Currently, only the examination creator can publish questions with another person as its corrector."));
                printf("<dt>Examinator:</dt>\n");
                printf("<dd>%s</dd>\n", _("An examinator can assign (register) student for an examination and re-schedule the start and stop time."));
                printf("<dt>Decoder:</dt>\n");
                printf("<dd>%s</dd>\n", _("An decoder is the person that is granted permissions to decode the examination. By decoding, the real identity behind the anonymous identity is disclosed. A person assigned as the decoder of the examination is usually the same that reports the result to UPPDOK."));
                printf("</dl>\n");

                printf("<h5>" . _("Lifecycle") . "</h5>\n");
                printf("<p>");
                printf(_("This is an example of how the system is supposed to be used:"));
                printf("</p>\n");
                printf("<ol>\n");
                printf("<li>" . _("A person granted the teacher permission creates the examination. He/she grants other people the contributor, examinator and decoder roles. The teacher defines when and for how long the examination will last.") . "</li><br />\n");
                printf("<li>" . _("The people assigned as contributors adds questions to the examination. By default, the teacher is also assigned the contributor role.") . "</li><br />\n");
                printf("<li>" . _("The people assigned the examinator role adds those students that should take the exam. If students shows up at the examination day, they can be added by the examinator.") . "</li><br />\n");
                printf("<li>" . _("After the examination day, those that have contributed questions logs on and corrects the answers. The contributor can only correct answers to those questions they have created.") . "</li><br />\n");
                printf("<li>" . _("Once all answers have been corrected, the decoder can logon and disclose the anonymous identities behind the answers. Once the examination has been decoded, its no longer possible to modify the scores to the answers.") . "</li><br />\n");
                printf("</ol>\n");

                printf("<h5>" . _("Restrictions") . "</h5>\n");
                printf("<p>" . _("Only the person that publish an question for an examination can correct the answers to that question.") . "</p>\n");
        }

}

$page = new NotesPage();
$page->render();
?>
