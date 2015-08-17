<?php

class imServiceTest extends PHPUnit_Framework_TestCase
{

    var $CI;
    var $model;

    public function setUp()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('incident_manager/im_model');
        $this->im = new Im_model();

    }


    public function testDistansecalc()
    {
        $this->assertTrue(True);
    }


    public function testOperatorCheck()
    {

        $db = $this->CI->load->database();
        $res = $this->CI->db->query("select operator, initial_status_proc from [IncidentManager].[dbo].[DIC_OperatorList] where operator!='auto' and  operator!='foto'  ");

        foreach ($res->result_object() as $row) {
            $actual_status = $this->im->operator_check($row->operator);
            $this->assertTrue(is_object($actual_status));
            $this->assertObjectHasAttribute("initial_status_proc", $actual_status);
            $this->assertEquals((int)$row->initial_status_proc, $actual_status->initial_status_proc);
        }
        $this->assertFalse($this->im->operator_check("auto"));


    }


    public function testDistanceFormer()
    {
        $this->assertStringEndsWith('...', $this->im->PrepareTaskText($this->generateRandomString(10000)));
        $random_string = $this->generateRandomString(100);
        $this->assertSame($this->im->PrepareTaskText($random_string), $this->im->PrepareTaskText($random_string));
        $random_string = $this->generateRandomString(5000);
        $this->assertSame($this->im->PrepareTaskText(substr($random_string, 0, 2500)), $this->im->PrepareTaskText(substr($random_string, 0, 3000)));
        $this->assertNotSame($this->im->PrepareTaskText(substr($random_string, 0, 500)), $this->im->PrepareTaskText(substr($random_string, 0, 3000)));

        $this->assertSame($this->im->PrepareTaskText("==>"), htmlentities("==>"));
        $this->assertNotContains("-->", $this->im->PrepareTaskText("==>"));

    }


    public function testSaveIncidentUserCantSendAutoOperator()
    {
        require_once('../controllers/incident_manager/im.php');
        $data = '{"data":
                    {
                    "external_problem_id": "1111",
                    "point_type": "7",
                    "point": "7",
                    "operator": "HelpDesc",
                    "problem_text": "тестирование ",
                    "group_id":"339",
                    "sub_group_id":"339",
                    "lon":"46",
                    "lat":"47"
                    }}';


        $this->CI->im = new Im;
        $this->CI->db = $this->CI->load->database();

        $this->CI->im->http_client = $this->getMock('http_client');
        $this->CI->im->http_client->expects($this->any())->method('get_post')->willReturn($data);

        $_GET['format'] = "json";
         ob_start();
        $response = $this->CI->im->SaveIncident();

        $response = ob_get_contents();

        ob_end_clean();
        $this->assertJson($response);


    }


    private function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }


}