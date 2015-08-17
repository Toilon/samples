   public function getFavoriteBranches()
    {


        $planner_branch_cache = $this->cache->file->get('cashplanner_favorite_branch_'.$_SESSION['user_login']);
        if( !is_array($planner_branch_cache) )
        {
        $res = $this->cashmanager_db->query("  select distinct  a.syb_branch as SybBranch, b.bbp_pidd as SybBranchName,
				upper(a.bush_branch) as BushBranch, b2.BBP_PIDD as BushBranchName
				from [CashForcast].[dbo].[hwCashPlanner_favorite_branch] a
				join back..bprp b on a.syb_branch=b.eca_brnm COLLATE SQL_Latin1_General_CP1251_CI_AS
				join back..bprp b2 on a.bush_branch=b2.eca_brnm COLLATE SQL_Latin1_General_CP1251_CI_AS
				where a.ldap_login='{$_SESSION['user_login']}' order by syb_branch");

        $ret_arr = array();
        foreach($res->result_array() as $row)
        {
            $ret_arr[$row['SybBranch']."|".$row['BushBranch']] = $row['SybBranch']."-".$row['BushBranch']."  ".$row['SybBranchName'].". ".$row['BushBranchName'];


        }
            $res = $this->cache->file->save('cashplanner_favorite_branch_'.$_SESSION['user_login'], 3600);
        }
        else
        {
            $ret_arr = $planner_branch_cache;
        }

        return $ret_arr;


    }