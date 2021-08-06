<?php

namespace Tygh\Shippings\Multiship\Objects;

// Объект - Получатель
class MultiShipRecipient extends MultiShipObject
{
  public $_prefix = "recipient_";
  public $_fields = array("first_name", "middle_name", "last_name", "phone", "email", "comment", "time_from", "time_to");
  public $_critical = array("first_name", "last_name", "phone");

  public function fixField($name, $value)
  {
    switch ($name) {
      case "phone":
      {
        if (is_array($value)) {
          foreach ($value as $key => $phone) {
            $value[$key] = preg_replace("/[^0-9]/", '', $phone);
          }
        } else {
          $value = preg_replace("/[^0-9]/", '', $value);
        }
      }
    }

    return parent::fixField($name, $value);
  }

  /**
   * @param Multiship_Order|null $order
   * @return bool
   */
  public function validate($order = null)
  {
    if (isset($order->user_status_id) && $order->user_status_id == ORDER_DRAFT_STATUS) {
      $this->_critical = array();
    } else {
      $this->_critical = array("first_name", "last_name", "phone");
    }

    return parent::validate();
  }
}
