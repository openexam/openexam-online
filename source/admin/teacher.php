<?php

// 
// Copyright (C) 2010-2012 Computing Department BMC, 
// Uppsala Biomedical Centre, Uppsala University.
// 
// File:   source/admin/teacher.php
// Author: Anders Lövgren
// Date:   2010-04-26
// 
// The teacher admin page. This page lets authorized user (supervisors) grant 
// or revoke the teacher role for other users.
//
// 
// Force logon for unauthenticated users:
// 
$GLOBALS['logon'] = true;

// 
// System check:
// 
if (!file_exists("../../conf/database.conf")) {
        header("location: setup.php?reason=database");
}
if (!file_exists("../../conf/config.inc")) {
        header("location: setup.php?reason=config");
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
include "include/ldap.inc";

// 
// Business logic:
// 
include "include/admin.inc";
include "include/teacher.inc";

// 
// The index page:
// 
class TeacherAdminPage extends AdminPage
{

        private static $params = array(
                "action" => "/^(grant|revoke)$/",
                "user"   => parent::pattern_user
        );

        public function __construct()
        {
                parent::__construct(_("Teacher Admin"), self::$params);
        }

        //
        // The main entry point. This is where all processing begins.
        //
        public function printBody()
        {
                if (isset($this->param->action)) {
                        //
                        // Check required request parameters:
                        //
                        if (!isset($this->param->user)) {
                                $this->fatal(_("Request parameter error!"), _("Missing request parameter 'user'."));
                        }
                        //
                        // Grant or revoke admin privileges:
                        //
                        if ($this->param->action == "grant") {
                                $this->grantUserRights();
                        } elseif ($this->param->action == "revoke") {
                                $this->revokeUserRights();
                        }
                } else {
                        self::listTeacherUsers();
                }
        }

        //
        // Grant administrative privileges to user.
        //
        private function grantUserRights()
        {
                Teacher::grantUserRights($this->param->user);
                header(sprintf("Location: %s/admin/teacher.php", BASE_URL));
        }

        //
        // Revoke administrative privileges from user.
        //
        private function revokeUserRights()
        {
                Teacher::revokeUserRights($this->param->user);
                header(sprintf("Location: %s/admin/teacher.php", BASE_URL));
        }

        //
        // List all users with the teacher role.
        //
        private static function listTeacherUsers()
        {
                global $locale;

                echo "<h3>" . _("Administration") . "</h3>\n";
                echo "<p>" .
                _("This page let you grant and revoke the teacher role for other users. ") .
                _("These users have been granted the teacher role:") .
                "</p>\n";

                $addr = array();
                $ldap = new LdapSearch(LdapConnection::instance());
                $users = Teacher::getTeachers();

                $table = new Table();
                $row = $table->addRow();
                $row->addHeader(_("Name"));
                $row->addHeader(_("User"));
                $row->addHeader(_("Role"));
                foreach ($users as $user) {
                        $data = $ldap->searchPrincipalName($user->getUserName());
                        $name = "";
                        $mail = null;
                        if ($data->first() != null) {
                                if ($data->first()->hasDisplayName()) {
                                        $name = $data->first()->getDisplayName()->first();
                                }
                                if ($data->first()->hasMail()) {
                                        $mail = $data->first()->getMail()->first();
                                        $addr[] = $mail;
                                }
                        }
                        $row = $table->addRow();
                        $data = $row->addData($name);
                        if (isset($mail)) {
                                $data->setLink(sprintf("mailto:%s", $mail));
                        }
                        $row->addData($user->getUserName());
                        $data = $row->addData(_("Revoke"));
                        $data->setLink(sprintf("?user=%s&amp;action=revoke", $user->getUserID()));
                }
                $table->output();

                echo "<h5>" . _("Add new teacher:") . "</h5>\n";
                printf("<p>" . _("Fill in the user name and click on '%s' to grant this user the teacher role:") . "</p>\n", _("Grant"));

                $form = new Form("teacher.php", "GET");
                $form->addHidden("action", "grant");
                $input = $form->addTextBox("user");
                $input->setLabel(_("Username"));
                $form->addSubmitButton("submit", _("Grant"));
                $form->output();

                echo "<h5>" . _("Contact:") . "</h5>\n";
                printf("<p>" .
                    _("Send an email to <a href=\"%s\">all teachers</a> that have an email address in the LDAP directory.") .
                    "</p>\n", sprintf("mailto:%s", implode(";", $addr)));
        }

}

$page = new TeacherAdminPage();
$page->render();
?>
