<?php

namespace Tygh\Shippings\Multiship\Objects;

// Объект - Адрес доставки
class MultiShipDeliveryPoint extends MultiShipObject
{
  public $_prefix = "deliverypoint_";
  public $_fields = array("index", "city", "street", "house", "build", "housing", "porch", "code", "floor", "flat", "station");
  public $_critical = array("city", "street", "house");

  /**
   * @param Multiship_Order|null $order
   * @return bool
   */
  public function validate($order = null)
  {
    if (isset($order->user_status_id) && $order->user_status_id == ORDER_DRAFT_STATUS) {
      $this->_critical = array();
    } else {
      $this->_critical = array("city", "street", "house");
    }

    return parent::validate();
  }

}
