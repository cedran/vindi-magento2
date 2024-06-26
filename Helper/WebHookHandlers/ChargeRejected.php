<?php

namespace Vindi\Payment\Helper\WebHookHandlers;

use Vindi\Payment\Model\Payment\Bill;

class ChargeRejected
{
    private $bill, $order, $logger;

    public function __construct(
        Bill $bill,
        Order $order,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->bill = $bill;
        $this->order = $order;
        $this->logger = $logger;
    }

    /**
     * @param array $data
     *
     * @return bool
     * @throws \Exception
     */
    public function chargeRejected($data)
    {
        $charge = $data['charge'];

        if (!($order = $this->getOrderFromBill($charge['bill']['id']))) {
            $this->logger->warning(__('Order not found'));

            return false;
        }

        $gatewayMessage = $charge['last_transaction']['gateway_message'];
        $isLastAttempt = $charge['next_attempt'] === null;
        $statusIsNotPending = $charge['status'] != 'pending';

        if ($isLastAttempt && $statusIsNotPending) {
            $order->addStatusHistoryComment(__(sprintf(
                'Payment rejected. Motive: "%s"',
                $gatewayMessage
            )));
            $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED, true, __(sprintf(
                'All payment tries were rejected. Motive: "%s".',
                $gatewayMessage
            )), true);
            $order->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED);
            $this->logger->info(
                __('All payment tries were rejected. Motive: "%1".', $gatewayMessage)
            );
        } else {
            $order->addStatusHistoryComment(
                __('Payment try rejected. Motive: "%s". A new try will be made', $gatewayMessage)
            );
            $this->logger->info(__('Payment try rejected. Motive: "%1". A new try will be made', $gatewayMessage));
        }

        $order->save();

        return true;
    }

    private function getOrderFromBill($billId)
    {
        $bill = $this->bill->getBill($billId);

        if (!$bill) {
            return false;
        }

        return $this->order->getOrder(compact('bill'));
    }
}
