<?php
    public function paycardconfirmAction()
    {

        Zend_Layout::getMvcInstance()->assign('page_title', "Подтверждение оплаты");
        Zend_Layout::getMvcInstance()->assign('prev_action', "Способ оплаты");
        Zend_Layout::getMvcInstance()->assign('prev_form_action', "paymentsource");

        Zend_Layout::getMvcInstance()->assign('css', array("cardconfirm.css"));
        Zend_Layout::getMvcInstance()->assign('js', array("cardconfirm.js"));


        $request = $this->getRequest();
        $posted_data = $request->getPost();
        unset($posted_data['ivr_reference']);
        Zend_Layout::getMvcInstance()->assign('prev_action_data', $this->ExtractPrevStepParams($posted_data));


        $this->view->next_step_params = $this->CreateNextStepParams($posted_data);
        $validate_user = $this->IsValidNeeded($posted_data);


        $mask = new Applications_MaskOutput();

        $this->view->client_phone = $mask->MaskPhoneNum($_SESSION['client'][$posted_data['ident']]['phone']);
        $this->view->client_phone_raw = $_SESSION['client'][$posted_data['ident']]['phone'];

        $this->view->validate_user = $validate_user;


        if ($validate_user)
        {
                $payment_basket = new payment_basket();
                $payment_basket->SendSMSVerifitation($_SESSION['client'][$posted_data['ident']]['phone']);

        }
        $curr = new Applications_CurrManager();
        $this->view->client_card = $this->mask_card($posted_data['client_card_no']);

        $this->view->request_sm = $posted_data['need_to_pay_string'];
        $this->view->total_sm = $posted_data['need_to_pay_string'];
        $this->view->next_step = "paycardprocess";


    }