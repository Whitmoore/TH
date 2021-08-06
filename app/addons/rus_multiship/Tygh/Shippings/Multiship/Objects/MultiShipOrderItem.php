<?php

namespace Tygh\Shippings\Multiship\Objects;

// Объект - Товарная позиция
class MultiShipOrderItem extends MultiShipObject
{
  public $_prefix = "orderitem_";
  public $_fields = array("article", "name", "quantity", "cost", "weight", "width", "height", "length", "id");
  public $_critical = array("name", "quantity", "cost");
}
