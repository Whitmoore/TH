<?php

namespace Tygh\Shippings\Multiship\Objects;

// Объект - параметры доставки
class MultiShipDelivery extends MultiShipObject
{
  public $_prefix = "delivery_";
  public $_fields = array("direction", "delivery", "price", "pickuppoint", "to_ms_warehouse");
  public $_critical = array("direction", "delivery", "price", "to_ms_warehouse", "pickuppoint");

  /**
   * @param MultishipOrder|null $order
   * @return bool
   */
  public function validate($order = null)
  {
    if (isset($order->user_status_id) && $order->user_status_id == ORDER_DRAFT_STATUS) {
      $this->_critical = array();
    } else {
      $this->_critical = array("direction", "delivery", "price", "to_ms_warehouse", "pickuppoint");
    }

    return parent::validate();
  }
}
