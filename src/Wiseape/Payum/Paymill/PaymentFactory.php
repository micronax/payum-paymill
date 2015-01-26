<?php

/**
 * @copyright wiseape GmbH
 * @author Ruben Rögels
 * @license LGPL-3.0+
 */

namespace Wiseape\Payum\Paymill;

use Payum\Core\Action\ActionInterface;
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
     * @param ActionInterface $renderTemplateAction
     * @param string $layoutTemplate
     * @param string $obtainTokenTemplate
     *
     * @return PaymentInterface
     */
    public static function create(Keys $keys) {

        $payment = new Payment;

        $payment->addApi($keys);

        $payment->addExtension(new EndlessCycleDetectorExtension);

        $payment->addAction(new CaptureAction);
        $payment->addAction(new CaptureOrderAction);
        $payment->addAction(new FillOrderDetailsAction);
        $payment->addAction(new StatusAction);
        $payment->addAction(new GetHttpRequestAction);
        $payment->addAction(new CreateTransactionAction);
        $payment->addAction(new ExecuteSameRequestWithModelDetailsAction);

        return $payment;
    }

    /**
     */
    private function __construct() {
        
    }

}
