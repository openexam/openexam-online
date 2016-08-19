<?php

// 
// Copyright (C) 2010-2012 Computing Department BMC, 
// Uppsala Biomedical Centre, Uppsala University.
// 
// File:   source/teacher/template.php
// Author: Anders Lövgren
// Date:   2010-04-26
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

// 
// Include database support:
// 
include "include/database.inc";

// 
// Business logic:
// 
include "include/teacher.inc";

// 
// The template page:
// 
class TemplatePage extends TeacherPage
{

        private static $params = array("exam" => parent::pattern_index);

        public function __construct()
        {
                parent::__construct(_("Template Page"), self::$params);
        }

        //
        // The main entry point. This is where all processing begins.
        //
        public function printBody()
        {
                //
                // Authorization first:
                //
                if (isset($this->param->exam)) {
                        $this->checkAccess($this->param->exam);
                }

                //
                // Bussiness logic:
                //
                if (isset($this->param->exam)) {
                        die("TODO: implement bussiness logic");
                }
        }

        //
        // Verify that the caller has been granted the required role on this exam.
        // This example code checks if caller is assigned the contributor role. New
        // roles can be added in include/teacher/manager.inc
        //
        private function checkAccess()
        {
                $role = "contributor";

                if (!$this->manager->hasRole(phpCAS::getUser(), $role)) {
                        $this->fatal(_("Access denied!"), sprintf(_("Only users granted the %s role on this exam can access this page. The script processing has halted."), $role));
                }
        }

}

$page = new TemplatePage();
$page->render();
?>
