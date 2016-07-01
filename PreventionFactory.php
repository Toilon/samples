<?php

namespace API\Classes\LogicalModels\Preventions;

use API\Classes\LogicalModels\Preventions\AtmAndSSTPreventions;
use API\Classes\LogicalModels\Preventions\MoneyCountersPreventions;
use API\Classes\LogicalModels\Preventions\NotImplimentedDeviceTypeException;


class PreventionFactory
{
    public static function getInstance($device_type)
    {
        switch($device_type)
        {
            case 1:
            case 2: $obj = new AtmAndSSTPreventions(); break;
            case 7: $obj = new MoneyCountersPreventions(); break;
            default: throw new NotImplimentedDeviceTypeException('Model for '.$device_type.' device type is not implemented');
        }
        return $obj;
    }
}
