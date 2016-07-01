<?php namespace API\Controllers\Stat;

/**
 * Created by it140488kao
 * Date: 29.04.2016
 */

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use Exception;
use API\Controllers\APIController;
use API\Classes\LogicalModels\Preventions\PreventionFactory;

use Helpers\GenericHelper;


class PreventionController extends APIController
{
    public function savePreventionData()
    {

        try{
            list($request, $response) = GenericHelper::apiPrepareForTwoConveyorPostFormats($this);

            $data['device_type']     = Input::get('device_type', '');
            $data['compassname']     = Input::get('compassname', '');
            $data['engineer_ldap'] = Input::get('engineer_ldap', '');
            $data['prevention_date'] = Input::get('prevention_date', '');

            $validation_rules = [
                'device_type'=>'required|integer',
                'compassname'=>'required',
                'engineer_ldap'=>'required'
            ];

            $validator = Validator::make($data, $validation_rules);

            if(!$validator->passes()){
                return $response->makeValidationBadResponse($validator);
            }

            $obj = PreventionFactory::getInstance($data['device_type']);


            if($obj->setPreventionData($data['compassname'], $data['engineer_ldap'], $data['prevention_date'])){
                return $response->makeSingleGoodResponse(['res' => 'saved']);
            }else{
                throw new Exception("DB error", 415);
            }

        } catch (Exception $e) {
            return $response->makeSingleBadResponse($e->getMessage(), $e->getCode());
        }
    }
}