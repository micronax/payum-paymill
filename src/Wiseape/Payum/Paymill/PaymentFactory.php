<?php

/**
 * @copyright wiseape GmbH
 * @author Ruben Rögels
 * @license LGPL-3.0+
 */

namespace Wiseape\Payum\Paymill;

use Buzz\Client\ClientInterface;
use Payum\Core\Action\CaptureOrderAction;
use Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction;
use Payum\Core\Action\GetHttpRequestAction;
use Payum\Core\Payment;
use Payum\Core\Extension\EndlessCycleDetectorExtension;
use Payum\Core\PaymentInterface;
use Wiseape\Payum\Paymill\Action\Api\CreateTransactionAction;
use Wiseape\Payum\Paymill\Action\CaptureAction;
use Wiseape\Payum\Paymill\Action\FillOrderDetailsAction;
use Wiseape\Payum\Paymill\Action\StatusAction;

abstract class PaymentFactory {

    /**
     * @param Keys $keys
     * @param ClientInterface $client
     *
     * @return PaymentInterface
     */
    public static function create(Keys $keys, ClientInterface $client = null) {

        $payment = new Payment;

        $payment->addApi($keys);

        $payment->addExtension(new EndlessCycleDetectorExtension);

        $payment->addAction(new CaptureAction);
        $payment->addAction(new CaptureOrderAction);
        $payment->addAction(new FillOrderDetailsAction);
        $payment->addAction(new StatusAction);
        $payment->addAction(new GetHttpRequestAction);

        $action = new CreateTransactionAction;
        $action->setClient($client);
        $payment->addAction($action);

        $payment->addAction(new ExecuteSameRequestWithModelDetailsAction);

        return $payment;
    }

    /**
     */
    private function __construct() {
        
    }

}
