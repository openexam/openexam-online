<?php

// 
// Copyright (C) 2010-2014 Computing Department BMC, 
// Uppsala Biomedical Centre, Uppsala University.
// 
// File:   source/teacher/decoder.php
// Author: Anders Lövgren
// Date:   2010-05-05
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
include('Mail.php');
include('Mail/mime.php');

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
include "include/exam.inc";
include "include/teacher.inc";
include "include/teacher/manager.inc";
include "include/teacher/decoder.inc";
include "include/teacher/correct.inc";

// 
// Support classes:
// 
include "include/pdf.inc";
include "include/smtp.inc";
include "include/sendmail.inc";
include "include/scoreboard.inc";

// 
// Settings for result mail attachments.
// 
if (!defined('ATTACH_MAX_FILE_SIZE')) {
        define('ATTACH_MAX_FILE_SIZE', 1024 * 1024);
}
if (!defined('ATTACH_MAX_NUM_FILES')) {
        define('ATTACH_MAX_NUM_FILES', 3);
}

// 
// The decoder page:
// 
class DecoderPage extends TeacherPage
{

        private static $params = array(
                "exam"     => parent::pattern_index,
                "mode"     => "/^(result|scores)$/",
                "mirror"   => parent::pattern_textline, // button
                "action"   => "/^(save|show|mail|download)$/",
                "format"   => "/^(pdf|html|ps|csv|tab|xml)$/",
                "student"  => "/^(\d+|all|list|form|tree)$/",
                "message"  => parent::pattern_textarea,
                "colorize" => parent::pattern_index,
                "verbose"  => parent::pattern_index,
                "order"    => "/^(state|name|date)$/",
                "sort"     => "/^(tag|name|user|code|persnr|pnr|summary|percent|grade)$/",
                "desc"     => "/^[0-1]$/"
        );
        private $decoder;
        private $filter;

        public function __construct()
        {
                parent::__construct(_("Decoder Page"), self::$params);

                if (!isset($this->param->order)) {
                        $this->param->order = "state";
                }
                if (!isset($this->param->verbose)) {
                        $this->param->verbose = ScoreBoardPrinter::VERBOSE_REPORT;
                }
                if (!isset($this->param->colorize)) {
                        $this->param->colorize = false;
                }
                if (!isset($this->param->sort)) {
                        $this->param->sort = "name";
                }
                if (!isset($this->param->desc)) {
                        $this->param->desc = true;
                }
                if (!isset($this->param->student)) {
                        $this->param->student = "tree";
                }

                if (isset($this->param->exam)) {
                        $this->filter = new ScoreBoardFilter($this->param->exam, true, DECODED_SHOW_UNATTENDED);
                        $this->decoder = new Decoder($this->param->exam);
                }
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
                        $this->checkAccess();
                        $this->setDecoded();
                }

                //
                // Bussiness logic:
                //
                if (isset($this->param->exam)) {
                        if (!isset($this->param->action)) {
                                $this->param->action = "download";
                        }
                        if ($this->param->action == "download") {
                                $this->showDownload();
                        } elseif ($this->param->action == "show") {
                                $this->showScores();
                        } elseif ($this->param->action == "save") {
                                $this->assert("mode");
                                if ($this->param->mode == "result") {
                                        $this->assert(array(
                                                "format",
                                                "student"));
                                        $this->saveResult();
                                } elseif ($this->param->mode == "scores") {
                                        $this->assert("format");
                                        $this->saveScores();
                                }
                        } elseif ($this->param->action == "mail") {
                                if ($this->param->student == "list" ||
                                    $this->param->student == "form" ||
                                    $this->param->student == "tree") {
                                        $this->mailResult($this->param->student);
                                } else {
                                        $this->assert("format");
                                        $this->sendResult();
                                }
                        }
                } else {
                        self::showAvailableExams();
                }
        }

        public function printMenu()
        {
                if (isset($this->param->exam)) {
                        printf("<span id=\"menuhead\">%s:</span>\n", _("Decoder"));
                        printf("<ul>\n");
                        printf("<span id=\"menuhead\">%s:</span>\n", _("Result"));
                        printf("<ul>\n");
                        printf("<li><a href=\"?exam=%d&amp;action=download\">%s</a></li>\n", $this->param->exam, _("Download"));
                        printf("<li><a href=\"?exam=%d&amp;action=mail\">%s</a></li>\n", $this->param->exam, _("Send by email"));
                        printf("</ul>\n");
                        printf("<br/>\n");
                        printf("<span id=\"menuhead\">%s:</span>\n", _("Score board"));
                        printf("<ul>\n");
                        printf("<li><a href=\"?exam=%d&amp;action=download\">%s</a></li>\n", $this->param->exam, _("Download"));
                        printf("<li><a href=\"?exam=%d&amp;action=show\">%s</a></li>\n", $this->param->exam, _("Show"));
                        printf("</ul>\n");
                        printf("<br/>\n");
                        printf("</ul>\n");
                        parent::printMenu();   // print parent menu
                }
        }

        //
        // Verify that the caller has been granted the required role on this exam.
        //
        private function checkAccess()
        {
                if (!$this->manager->isDecoder(phpCAS::getUser())) {
                        $this->fatal(_("Access denied!"), sprintf(_("Only users granted the %s role on this exam can access this page. The script processing has halted."), "decoder"));
                }

                if (!$this->manager->getInfo()->isDecodable()) {
                        $this->fatal(_("Can't continue!"), _("This examination is not yet decodable, probably becuase not all answers have been corrected yet. The script processing has halted."));
                }
        }

        //
        // This function flags the exam as decoded. It should be called whenever
        // script execution calls an function that reveals the real identities.
        //
        private function setDecoded()
        {
                $data = $this->manager->getData();

                $decoded = $data->getExamDecoded() == 'N' ? true : false;
                $this->decoder->setDecoded();

                printf("<h3>" . _("Decoded Examination") . "</h3>\n");
                if ($decoded) {
                        printf("<p>" . _("The examination has been successful decoded. It's no longer possible change the correction of answers in this examination.") . "</p>\n");
                }
        }

        //
        // Save result from exam. The result is the complete examination for an
        // student. If student equals all, then all student result is downloaded
        // as an zip archive.
        //
        private function saveResult()
        {
                ob_end_clean();

                switch ($this->param->format) {
                        case "pdf":
                        case "html":
                        case "ps":
                                if ($this->param->student == "all") {    // Send zip-file containing all results.
                                        $result = new ResultPDF($this->param->exam);
                                        $result->setFormat($this->param->format);
                                        $result->sendAll();
                                } else {
                                        $result = new ResultPDF($this->param->exam);
                                        $result->setFormat($this->param->format);
                                        $result->send($this->param->student);
                                }
                                break;
                        default:
                                die(sprintf("Format %s is not supported in result mode.", $this->param->format));
                }
                exit(0);     // Prevent output of standard footer.
        }

        private function saveScores()
        {
                if (!$this->manager->getInfo()->isDecoded()) {
                        $this->fatal(_("Can't continue!"), _("This examination has not been decoded. The script processing has halted."));
                }

                ob_end_clean();

                $stream = fopen("php://memory", "r+");
                if ($stream) {
                        switch ($this->param->format) {
                                case "pdf":
                                case "ps":
                                        die("TODO: implement saving score board as PDF and PostScript");
                                        break;
                                case "html":
                                        $format = new OutputTextHtml();
                                        break;
                                case "tab":
                                        $format = new OutputTextTab();
                                        break;
                                case "csv":
                                        $format = new OutputTextCsv();
                                        break;
                                case "xml":
                                        $format = new OutputTextXml();
                                        break;
                                default:
                                        die(sprintf("Format %s is not supported in score board mode.", $this->param->format));
                        }

                        if (isset($format)) {
                                $writer = new StreamWriter($stream, $format);
                                $sender = new ScoreBoardWriter($this->param->exam, $writer, $format);
                                $sender->send();
                                fclose($stream);
                                exit(1);
                        }
                }
        }

        //
        // Show the page where caller can chose to download the result and score
        // board in different formats.
        //
        private function showDownload()
        {
                global $locale;

                //
                // The form for downloading the results:
                //
                printf("<h5>" . _("Download Result") . "</h5>\n");
                printf("<p>" .
                    _("This section lets you download the results for all or individual students in different formats. ") .
                    _("The result contains the complete examination with answers and scores.") .
                    "</p>\n");
                printf("<p>" .
                    _("Notice that the language used in the generated file will be the same as your currently selected language (%s).") .
                    "</p>\n", _($locale));

                $options = array(
                        "pdf"  => "Adobe PDF",
                        "ps"   => "PostScript",
                        "html" => "HTML");

                $form = new Form("decoder.php", "GET");
                $form->addHidden("exam", $this->param->exam);
                $form->addHidden("mode", "result");
                $form->addHidden("action", "save");

                $combo = $form->addComboBox("format");
                $combo->setLabel(_("Format"));
                foreach ($options as $name => $label) {
                        $combo->addOption($name, $label);
                }

                $combo = $form->addComboBox("student");
                $combo->setLabel(_("Select"));
                $board = new ScoreBoard($this->param->exam, $this->filter);
                $students = $board->getStudents();
                $option = $combo->addOption("all", _("All Students"));
                $option = $combo->addOption(0, "---");
                $option->setDisabled();
                foreach ($students as $student) {
                        $student->setStudentName($this->getDisplayName($student->getStudentUser()));
                        $combo->addOption($student->getStudentID(), sprintf("%s (%s) [%s]", $student->getStudentName(), $student->getStudentUser(), $student->getStudentCode()));
                }
                $button = $form->addSubmitButton("submit", _("Download"));
                $button->setLabel();
                $button->setTitle(_("Please note that it might take some time to complete your request, especial if the examination has a lot of students."));
                $form->output();

                //
                // The form for downloading the score board:
                //
                printf("<h5>" . _("Download Score Board") . "</h5>\n");
                printf("<p>" .
                    _("This section lets you download the score board showing a summary view of the examination in different formats. ") .
                    "</p>\n");

                $options = array(
                        "csv"  => _("Comma Separated Text"),
                        "tab"  => _("Tab Separated Text"),
                        "xml"  => _("XML Format Data"),
                        "html" => _("Single HTML Page"));

                $form = new Form("decoder.php", "GET");
                $form->addHidden("exam", $this->param->exam);
                $form->addHidden("mode", "scores");
                $form->addHidden("action", "save");
                $combo = $form->addComboBox("format");
                $combo->setLabel(_("Format"));
                foreach ($options as $name => $label) {
                        $combo->addOption($name, $label);
                }
                $button = $form->addSubmitButton("submit", _("Download"));
                $button->setLabel();
                $form->output();
        }

        //
        // Shows the score board with the anonymous identity disclosed.
        //
        private function showScores()
        {
                $data = $this->manager->getData();

                printf("<h5>" . _("Answer Results") . "</h5>\n");
                printf("<p>" .
                    _("This table shows all answers from students to questions for the examination '%s'. ") .
                    "</p>\n", $data->getExamName());

                $verbosity = array(
                        _("Summary"),
                        _("Report"),
                        _("All")
                );

                $colorize = array(
                        _("Standard"),
                        _("Colorize")
                );

                $vcurr = $this->param->verbose;
                $vnext = ($this->param->verbose + 1) % count($verbosity);

                $ccurr = $this->param->colorize;
                $cnext = ($this->param->colorize + 1) % count($colorize);

                printf("<span class=\"links viewmode\">");
                printf("%s: <a href=\"?exam=%d&amp;action=show&amp;colorize=%d&amp;verbose=%d\">%s</a>, ", _("Details"), $this->param->exam, $ccurr, $vnext, $verbosity[$vnext]);
                printf("%s: <a href=\"?exam=%d&amp;action=show&amp;colorize=%d&amp;verbose=%d\">%s</a>", _("Mode"), $this->param->exam, $cnext, $vcurr, $colorize[$cnext]);
                printf("</span>\n");

                $board = new ScoreBoardPrinter($this->param->exam, $this->filter);
                $board->setVerbose($this->param->verbose);
                $board->setColorized($this->param->colorize);
                $board->setOrder($this->param->sort, $this->param->desc != 0);
                $board->output();

                printf("<h5>" . _("Color Codes") . "</h5>\n");
                printf("<p>" . _("These are the color codes used in the score board:") . "</p>\n");
                if ($this->param->colorize) {
                        $codes = array(
                                "s0"   => sprintf(_("Less than %d%% of max score."), 20),
                                "s20"  => sprintf(_("Between %d and %d %% of max score."), 20, 40),
                                "s40"  => sprintf(_("Between %d and %d %% of max score."), 40, 60),
                                "s60"  => sprintf(_("Between %d and %d %% of max score."), 60, 80),
                                "s80"  => sprintf(_("Between %d and %d %% of max score."), 80, 99),
                                "s100" => sprintf(_("%d%% correct answer (full score)."), 100));
                } else {
                        $codes = array(
                                "ac" => _("Answer has been corrected."),
                                "no" => _("This answer should be corrected by another person."),
                                "na" => _("No answer was given for this question."),
                                "nc" => _("The answer has not yet been corrected."),
                                "qr" => _("Question is flagged as removed (no scores for this question is counted).")
                        );
                }
                $table = new Table();
                foreach ($codes as $code => $desc) {
                        $row = $table->addRow();
                        $row->addData()->setClass(sprintf("cc %s", $code));
                        $row->addData($desc);
                }
                $table->output();
        }

        //
        // Send the email message. The message is either sent to myself (debug), all students
        // or individual students.
        //
        private function sendResult()
        {
                printf("<h5>" . _("Sending Result") . "</h5>\n");

                $from = $this->getMailRecepient(phpCAS::getUser());
                $data = $this->manager->getData();
                $mail = new MailResult($data->getExamName(), $data->getExamStartTime(), $from, $from);

                //
                // Append any uploaded files.
                //
                for ($i = 0; $i < ATTACH_MAX_NUM_FILES; $i++) {
                        if ($_FILES['attach']['error'][$i] == 0 && $_FILES['attach']['size'][$i] > 0) {    // successful uploaded
                                if (is_uploaded_file($_FILES['attach']['tmp_name'][$i])) {
                                        $mail->addAttachment($_FILES['attach']['name'][$i], $_FILES['attach']['type'][$i], $_FILES['attach']['tmp_name'][$i]);
                                }
                        }
                }

                //
                // Append optional message.
                //
                if (isset($this->param->message)) {
                        $lines = split("\n", $this->param->message);
                        if (count($lines) > 0) {
                                $sect = array(
                                );
                                foreach ($lines as $line) {
                                        $line = trim($line);
                                        if (strlen($line) == 0) {
                                                $sect[] = "\n\n";
                                        } elseif ($line[0] == "-") {
                                                $curr = array_pop($sect);
                                                if (count($sect)) {
                                                        $head = array_shift($sect);
                                                        $text = "  " . implode(" ", $sect);
                                                        $mail->addMessage($head, $text);
                                                }
                                                $sect = array(
                                                );
                                                $sect[] = $curr;
                                        } else {
                                                $sect[] = $line;
                                        }
                                }
                                $head = array_shift($sect);
                                $text = "  " . implode(" ", $sect);
                                $mail->addMessage($head, $text);
                        }
                }

                $result = new ResultPDF($this->param->exam);
                $result->setFormat($this->param->format);

                if ($this->param->student == "all") {
                        $students = $this->manager->getStudents();
                } else {
                        $students = array(
                                $this->manager->getStudentData($this->param->student));
                }

                foreach ($students as $student) {
                        $addr = $this->getMailRecepient($student->getStudentUser());
                        if (!isset($addr)) {
                                $this->error(sprintf(_("Failed lookup email address for %s"), $student->getStudentUser()));
                                continue;
                        }
                        $file = tempnam("/tmp", "openexam-result");
                        $result->save($student->getStudentID(), $file);

                        if (strstr($this->param->format, "pdf")) {
                                $attach = new MailAttachment("result.pdf", "application/pdf", $file);
                        } elseif (strstr($this->param->format, "ps")) {
                                $attach = new MailAttachment("result.ps", "application/postscript", $file);
                        } elseif (strstr($this->param->format, "html")) {
                                $attach = new MailAttachment("result.html", "text/html", $file);
                        }

                        if (isset($this->param->mirror)) {
                                $mail->setFrom($addr);
                                $mail->send($from, $attach);
                                $this->success(sprintf(_("Successful sent message to <a href=\"mailto:%s\">%s</a>"), $from->getEmail(), $from->getName()));
                        } else {
                                $mail->setBcc($from);
                                $mail->send($addr, $attach);
                                $this->success(sprintf(_("Successful sent message to <a href=\"mailto:%s\">%s</a>"), $addr->getEmail(), $addr->getName()));
                        }
                }
        }

        private function mailResult($show)
        {
                $mode = array(
                        "form" => _("Form"),
                        "tree" => _("Tree")
                );
                $disp = array(
                );
                printf("<span class=\"links viewmode\">\n");
                foreach ($mode as $name => $text) {
                        if ($show != $name) {
                                $disp[] = sprintf("<a href=\"?exam=%d&amp;action=mail&amp;student=%s\">%s</a>", $this->param->exam, $name, $text);
                        } else {
                                $disp[] = $text;
                        }
                }
                printf("%s: %s\n", _("Show"), implode(", ", $disp));
                printf("</span>\n");

                switch ($show) {
                        case "form":
                                $this->mailResultForm();
                                break;
                        case "tree":
                                $this->mailResultTree();
                                break;
                }
        }

        // 
        // List email addresses for all student in a tree structure.
        // 
        private function mailResultTree()
        {
                printf("<p>" .
                    _("Compose and send email to all or individual students by clicking on one of the links below. ") .
                    _("Your own email program should be opened with the recepients filled in. ") .
                    _("The email body will contain the link from where the students can login and download their result. ")
                );

                $board = new ScoreBoard($this->param->exam, $this->filter);
                $students = $board->getStudents();

                $recepients = array();

                foreach ($students as $student) {
                        $addr = $this->getMailRecepient($student->getStudentUser());
                        if (!isset($addr)) {
                                $this->error(sprintf(_("Failed lookup email address for %s"), $student->getStudentUser()));
                                continue;
                        } else {
                                $recepients[] = $addr->getAddress();
                        }
                }

                $data = $this->manager->getData();
                $date = strftime("%x", strtotime($data->getExamStartTime()));
                $body = sprintf(_("Result from the examination '%s' on %s can now be downloaded from %s"), $data->getExamName(), $date, sprintf("%s/result/", BASE_URL));
                $subj = sprintf(_("Result available for examination on %s (OpenExam)"), $date);

                $tree = new TreeBuilder(_("Students"));
                $link = sprintf("mailto:?bcc=%s&subject=%s&body=%s", implode(",", $recepients), $subj, $body);
                $root = $tree->getRoot();
                $root->setLink($link);
                $root->addLink(_("Email"), $link, _("Send email to all students in this list."));

                // 
                // GET request limits on Windows force us to group students.
                // 
                for ($i = 0; $i < count($recepients); $i += 30) {
                        $slice = array_slice($recepients, $i, 30);
                        $link = sprintf("mailto:?bcc=%s&subject=%s&body=%s", implode(",", $slice), $subj, $body);
                        $group = $root->addChild(_("Group"));
                        $group->setLink($link);
                        $group->addLink(_("Email"), $link, _("Send email to all students in this group."));
                        foreach ($slice as $addr) {
                                $link = sprintf("mailto:?to=%s&subject=%s&body=%s", $addr, $subj, $body);
                                $child = $group->addChild($addr);
                                $child->setLink($link);
                                $child->addLink(_("Email"), $link, sprintf(_("Send email to %s."), $addr));
                        }
                }

                $tree->output();
        }

        //
        // Show form for sending examination result to students.
        //
        private function mailResultForm()
        {
                global $locale;

                //
                // The form for sending the results by email:
                //
                printf("<p>" .
                    _("This section lets you send the results to all or individual students in different formats. ") .
                    _("The result contains the complete examination with answers and scores.") .
                    "</p>\n");
                printf("<p>" .
                    _("Notice that the language used in the outgoing message will be the same as your currently selected language (%s).") .
                    "</p>\n", _($locale));

                //
                // The format and student select section:
                //
                $options = array(
                        "pdf"  => "Adobe PDF",
                        "ps"   => "PostScript",
                        "html" => "HTML");
                $form = new Form("decoder.php", "POST");
                $form->setEncodingType("multipart/form-data");
                $form->addHidden("MAX_FILE_SIZE", ATTACH_MAX_FILE_SIZE);
                $form->addHidden("exam", $this->param->exam);
                $form->addHidden("mode", "result");
                $form->addHidden("action", "mail");

                $form->addSectionHeader(_("Send Result"));
                $combo = $form->addComboBox("format");
                $combo->setLabel(_("Format"));
                foreach ($options as $name => $label) {
                        $combo->addOption($name, $label);
                }
                $combo = $form->addComboBox("student");
                $combo->setLabel(_("Select"));
                $board = new ScoreBoard($this->param->exam, $this->filter);
                $students = $board->getStudents();
                $option = $combo->addOption("all", _("All Students"));
                $option = $combo->addOption(0, "---");
                $option->setDisabled();
                foreach ($students as $student) {
                        $student->setStudentName($this->getDisplayName($student->getStudentUser()));
                        $combo->addOption($student->getStudentID(), sprintf("%s (%s) [%s]", $student->getStudentName(), $student->getStudentUser(), $student->getStudentCode()));
                }

                //
                // The optional message section:
                //
                $form->addSectionHeader(_("Optional Message"));
                $input = $form->addTextArea("message", _("Header 1:\n---\nSome text for this first section...\n\nHeader 2:\n---\nIt's possible to have multiple blocks of text separated by newlines:\n\nFirst block...\n\n...and second block.\n"));
                $input->setLabel(_("Text"));
                $input->setClass("message");
                $input->setTitle(_("Append one or more optional section of text to the message."));
                $input->setEvent(EVENT_ON_DOUBLE_CLICK, EVENT_HANDLER_CLEAR_CONTENT);

                //
                // The attachment section:
                //
                $form->addSectionHeader(_("Attachements"));
                for ($i = 0; $i < ATTACH_MAX_NUM_FILES; $i++) {
                        $input = $form->addFileInput("attach[]");
                        $input->setClass("file");
                        $input->setTitle(_("Attach this file to all outgoing messages."));
                        $input->setLabel();
                }
                $form->addSpace();
                $input = $form->addCheckBox("mirror", _("Enable mirror mode (dry-run)."));
                $input->setTitle(_("If checked, then your email address will be used as the receiver, with the student address set as the sender."));
                $input->setLabel();
                $form->addSpace();
                $button = $form->addSubmitButton("submit", _("Send"));
                $button->setLabel();
                $button->setTitle(_("Please note that it might take some time to complete your request, especial if the examination has a lot of students."));

                $form->output();
        }

        //
        // Show all exams where caller has been granted the decoder role.
        //
        private function showAvailableExams()
        {
                $utils = new TeacherUtils($this, phpCAS::getUser());
                $utils->listDecodable($this->param->order);
        }

}

$page = new DecoderPage();
$page->render();

?>
