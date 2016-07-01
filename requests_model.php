<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class Requests_model
 * Класс для работы с заявками в инцидент менеджере
 */
class Requests_model extends CI_Model
{

    /**
     * Метод возвращает все заявки в Инцидент менеджере по группе
     * @param $group_id
     * @param int $sub_group
     * @return array
     */
    public function getRequets($group_id, $group_type = '', $region_type = '')
    {

        $awardGroupsList = new \ArrayIterator($this->award_model->getAwardGroups());
        $awardGroupsList = new \Diint\Iterators\FilterOnlySubAwardGroup($awardGroupsList, $group_id);

        if (is_array($region_type) && (count($region_type) > 1 || (count($region_type) == 1 && $region_type[0] != "-1"))) {
            $awardGroupsList = new \Diint\Iterators\AwardGroupsRegionFilter($awardGroupsList, $region_type);
        }

        $result_array = $groups = [];
        foreach ($awardGroupsList as $group) {
            $groups[$group->getGroupId()] = $group->getGroupName();
        }
        natsort($groups);

        foreach ($groups as $key => $group) {
            $result_array[$key] = [];
        }
        unset($groups);


        $this->db->select('i.id, i.engineer_ldap, i.CausingProblem, i.ProblemReportId, i.ProblemCategory, i.group_id, i.sub_group_id, i.point_type, i.point, i.point_state, i.ins_date, i.Status_dev,
                           i.dispetcher_ldap, i.route_list_id, i.distance, i.point_lat, i.point_lon, i.dispetcher_view_dt, i.take_in_work_ldap,i.take_in_work_dt, i.close_dt, i.delay_dt,
                           i.Status_proc, i.pp_num, i.deadline_dt, i.device_adres, i.operator, i.problem_text, i.in_route, i.CausingSubProblem, i.start_work_dt, isnull(i.request_type,0) as request_type, d.allow_concat,
                           (select COUNT(*) from AtmBase..incident_manager_requestedit_log where incident_id= i.id) as red_cnt, im_merch.award_group_id as  original_group_id');

        $this->db->from('AtmBase..incident_manager i');
        $this->db->join('AtmBase..incident_manager_device_types d', ' d.id=i.point_type  ');
        $this->db->join('IncidentManager..Dic_points im_merch', ' im_merch.merchant = i.point and im_merch.device_types=  i.point_type  ', 'left');
        $this->db->where_in('Status_proc', [0, 1, 2, 5, 10, 11]);
        if ($group_type != 'all') {
            $this->db->where('i.point_type', (int)$group_type);
        }
        $this->db->where('i.group_id', $group_id);
        $this->db->order_by("status_dev", "desc");
        $this->db->order_by("pp_num", "asc");

        $this->db->order_by("Status_proc", "desc");
        $this->db->order_by("ins_date", "asc");

        $this->db->order_by("point", "asc");
        $this->db->order_by("isnull(i.request_type,0) asc");





        $query = $this->db->get();

        $glueArray = [];

        $i = 0;
        $timeFromAdditionalTasks = 0;
        foreach ($query->result_array() as $request) {

            $request['point'] = strtoupper($request['point']);
            $arrayKey = $request['point'] . "@" . $request['point_type']. "@" . $request['Status_proc'];

            if ($request['allow_concat'] == "1") {
                if (array_key_exists($arrayKey, $glueArray)) {
                    $ins_date = new DateTime($request['ins_date']);
                    $prev_ins_date = new DateTime($glueArray[$arrayKey]['ins_date']);

                    $glueArray[$arrayKey]['unix_ins_date'] = $prev_ins_date->getTimestamp();
                    if ($ins_date < $prev_ins_date && $request['request_type'] != "1") {
                        $glueArray[$arrayKey]['ins_date'] = $ins_date->format('Y-m-d H:i:s');
                        $glueArray[$arrayKey]['unix_ins_date'] = $ins_date->getTimestamp();
                    }


                    $deadlineDt = new DateTime($request['deadline_dt']);
               
                    if($request['request_type'] == 0 && $glueArray[$arrayKey]['deadline_dt']=="")
                    {
                        $glueArray[$arrayKey]['deadline_dt'] = $deadlineDt->format('d-m-Y H:i');
                    }
                    else
                    {
                        $prev_deadlineDt = new DateTime($glueArray[$arrayKey]['deadline_dt']);

                        $glueArray[$arrayKey]['unix_deadlinedt'] = $prev_deadlineDt->getTimestamp();

                        if ($deadlineDt < $prev_deadlineDt  && $request['request_type'] != "1") {
                            $glueArray[$arrayKey]['deadline_dt'] = $deadlineDt->format('d-m-Y H:i');
                            $glueArray[$arrayKey]['unix_deadlinedt'] = $deadlineDt->getTimestamp();
                        }
                    }

                    if ($request['Status_proc'] == "2") {
                        $glueArray[$arrayKey]['Status_proc'] = 2;
                    }
                    $glueArray[$arrayKey]['device_problem'] .= ";" . $request['problem_text'];
                    $glueArray[$arrayKey]['grouped_ids'] .= $request['id'] . ";";
                } else {
                    $ins_date = new DateTime($request['ins_date']);
                    $glueArray[$arrayKey] = $request;
                    $glueArray[$arrayKey]['unix_ins_date'] = $ins_date->getTimestamp();


                    if($request['request_type'] == "0")
                    {

                        $deadlineDt = new DateTime($request['deadline_dt']);
                        $glueArray[$arrayKey]['deadline_dt'] = $deadlineDt->format('d-m-Y H:i');
                    }
                    else
                    {
                        $glueArray[$arrayKey]['deadline_dt']="";
                    }

                    $glueArray[$arrayKey]['device_problem'] = $request['problem_text'];
                    $glueArray[$arrayKey]['device_adress'] = $request['device_adres'];
                    $glueArray[$arrayKey]['grouped_ids'] = $request['id'] . ";";
                }
            }else
            {
                $ins_date = new DateTime($request['ins_date']);
                $deadlineDt = new DateTime($request['deadline_dt']);
                $glueArray[$arrayKey.$i] = $request;
                $glueArray[$arrayKey.$i]['unix_ins_date'] = $ins_date->getTimestamp();


                $glueArray[$arrayKey.$i]['deadline_dt'] = $deadlineDt->format('d-m-Y H:i');
                $glueArray[$arrayKey.$i]['device_problem'] = $request['problem_text'];
                $glueArray[$arrayKey.$i]['device_adress'] = $request['device_adres'];
                $glueArray[$arrayKey.$i]['grouped_ids'] = $request['id'] . ";";
            }

            $i++;

        }

        $where = "";
        if ($group_type != 'all') {
            $where = " and point_type = " . (int)$group_type;
        }

        $res = $this->db->query("SELECT i.id into #tt
                        FROM IncidentManager..incident_manager_archive i
                        WHERE i.group_id = $group_id
                        AND Status_proc in (4,3,7) $where
                        AND DATEDIFF(hour, close_dt, GETDATE())<12

                        SELECT id, engineer_ldap, CausingProblem, ProblemReportId, ProblemCategory, group_id, sub_group_id, point_type, point, point_state, ins_date, Status_dev,
                               dispetcher_ldap, route_list_id, distance, point_lat, point_lon, dispetcher_view_dt, take_in_work_ldap, take_in_work_dt, close_dt, delay_dt, Status_proc,
                               pp_num, deadline_dt, device_adres, operator, problem_text, in_route, CausingSubProblem, start_work_dt,
                               request_type, problem_text as device_problem, rtrim(convert(char(15), id))+';' as grouped_ids
                               from IncidentManager..incident_manager_archive where id in( select id from #tt )
                        drop table #tt");
        //echo "<pre>". $this->db->last_query()."</pre>";
        $i=0;
        foreach ($res->result_array() as $request)
        {

            $deadlineDt = new DateTime($request['deadline_dt']);
            $glueArray[$i]['deadline_dt'] = $deadlineDt->format('d-m-Y H:i');
            $glueArray[$i] = $request;
            $i++;
        }


        foreach ($glueArray as $gluedRequest) {

            if (array_key_exists($gluedRequest['sub_group_id'], $result_array)) {

                $result_array[$gluedRequest['sub_group_id']][] = $gluedRequest;
            }

        }


        return $result_array;
    }


    /**
     * Подсчет новых заявок среди заявок. Признако новых - pp_num=-1
     * @param $requests array
     */
    public function getNewRequestsCount($requests)
    {
        $result_arr = [];
        foreach ($requests as $group_id => $requests) {
            $result_arr[$group_id] = 0;
            foreach ($requests as $request) {
                if ($request['pp_num'] === -1 && $request['Status_proc'] == 0) {
                    $result_arr[$group_id]++;
                }
            }
        }
        return $result_arr;
    }


    public function getAdditionalTasks($group_id)
    {
        $this->db->select("e.id as request_id, e.engineer_ldap, source, address, task, ins_date, ready_date, take_to_work_date, close_date, status_proc");

        $this->db->from('IncidentManager..incident_manager_additional_engineer e');
        $this->db->join('atmbase..award_users_all a', ' a.ldap_login=e.engineer_ldap ');
        $this->db->where('a.group_id', (int)$group_id);
        $this->db->where("( status_proc in (1,2) or (status_proc=4 and datediff(hour, close_date, getdate() )<12))");

        $res = $this->db->get();

        $ret_arr = [];
        foreach ($res->result_array() as $request) {
            $ret_arr[$request['engineer_ldap']][] = $request;
        }

        return $ret_arr;

    }


}




