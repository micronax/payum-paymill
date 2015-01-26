<?php

/**
 * @copyright wiseape GmbH
 * @author Ruben Rögels
 * @license LGPL-3.0+
 */

namespace Wiseape\Payum\Paymill\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;

class StatusAction implements ActionInterface {

    /**
     * {@inheritDoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request) {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = $request->getModel();

        if(!isset($details['http_status_code'])) {
            $request->markNew();
            return;
        }

        // HTTP status code check, see https://developers.paymill.com/en/reference/api-reference/#document-v2/errors
        if(200 != $details['http_status_code'] || !isset($details['transaction'])) {
            $request->markFailed();
            return;
        }

        // API response code check, see https://developers.paymill.com/en/reference/api-reference/#document-v2/statuscodes
        $code = $details['transaction']['response_code'];
        if(10002 == $code) {
            $request->markPending();;
            return;
        } elseif(20000 != $code) {
            $request->markFailed();
            return;
        }

        // see Paymill\Models\Response\Transaction::getStatus() for details
        // or https://developers.paymill.com/en/reference/api-reference/#document-transactions
        switch($details['transaction']['status']) {
            case 'open':
            case 'pending':
            case 'preauthorize':
            case 'preauth':
                $request->markPending();
                break;
            case 'closed':
                $request->markCaptured();
                break;
            case 'failed':
                $request->markFailed();
                break;
            case 'refunded':
            case 'partial_refunded':
            case 'partially_refunded':
            case 'chargeback':
                $request->markRefunded();
                break;
            default:
                $request->markUnknown();
                break;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request) {
        return
                $request instanceof GetStatusInterface &&
                $request->getModel() instanceof \ArrayAccess
        ;
    }

}
