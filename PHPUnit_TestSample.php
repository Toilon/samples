<?

class Cashplanner_modelTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->CI =& get_instance();
        $this->CI->load->model("cashplanner/cashplanner_model", "planner", true);
// $this->planner = new Inkassconv_model();

        $cashmanager = $this->CI->load->database('cashmanager', TRUE);
        $results = $cashmanager->get('CashForcast..cashplanner_config')->result();

        foreach ($results as $setting) {
            $this->CI->config->set_item("cashplanner_" . $setting->config_key, $setting->value);
        }

    }

    public function testConveyorCheck()
    {


        if ($this->CI->config->item('cashplanner_conveyor_need') == 0) {
            $this->assertNotTrue($this->CI->planner->CheckConveyorNeed("test", "test"));
        } else {

            $this->assertTrue($this->CI->planner->CheckConveyorNeed('CSH0', 'CSH0'));
            $this->assertTrue($this->CI->planner->CheckConveyorNeed('CSSJ', 'CSH0'));
            $this->assertTrue($this->CI->planner->CheckConveyorNeed('K4H0', 'K3H0'));
            $this->assertTrue($this->CI->planner->CheckConveyorNeed('K4H0', 'K4H0'));
            $this->assertTrue($this->CI->planner->CheckConveyorNeed('K4H0', 'K5H0'));
            $this->assertTrue($this->CI->planner->CheckConveyorNeed('PLR0', 'PLR0'));

            $this->assertNotTrue($this->CI->planner->CheckConveyorNeed('PLR0', 'PLR1'));
            $this->assertNotTrue($this->CI->planner->CheckConveyorNeed('DN1F', 'DNH0'));
            $this->assertNotTrue($this->CI->planner->CheckConveyorNeed('KGSJ', 'KGH0'));
        }
    }


    public function testGettingBranchList()
    {
        $branch_list = $this->CI->planner->getBranchList(1);
        $this->assertNotCount(0, $branch_list);
        $this->assertTrue(is_array($branch_list));

    }

    public function testGetReasonSourceList()
    {
        $reason_list = $this->CI->planner->GetReasonSourceList();
        $this->assertNotCount(0, $reason_list);
        $this->assertTrue(is_array($reason_list));
    }

    public function testGetBankModules()
    {
        $bank_modules = $this->CI->planner->GetBankModules();
        $this->assertNotCount(0, $bank_modules);
        $this->assertTrue(is_array($bank_modules));
    }


    public function testGetManualPositionerInfo()
    {
        try {
            $this->CI->planner->GetManualPositionerInfo('PLR0', 'PLR0');
        } catch (Exception $e) {
            $this->fail();
        }

    }

    public function testDailyStatRegular()
    {
        $branch_list = $this->CI->planner->getBranchList(1);
        foreach ($branch_list as $branch => $name) {
            list($syb_branch, $bush_branch) = explode("|", $branch);
            $this->assertTrue((bool)strlen($syb_branch));
            $this->assertTrue((bool)strlen($bush_branch));

            $test_data = $this->CI->planner->GetDayStat('', $syb_branch, $bush_branch);
            foreach ($test_data as $date => $states) {
                $summ = $states['state_0'] + $states['state_1'] + $states['state_2'] + $states['in_route'] + $states['not_in_route'];
                $this->assertEquals($summ, $states['total']);
            }
        }
    }


    public function testDailyStatManual()
    {
        $branch_list = $this->CI->planner->getBranchList(1);
        foreach ($branch_list as $branch => $name) {

            list($syb_branch, $bush_branch) = explode("|", $branch);
            $this->assertTrue((bool)strlen($syb_branch));
            $this->assertTrue((bool)strlen($bush_branch));
            $test_data = $this->CI->planner->GetDayStatManual('', $syb_branch, $bush_branch);
            foreach ($test_data as $date => $states) {
                $summ = $states['state_0'] + $states['state_1'] + $states['state_2'] + $states['in_route'] + $states['not_in_route'];
                $this->assertEquals($summ, $states['total']);
            }
        }
    }


    public function testGetManualRequestList()
    {
        $branch_list = $this->CI->planner->getBranchList(1);
        foreach ($branch_list as $branch => $name) {
            list($syb_branch, $bush_branch) = explode("|", $branch);
            $manual_requests = $this->CI->planner->GetManualRequestList($syb_branch, $bush_branch, array(), 0);
            $this->assertTrue(is_array($manual_requests));

            foreach ($manual_requests as $request) {
                $this->assertArrayHasKey('pozicioner_sm', $request);


            }

        }

    }


}