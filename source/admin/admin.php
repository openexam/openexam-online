<?php

// 
// Copyright (C) 2010-2012 Computing Department BMC, 
// Uppsala Biomedical Centre, Uppsala University.
// 
// File:   source/admin/admin.php
// Author: Anders Lövgren
// Date:   2010-03-05
// 
// The main admin page.
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

// 
// The index page:
// 
class SupervisorAdminPage extends AdminPage
{

        private static $params = array(
                "user"   => parent::pattern_user,
                "action" => "/^(grant|revoke)$/"
        );

        public function __construct()
        {
                parent::__construct(_("Supervisor Admin"), self::$params);
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
                        $this->assert("user");
                        
                        //
                        // Grant or revoke admin privileges:
                        //
                        if ($this->param->action == "grant") {
                                $this->grantUserRights();
                        } elseif ($this->param->action == "revoke") {
                                $this->revokeUserRights();
                        }
                } else {
                        $this->listAdminUsers();
                }
        }

        //
        // Grant administrative privileges to user.
        //
        private function grantUserRights()
        {
                Admin::grantUserRights($this->param->user);
                header(sprintf("Location: %s/admin/admin.php", BASE_URL));
        }

        //
        // Revoke administrative privileges from user.
        //
        private function revokeUserRights()
        {
                Admin::revokeUserRights($this->param->user);
                header(sprintf("Location: %s/admin/admin.php", BASE_URL));
        }

        //
        // List all users with administrative privileges.
        //
        private function listAdminUsers()
        {
                global $locale;

                echo "<h3>" . _("Administration") . "</h3>\n";
                echo "<p>" .
                _("This page let you grant and revoke administrative privileges. ") .
                _("These users have been granted administrative privileges:") .
                "</p>\n";

                $ldap = new LdapSearch(LdapConnection::instance());
                $users = Admin::getAdminUsers();

                $table = new Table();
                $row = $table->addRow();
                $row->addHeader(_("Name"));
                $row->addHeader(_("User"));
                $row->addHeader(_("Role"));
                foreach ($users as $user) {
                        $data = $ldap->searchPrincipalName($user->getUserName());
                        $name = "";
                        if ($data->first() != null) {
                                if ($data->first()->hasDisplayName()) {
                                        $name = $data->first()->getDisplayName()->first();
                                }
                        }
                        $row = $table->addRow();
                        $row->addData(iconv("UTF8", $locale->getCharSet(), $name));
                        $row->addData($user->getUserName());
                        $data = $row->addData(_("Revoke"));
                        $data->setlink(sprintf("?user=%s&amp;action=revoke", $user->getUserID()));
                }
                $table->output();

                echo "<h5>" . _("Add new administrator:") . "</h5>\n";
                printf("<p>" . _("Fill in the user name and click on '%s' to give this user administrative privileges:") . "</p>\n", _("Grant"));

                $form = new Form("admin.php", "GET");
                $form->addHidden("action", "grant");
                $input = $form->addTextBox("user");
                $input->setLabel(_("Username"));
                $form->addSubmitButton("submit", _("Grant"));
                $form->output();
        }

}

$page = new SupervisorAdminPage();
$page->render();
?>
