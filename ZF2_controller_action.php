<?php

    public function metalPricesAction()
    {
        $this->layout('layout/admin');
        $sm = $this->getServiceLocator();

        if($this->params()->fromPost('action')=="save")
        {
            $metal = new Metal();
            $metal->price = $this->params()->fromPost('metall_price');
            $metal->proba = $this->params()->fromPost('form_proba');
            $metal->type = $this->params()->fromPost('form_metall_type');
            $sm->get('Metal\Model\MetalPriceTable')->save($metal);
        }

        $e = $sm->get('Metal\Model\MetalPriceTable');
        $gold = $e->fetchGold();
        
        $silver = $e->fetchSilver();
       
        return new ViewModel(array(
            "goldList"=>$gold, "silverList" => $silver
            )
        );
    }		