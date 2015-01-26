<?php

/**
 * @copyright wiseape GmbH
 * @author Ruben Rögels
 * @license LGPL-3.0+
 */

namespace Wiseape\Payum\Paymill\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Action\PaymentAwareAction;
use Paymill\Request;
use Paymill\Services\PaymillException;
use Paymill\Models\Request\Transaction as TransactionRequest;
use Wiseape\Payum\Paymill\Keys;
use Wiseape\Payum\Paymill\Request\Api\CreateTransaction;

class CreateTransactionAction implements ActionInterface, ApiAwareInterface {

    /**
     * @var Keys
     */
    protected $keys;

    /**
     * {@inheritDoc}
     */
    public function setApi($api) {
        if(false == $api instanceof Keys) {
            throw new UnsupportedApiException('Not supported.');
        }

        $this->keys = $api;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request) {
        /* @var $request CreateCharge */
        RequestNotSupportedException::assertSupports($this, $request);

        /* @var $details \ArrayAccess */
        $details = $request->getModel();

        if(!isset($details['token']) || !strlen($details['token'])) {
            throw new LogicException('The token has to be set.');
        }

        try {
            $paymillTxn = new TransactionRequest;

            $paymillTxn->setToken($details['token']);

            $paymillTxn->setAmount($details['amount']);
            $paymillTxn->setCurrency($details['currency']);
            $paymillTxn->setDescription($details['description']);

            $paymillRequest = new Request($this->keys->getSecretKey());
            $paymillRequest->create($paymillTxn);

            $details['http_status_code'] = 200;
            $details['transaction'] = $paymillRequest->getLastResponse()['body']['data'];
        } catch(PaymillException $e) {

            $details['http_status_code'] = $e->getStatusCode();
            $details['error'] = $e->getResponseCode();
            $details['error_message'] = $e->getMessage();
            $details['error_response'] = $e->getRawObject();
        }

        $request->setModel($details);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request) {
        return
                $request instanceof CreateTransaction &&
                $request->getModel() instanceof \ArrayAccess
        ;
    }

}
