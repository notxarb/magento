<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage_GoogleCheckout
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Google Checkout Event Observer
 *
 * @category   Mage
 * @package    Mage_GoogleCheckout
 */
class Mage_GoogleCheckout_Model_Observer
{
    public function salesOrderShipmentTrackSaveAfter(Varien_Event_Observer $observer)
    {
        $track = $observer->getEvent()->getTrack();

        $order = $track->getShipment()->getOrder();

        if ($order->getShippingMethod()!='googlecheckout_carrier') {
            return;
        }

        Mage::getModel('googlecheckout/api')
            ->setStoreId($order->getStoreId())
            ->deliver($order->getExtOrderId(), $track->getCarrierCode(), $track->getNumber());
    }

    public function salesOrderShipmentSaveAfter(Varien_Event_Observer $observer)
    {
        $googleShipmentNames = array('googlecheckout_carrier', 'googlecheckout_merchant', 'googlecheckout_flatrate', 'googlecheckout_pickup');

        $shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();

        if (!in_array($order->getShippingMethod(), $googleShipmentNames)) {
            return;
        }

        $items = array();

        foreach ($shipment->getAllItems() as $item) {
            $items[] = $item->getSku();
        }

        if ($items) {
            Mage::getModel('googlecheckout/api')
                ->setStoreId($order->getStoreId())
                ->shipItems($order->getExtOrderId(), $items);
        }
    }
}
