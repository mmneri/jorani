<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * This file is part of Jorani.
 *
 * Jorani is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jorani is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jorani.  If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('BASEPATH')) exit('No direct script access allowed');

class Contracts extends CI_Controller {
    
    /**
     * Default constructor
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function __construct() {
        parent::__construct();
        setUserContext($this);
        $this->lang->load('contract', $this->language);
        $this->load->model('contracts_model');
    }
    
    /**
     * Prepare an array containing information about the current user
     * @return array data to be passed to the view
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    private function getUserContext()
    {
        $data['fullname'] = $this->fullname;
        $data['is_admin'] = $this->is_admin;
        $data['is_hr'] = $this->is_hr;
        $data['user_id'] =  $this->user_id;
        $data['language'] = $this->language;
        $data['language_code'] =  $this->language_code;
        return $data;
    }

    /**
     * Display the list of all contracts defined in the system
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function index($filter = 'requested') {
        $this->auth->check_is_granted('list_contracts');
        if ($filter == 'all') {
            $showAll = true;
        } else {
            $showAll = false;
        }
        $data = getUserContext($this);
        $data['filter'] = $filter;
        $data['title'] = lang('contract_index_title');
        $data['help'] = $this->help->create_help_link('global_link_doc_page_contracts_list');
        $data['contracts'] = $this->contracts_model->get_contracts();
        $this->load->view('templates/header', $data);
        $this->load->view('menu/index', $data);
        $this->load->view('contracts/index', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Display details of a given contract
     * @param int $id contract identifier
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function view($id) {
        $this->auth->check_is_granted('view_contract');
        $data = getUserContext($this);
        $data['contract'] = $this->contracts_model->get_contracts($id);
        if (empty($data['contract'])) {
            show_404();
        }        
        $data['title'] = lang('contract_view_title');
        $this->load->view('templates/header', $data);
        $this->load->view('menu/index', $data);
        $this->load->view('contracts/view', $data);
        $this->load->view('templates/footer');
    }
    
    /**
     * Display a form that allows updating a given contract
     * @param int $id Contract identifier
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function edit($id) {
        $this->auth->check_is_granted('edit_contract');
        $data = getUserContext($this);
        $this->load->helper('form');
        $this->load->library('form_validation');
        $data['title'] = lang('contract_edit_title');
        
        $this->form_validation->set_rules('name', lang('contract_edit_field_name'), 'required|xss_clean');
        $this->form_validation->set_rules('startentdatemonth', lang('contract_edit_field_start_month'), 'required|xss_clean');
        $this->form_validation->set_rules('startentdateday', lang('contract_edit_field_start_day'), 'required|xss_clean');
        $this->form_validation->set_rules('endentdatemonth', lang('contract_edit_field_end_month'), 'required|xss_clean');
        $this->form_validation->set_rules('endentdateday', lang('contract_edit_field_end_day'), 'required|xss_clean');

        $data['contract'] = $this->contracts_model->get_contracts($id);
        if (empty($data['contract'])) {
            show_404();
        }

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('templates/header', $data);
            $this->load->view('menu/index', $data);
            $this->load->view('contracts/edit', $data);
            $this->load->view('templates/footer');
        } else {
            $this->contracts_model->update_contract();
            $this->session->set_flashdata('msg', lang('contract_edit_msg_success'));
            redirect('contracts');
        }
    }
    
    /**
     * Display the form / action Create a new contract
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function create() {
        $this->auth->check_is_granted('create_contract');
        $data = getUserContext($this);
        $this->load->helper('form');
        $this->load->library('form_validation');
        $data['title'] = lang('contract_create_title');

        $this->form_validation->set_rules('name', lang('contract_create_field_name'), 'required|xss_clean');
        $this->form_validation->set_rules('startentdatemonth', lang('contract_create_field_start_month'), 'required|xss_clean');
        $this->form_validation->set_rules('startentdateday', lang('contract_create_field_start_day'), 'required|xss_clean');
        $this->form_validation->set_rules('endentdatemonth', lang('contract_create_field_end_month'), 'required|xss_clean');
        $this->form_validation->set_rules('endentdateday', lang('contract_create_field_end_day'), 'required|xss_clean');

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('templates/header', $data);
            $this->load->view('menu/index', $data);
            $this->load->view('contracts/create', $data);
            $this->load->view('templates/footer');
        } else {
            $this->contracts_model->set_contracts();
            $this->session->set_flashdata('msg', lang('contract_create_msg_success'));
            redirect('contracts');
        }
    }
    
    /**
     * Delete a given contract
     * @param int $id contract identifier
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function delete($id) {
        $this->auth->check_is_granted('delete_contract');
        //Test if the contract exists
        $data['contract'] = $this->contracts_model->get_contracts($id);
        if (empty($data['contract'])) {
            show_404();
        } else {
            $this->contracts_model->delete_contract($id);
        }
        $this->session->set_flashdata('msg', lang('contract_delete_msg_success'));
        redirect('contracts');
    }
    
    /**
     * Display an interactive calendar that allows to dynamically set the days
     * off, bank holidays, etc. for a given contract
     * @param type $id contract identifier
     * @param type $year optional year number (4 digits), current year if empty
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function calendar($id, $year = 0) {
        $this->auth->check_is_granted('calendar_contract');
        $data = getUserContext($this);
        $data['title'] = lang('contract_calendar_title');
        $data['help'] = $this->help->create_help_link('global_link_doc_page_contracts_calendar');
        if ($year <> 0) {
            $data['year'] = $year;
        } else {
            $data['year'] = date("Y");
        }
        $contract = $this->contracts_model->get_contracts($id);
        $data['contract_id'] = $id;
        $data['contract_name'] = $contract['name'];
        $data['contract_start_month'] = intval(substr($contract['startentdate'], 0, 2));
        $data['contract_start_day'] = intval(substr($contract['startentdate'], 3));
        $data['contract_end_month'] = intval(substr($contract['endentdate'], 0, 2));
        $data['contract_end_day'] = intval(substr($contract['endentdate'], 3));
        $this->load->model('dayoffs_model');
        $data['dayoffs'] = $this->dayoffs_model->get_dayoffs($id, $data['year']);
        $this->load->view('templates/header', $data);
        $this->load->view('menu/index', $data);
        $this->load->view('contracts/calendar', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Ajax endpoint : add a day off to a contract
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function editdayoff() {
        if ($this->auth->is_granted('adddayoff_contract') == FALSE) {
            $this->output->set_header("HTTP/1.1 403 Forbidden");
        } else {
            $contract = $this->input->post('contract', TRUE);
            $timestamp = $this->input->post('timestamp', TRUE);
            $type = $this->input->post('type', TRUE);
            $title = $this->input->post('title', TRUE);
            if (isset($contract) && isset($timestamp) && isset($type) && isset($title)) {
                $this->load->model('dayoffs_model');
                $this->output->set_content_type('text/plain');
                if ($type == 0) {
                    echo $this->dayoffs_model->deletedayoff($contract, $timestamp);
                } else {
                    echo $this->dayoffs_model->adddayoff($contract, $timestamp, $type, $title);
                }
            } else {
                $this->output->set_header("HTTP/1.1 422 Unprocessable entity");
            }
        }
    }
    
    /**
     * Ajax endpoint : Edit a series of day offs for a given contract
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function series() {
        if ($this->auth->is_granted('adddayoff_contract') == FALSE) {
            $this->output->set_header("HTTP/1.1 403 Forbidden");
        } else {
            if ($this->input->post('day', TRUE) !=null && $this->input->post('type', TRUE) !=null &&
                    $this->input->post('start', TRUE) !=null && $this->input->post('end', TRUE) !=null
                     && $this->input->post('contract', TRUE) !=null) {
                expires_now();
                header("Content-Type: text/plain");

                //Build the list of dates to be marked
                $start = strtotime($this->input->post('start', TRUE));
                $end = strtotime($this->input->post('end', TRUE));
                $type = $this->input->post('type', TRUE);
                $day = strtotime($this->input->post('day', TRUE), $start);
                $list = '';
                while ($day <= $end) {
                    $list .= date("Y-m-d", $day) . ",";
                    $day = strtotime("+1 weeks", $day);
                }
                $list = rtrim($list, ",");
                $contract = $this->input->post('contract', TRUE);
                $title = $this->input->post('title', TRUE);
                $this->load->model('dayoffs_model');
                $this->dayoffs_model->deletedayoffs($contract, $list);
                if ($type != 0) {
                    $this->dayoffs_model->adddayoffs($contract, $type, $title, $list);
                    echo 'updated';
                } else {
                    echo 'deleted';
                }
            } else {
                $this->output->set_header("HTTP/1.1 422 Unprocessable entity");
            }
        }
    }
    
    /**
     * Ajax endpoint : Send a list of fullcalendar events
     * List of day offs for the connected user
     * @param int $id employee id or connected user (from session)
     */
    public function userDayoffs($id = 0) {
        expires_now();
        header("Content-Type: application/json");
        $start = $this->input->get('start', TRUE);
        $end = $this->input->get('end', TRUE);
        $this->load->model('dayoffs_model');
        if ($id == 0) $id =$this->user_id;
        echo $this->dayoffs_model->userDayoffs($id, $start, $end);
    }
    
    /**
     * Ajax endpoint : Send a list of fullcalendar events
     * List of all possible day offs
     * @param int $entity_id Entity identifier
     */
    public function allDayoffs() {
        expires_now();
        header("Content-Type: application/json");
        $start = $this->input->get('start', TRUE);
        $end = $this->input->get('end', TRUE);
        $entity = $this->input->get('entity', TRUE);
        $children = filter_var($this->input->get('children', TRUE), FILTER_VALIDATE_BOOLEAN);
        $this->load->model('dayoffs_model');
        echo $this->dayoffs_model->allDayoffs($start, $end, $entity, $children);
    }
    
    /**
     * Action: export the list of all contracts into an Excel file
     * @author Benjamin BALET <benjamin.balet@gmail.com>
     */
    public function export() {
        $this->auth->check_is_granted('export_contracts');
        expires_now();
        $this->load->library('excel');
        $this->excel->setActiveSheetIndex(0);
        $this->excel->getActiveSheet()->setTitle(lang('contract_index_title'));
        $this->excel->getActiveSheet()->setCellValue('A1', lang('contract_export_thead_id'));
        $this->excel->getActiveSheet()->setCellValue('B1', lang('contract_export_thead_name'));
        $this->excel->getActiveSheet()->setCellValue('C1', lang('contract_export_thead_start'));
        $this->excel->getActiveSheet()->setCellValue('D1', lang('contract_export_thead_end'));
        $this->excel->getActiveSheet()->getStyle('A1:D1')->getFont()->setBold(true);
        $this->excel->getActiveSheet()->getStyle('A1:D1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $users = $this->contracts_model->get_contracts();
        $line = 2;
        foreach ($users as $user) {
            $this->excel->getActiveSheet()->setCellValue('A' . $line, $user['id']);
            $this->excel->getActiveSheet()->setCellValue('B' . $line, $user['name']);
            $this->excel->getActiveSheet()->setCellValue('C' . $line, $user['startentdate']);
            $this->excel->getActiveSheet()->setCellValue('D' . $line, $user['endentdate']);
            $line++;
        }
        
        //Autofit
        foreach(range('A', 'D') as $colD) {
            $this->excel->getActiveSheet()->getColumnDimension($colD)->setAutoSize(TRUE);
        }

        $filename = 'contracts.xls';
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
        $objWriter->save('php://output');
    }
}
