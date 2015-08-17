      
  public function exams()
    {

        $this->load->model('branch_model', 'branch', true);


        $data['branches'] = $this->branch->SelectBranch("syb_branch", 1);
        $data['branches_window'] = $this->branch->SelectBranch("syb_branch", 0);

        $exams_type = $this->manager->ExamsTypes();
        $data['emax_types']=$exams_type;
        unset($exams_type[""]);
        $data['emax_types_window']=$exams_type;

        $status_proc=$this->manager->GetDicExamState();

        $data['status_proc'][""] = "Все статусы";
        foreach ($status_proc as $key=>$row)
        {
            $data['status_proc'][$key] = $row['status_name'];
        }

  
        if($this->input->post("act")=="search")
        {
            $data['check_lists'] = "";
            $exams = $this->manager->GetExamsRequest($this->input->post());

            $data['main_table'] = $this->parser->parse('incident_manager/Main/exams/header', array(), TRUE);
            $i=0;
            $questions_list = $this->manager->GetReportQuestions();

            $q_id =  new RecursiveArrayIterator($questions_list);
            $q_it2 = new RecursiveIteratorIterator($q_id);
            foreach ($exams as $exam)
            {
                $data_check['questions_list'] = $questions_list;
                //$report_json = $request_data[0]['poll'];

                $answer_array = (array)json_decode($exam['json_result']);

                $total_q = 0;
                $good_answer = 0;

                if(count($answer_array)>0)
                {
                    foreach ($q_it2 as $question_code=>$section_list)
                    {
                        $data_check[]=$question_code;
                        $total_q++;
                        if(isset($answer_array[$question_code]) && $answer_array[$question_code]->AnswerText=="1")
                        {
                            $good_answer++;
                        }
                    }
                }


                $result_exam = 	round(($good_answer*100)/$total_q,2)."%";






                $data_check['answers'] = $answer_array;
                $data_check['request_id'] = $exam["id"];

                $data['check_lists'] .= $this->parser->parse('incident_manager/Main/exams/checklist', $data_check, TRUE);




                $i++;
                $exam['pp_num'] = $i;

                switch ($exam['status'])
                {
                    case '0': $exam['status_td'] = $this->parser->parse('incident_manager/Main/exams/status_0', $exam, TRUE);
                        $exam['engineer_field'] = $this->parser->parse('incident_manager/Main/exams/status_0_enginner_set', $exam, TRUE);
                        break;
                    case '1': $exam['status_td'] = $this->parser->parse('incident_manager/Main/exams/status_1', $exam, TRUE);
                        $exam['engineer_field'] = $this->parser->parse('incident_manager/Main/exams/status_1_enginer_field', $exam, TRUE);
                        break;

                    default: $exam['status_td'] = $this->parser->parse('incident_manager/Main/exams/status_def', $exam, TRUE);
                    $exam['engineer_field'] = $this->parser->parse('incident_manager/Main/exams/status_1_enginer_field', $exam, TRUE);  break;
                }


                if($exam['status']=="4")
                {
                    $exam['checklist_field'] = $this->parser->parse('incident_manager/Main/exams/checklist_field', $exam, TRUE);
                    $exam['test_result'] = $result_exam;
                }
                else
                {
                    $exam['checklist_field'] = "";
                    $exam['test_result']="";
                }


                $data['main_table'] .= $this->parser->parse('incident_manager/Main/exams/row', $exam, TRUE);

            }
            $data['main_table'] .= $this->parser->parse('incident_manager/Main/exams/footer', array(), TRUE);

            $data['access_table'] = $this->GetEngCertsTable();


        }



        $this->load->view('layout/header_new');

        $this->load->view('incident_manager/Main/exams/search_form',$data);
        $this->load->view('layout/footer_new');

    }