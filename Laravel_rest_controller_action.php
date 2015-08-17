   public function getMerchantAdditionalInfo()
    {
        $this->es_logger->addNotice("Input: " . var_export(Input::all(), true), ['input' => true]);
        $response = new ConveyorHttpResponse();
        $validation_rules_generic = [
            'merchant' => 'required|alpha_num|size:8',
        ];
        $validator = Validator::make(Input::all(), $validation_rules_generic);

        if ($validator->passes()) {
            $merchant = Input::get('merchant', '');
            $this->es_logger->addGlobalContext(['merchant' => $merchant]);

            $additional_data_formatted = AwardMerchant::getStorageAndEngineerInfoFormatted($merchant);
            $answerParam = empty($additional_data_formatted) ? 0 : 'ok';

            $this->es_logger->addNotice(
                "Merchant [$merchant] additional info (response): " . var_export($additional_data_formatted, true),
                array_merge(
                    [
                        'success_response' => true,
                        'response' => true,
                        'http_status' => 200
                    ],
                    $additional_data_formatted
                )
            );
            return $response->makeSingleGoodResponse($additional_data_formatted, $answerParam);

        } else { // Validation failed
            $validation_error = ValidatorHelper::getErrorsAsString($validator);
            $this->es_logger->addWarning("Web service input validation error: $validation_error.",
                ['error_code' => 'validation_error', 'http_status' => 400]);
            return $response->makeValidationBadResponse($validator);
        }
    }