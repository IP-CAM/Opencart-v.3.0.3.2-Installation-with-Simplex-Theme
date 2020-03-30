<?php

class ControllerExtensionCreditCardMaibCommerce extends Controller
{
    public function install() {
        // Register the event triggers
        $this->load->model('setting/event');

        $this->model_setting_event->addEvent('transactionMAIB', 'catalog/controller/extension/payment/bank_transfer/confirm/before', 'extension/event/transaction/redirect');
    }

    public function uninstall()
    {
        $this->load->model('setting/event');

        $this->model_setting_event->deleteEvent('transactionMAIB');
    }
}