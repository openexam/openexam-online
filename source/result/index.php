<?php

// 
// Copyright (C) 2010-2012, 2014 Computing Department BMC, 
// Uppsala Biomedical Centre, Uppsala University.
// 
// File:   source/result/index.php
// Author: Anders Lövgren
// Date:   2010-12-16
//
// The page from where students can download results from the examinations.
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
// If logon is true, then CAS logon is enforced for this page.
// 
$GLOBALS['logon'] = true;

// 
// Include external libraries:
// 
include "MDB2.php";
include "CAS.php";

// 
// Include configuration:
// 
include "conf/config.inc";
include "conf/database.conf";

// 
// Include logon, user interface and support for error reporting:
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
// Locale and internationalization support:
// 
include "include/locale.inc";

// 
// Include bussiness logic:
//
include "include/exam.inc";
include "include/pdf.inc";
include "include/scoreboard.inc";
include "include/teacher/manager.inc";
include "include/teacher/correct.inc";

// 
// This class implements a standard page.
// 
class ResultPage extends BasePage
{

        private static $params = array(
                "exam"   => parent::pattern_index,
                "action" => "/^(details|download)$/",
                "format" => "/^(pdf|html)$/"
        );
        private $data = null;   // The data for current exam (if any).

        public function __construct()
        {
                parent::__construct(_("Result:"), self::$params);   // Internationalized with GNU gettext
                if (!isset($this->param->format)) {
                        $this->param->format = "pdf";
                }
        }

        //
        // Output the result page body.
        //
        public function printBody()
        {
                //
                // Authorization first:
                //
                $this->checkAccess();

                //
                // Bussiness logic:
                //
                if (!isset($this->param->exam) && !isset($this->param->action)) {
                        $this->showAvailableExams();
                } else {
                        if ($this->param->action == "download") {
                                $this->sendExam();
                        } elseif ($this->param->action == "details") {
                                $this->showExam();
                        }
                }
        }

        //
        // Check that caller is authorized to access this exam or don't have
        // an currently active examination.
        //
        private function checkAccess()
        {
                if (!isset($this->param->exam)) {
                        $exams = Exam::getActiveExams(phpCAS::getUser());
                        if ($exams->count() > 0) {
                                $this->fatal(_("Access denied!"), _("Access to results from your previous completed examinations is not available while another examination is taking place."));
                        }
                } else {
                        $this->data = Exam::getExamData(phpCAS::getUser(), $this->param->exam);
                        if (!$this->data->hasExamID()) {
                                $this->fatal(_("No examination found!"), _("The system could not found any active examiniations assigned to your logon ID. If you think this is an error, please contact the examinator for further assistance."));
                        }
                }
        }

        //
        // Show available exams. It's quite possible that no exams has been approved for the user.
        //
        private function showAvailableExams()
        {
                $exams = Exam::getFinishedExams(phpCAS::getUser());

                if ($exams->count() == 0) {
                        $this->fatal(_("No examination found!"), sprintf("<p>" . _("The system could not found any finished examiniations for your logon ID. If you think this is an error, please contact the examinator for further assistance.") . "</p>"));
                }

                //
                // Classify examinations as finished or decoded.
                // 
                $data = array('f' => array(), 'd' => array());
                foreach ($exams as $exam) {
                        if ($exam->getExamDecoded() == 'Y') {
                                $data['d'][] = $exam;
                        } else {
                                $data['f'][] = $exam;
                        }
                }
                printf("<h3>" . _("Completed examinations:") . "</h3>\n");
                printf("<p>" . _("Your allready completed examinations are either in the state finished or decoded. ") .
                    _("An finished examination is still in the phase of being corrected, while a decoded has already been corrected and got results ready to be downloaded.") .
                    "</p>");

                //
                // Build the tree of decoded and finished examinations:
                // 
                $tree = new TreeBuilder(_("Examinations"));
                $root = $tree->getRoot();

                if (count($data['d']) != 0) {
                        $sect = $root->addChild(_("Decoded"));
                        foreach ($data['d'] as $exam) {
                                $node = $sect->addChild($exam->getExamName());
                                $node->addDates(strtotime($exam->getExamStartTime()), strtotime($exam->getExamEndTime()));
                                $node->addLink(_("Download"), sprintf("?exam=%d&amp;action=download", $exam->getExamID()));
                                $node->addLink(_("Show"), sprintf("?exam=%d&amp;action=download&amp;format=html", $exam->getExamID()));
                                $node->addLink(_("Details"), sprintf("?exam=%d&amp;action=details", $exam->getExamID()));
                        }
                }

                if (count($data['f']) != 0) {
                        $sect = $root->addChild(_("Finished"));
                        foreach ($data['f'] as $exam) {
                                $node = $sect->addChild($exam->getExamName());
                                $node->addDates(strtotime($exam->getExamStartTime()), strtotime($exam->getExamEndTime()));
                                $node->addLink(_("Details"), sprintf("?exam=%d&amp;action=details", $exam->getExamID()));
                        }
                }

                //
                // Output the tree of decoded and finished examiniations:
                //
                $tree->output();
        }

        //
        // Send result to peer as either PDF or HTML.
        //
        private function sendExam()
        {
                //
                // Make sure we don't leak information:
                //
                if ($this->data->getExamDecoded() != 'Y') {
                        $this->fatal(_("No access"), _("This examiniation has not yet been decoded."));
                }

                //
                // Turn off and clear output buffer. Send data to peer in
                // the requested format.
                //
                ob_end_clean();
                $pdf = new ResultPDF($this->param->exam);
                $pdf->setFormat($this->param->format);
                $pdf->send($this->data->getStudentID());
                exit(0);
        }

        //
        // Show details for this examination.
        // 
        private function showExam()
        {
                printf("<h3>" . _("Examination details") . "</h3>\n");
                printf("<p>" . _("Showing description for examiniation <u>%s</u> on <u>%s</u>") . ":</p>\n", $this->data->getExamName(), strftime(DATE_FORMAT, strtotime($this->data->getExamStartTime())));
                printf("<div class=\"examination\">\n");
                printf("<div class=\"examhead\">%s</div>\n", $this->data->getExamName());
                printf("<div class=\"exambody\">%s</div>\n", str_replace("\n", "<br/>", $this->data->getExamDescription()));
                printf("</div>\n");
        }

}

// 
// Validate request parameters and (if validate succeeds) render the page.
// 
$page = new ResultPage();
$page->render();

?>
