<?php

/**
 * @copyright wiseape GmbH
 * @author Ruben Rögels
 * @license LGPL-3.0+
 */

namespace Wiseape\Payum\Paymill\Action;

use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Capture;
use Wiseape\Payum\Paymill\Request\Api\CreateTransaction;

class CaptureAction extends PaymentAwareAction {

    /**
     * @param Capture $request
     */
    public function execute($request) {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = $request->getModel();
        if(!isset($details['http_status_code'])) {
            $this->payment->execute(new CreateTransaction($details));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request) {
        return
                $request instanceof Capture &&
                $request->getModel() instanceof \ArrayAccess
        ;
    }

}
