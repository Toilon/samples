<?php namespace API\Classes\LogicalModels\Preventions;

use API\Models\AtmBase\AtmParam;


class AtmAndSSTPreventions implements PreventionInterface
{
    public function setPreventionData($compassname, $ldapLogin, $lastProf)
    {
        $param = new AtmParam();
        $atmParam = AtmParam::find($compassname);
        $atmParam->find($compassname);
        $atmParam->last_prof = $lastProf;
        $atmParam->ldap_prof = $ldapLogin;
        return $atmParam->save();
    }
}
