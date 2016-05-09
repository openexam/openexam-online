<?php

// 
// The source code is copyrighted, with equal shared rights, between the
// authors (see the file AUTHORS) and the OpenExam project, Uppsala University 
// unless otherwise explicit stated elsewhere.
// 
// File:    ServiceController.php
// Created: 2014-08-25 00:15:47
// 
// Author:  Anders Lövgren (QNET/BMC CompDept)
// 

namespace OpenExam\Controllers;

use OpenExam\Library\WebService\Common\Exception as ServiceException;
use OpenExam\Library\WebService\Common\ServiceRequest;
use OpenExam\Library\WebService\Common\ServiceResponse;

/**
 * Base class for data service controllers.
 * 
 * The ServiceController class is the base for service controllers
 * providing SOAP, REST or AJAX response as opposite to producing
 * HTML output.
 * 
 * The deriving class should implement exceptionAction() to send error
 * response to client in service dependent encoding format.
 * 
 * @author Anders Lövgren (QNET/BMC CompDept)
 */
abstract class ServiceController extends ControllerBase
{

        /**
         * The request payload.
         * @var array 
         */
        private $_payload;

        public function initialize()
        {
                parent::initialize();
                $this->view->disable();
        }

        /**
         * Get service request.
         * @param callable $remapper Callback remapping request parameters (e.g. exams -> exam_id).
         * @return ServiceRequest
         */
        protected abstract function getRequest($remapper = null);

        /**
         * Send service response.
         * @param ServiceResponse $response The service response.
         */
        protected abstract function sendResponse($response);

        /**
         * Get input (model) data and params from request.
         * @return array
         * @throws ServiceException
         */
        protected function getPayload()
        {
                // 
                // Cache payload in this class.
                // 
                if (isset($this->_payload)) {
                        return $this->_payload;
                }

                // 
                // Payload is either on stdin or in POST/PUT-data:
                // 
                if ($this->request->isPost()) {
                        $input = $this->request->getPost();
                }
                if ($this->request->isPut()) {
                        $stdin = file_get_contents("php://input");
                        $input = key($this->request->getPut());
                }
                
                if (isset($input) && $input == false) {
                        $input = file_get_contents("php://input");
                } elseif (is_null($input) && isset($stdin)) {
                        $input = $stdin;
                }

                // 
                // Convert data if needed/requested:
                // 
                if (is_string($input)) {
                        if ($this->request->getBestAccept() == 'application/json') {
                                $input = json_decode($input, true);
                        } elseif (($temp = json_decode($input, true)) != null) {
                                $input = $temp;
                        }
                        if (!isset($input)) {
                                throw new ServiceException("Unhandled content type");
                        }
                }

                // 
                // Currently, we are only handling array data;
                // 
                if (!is_array($input)) {
                        $input = (array) $input;
                }

                // 
                // Separate on model data and query params:
                // 
                foreach (array('data', 'params') as $part) {
                        if (isset($input[$part])) {
                                $$part = (array) $input[$part];
                                unset($input[$part]);
                        }
                }

                // 
                // Assume non-empty input is data by default:
                // 
                if (!isset($data) && !isset($params) && key($input) != "0") {
                        $data = $input;
                }
                if (!isset($data) && count($input) != 0) {
                        $data = $input;
                }
                if (!isset($data)) {
                        $data = array();
                }
                if (!isset($params)) {
                        $params = array();
                }

                // 
                // Cleanup if input data was missing.
                // 
                if (key($data) == "0") {
                        if ($data[0] == null) {
                                $data = array();
                        }
                        if (isset($data[0]) && is_string($data[0]) && strpbrk($data[0], '{[') != false) {
                                $data = array();
                        }
                }

                $this->_payload = array($data, $params);
                return $this->_payload;
        }

}