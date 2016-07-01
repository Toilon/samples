<?php namespace API\Classes\LogicalModels\Preventions;

interface PreventionInterface
{
    /**
     * @param $compassname
     * @param $ldapLogin
     * @param $lastProf
     * @return boolean
     */
    public function setPreventionData($compassname, $ldapLogin, $lastProf);
}