<?php

namespace Tygh\Shippings\Multiship\Objects;

/* Объект - Параметры заказа
  @PARAMS:
    (Integer) id ID заказа в системе MultiShip, null для нового заказа
    (String) num Номер заказа в учётной системе магазина
    (String) date Дата заказа в учётной системе магазина
    (Float n,2) weight Вес заказа
    (Float n,2) height Габариты заказа
    (Float n,2) width Габариты заказа
    (Float n,2) length Габариты заказа
    (Integer) payment_method ID пособа оплаты (см. getPaymentMethods())
    (Float n,2) cost Стоимость заказа
    (Float n,2) delivery_cost Стоимость доставки
    (Float n,2) assessed_value Оценочная стоимость заказа
    (Float n,2) total_cost Общая стоимость заказа, взымаемая с получателя
    (String) comment Комментарии к заказу
    (Array Of MultiShip_OrderItem) items опись товарных позиций в заказе
    (Integer) sender ID отправителя
    (Integer) requisite ID реквизитов организации отправителя
    (Integer) warehouse ID склада отправителя
    (Integer) user_status_id параметр для создания заказа со статусом "Черновик" (-2)
*/

// Объект - Заказ
class MultiShipOrder extends MultiShipObject
{
  public $_prefix = "order_";
  public $_fields = array("num", "date", "weight", "width", "height", "length", "payment_method", "delivery_cost", "assessed_value", "comment", "items", "sender", "requisite", "warehouse", "user_status_id", "total_cost");
  public $_critical = array("date", "items", "assessed_value", "delivery_cost", "weight", "width", "height", "length");

  public $_wrongItem;

  public function x__construct()
  {
    parent::__construct();
  }

  // Добавляем вложение в заказ
  public function appendItem(MultiShipOrderItem $item)
  {
    if (!is_array($this->items)) {
      $this->items = array();
    }
    $appended = $item->appendToArray($this->items[count($this->items)], true);
    if (!$appended) {
      $this->_wrongItem = $item;
    }
  }
  public function validate()
  {
    $validated = parent::validate();
    if ($this->_wrongItem) {
      if (!$this->_wrongItem->validate()) {
        $validated = false;
      }
    }

    return $validated;
  }
}
