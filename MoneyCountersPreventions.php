<?php namespace API\Classes\LogicalModels\Preventions;

use API\Models\Treasury\DeviceList;


class MoneyCountersPreventions implements PreventionInterface
{
    public function setPreventionData($compassname, $ldapLogin, $lastProf)
    {
        $atmParam = DeviceList::find($compassname);
        $atmParam->last_prof = $lastProf;
        return $atmParam->save();
    }
}
