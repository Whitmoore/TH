<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2020 PaulDreda    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/


namespace Tygh\CmpUpdater\Competitors;

class Cmp5 extends Competitor
{
    public function __construct()
    {
        parent::__construct(TENNIS_PRO_COMPETITOR_ID);
        $this->new_links = array(
        );
    }

    protected function prsProduct($content)
    {
        $product = array();

        if (preg_match('/<div class="page-product">(.*?)<\/body>/', preg_replace(array('/[\r\n\t]/', '/\>\s+\</m'), array('', '><'), $content), $section)) {

            $is_wilson_racket = false;
            if (preg_match('/<p class="product-name">(.*?)<\/p>/', $section[1], $match)) {
                $product['name'] = $match[1];

                if (mb_stripos($product['name'], 'ракетка') !== false && mb_stripos($product['name'], 'wilson') !== false) {
                    $is_wilson_racket = true;
                }
            }

            if (preg_match('/<div class="parameter">Артикул<\/div><div class="value">(.*?)<\/div>/', $section[1], $match)) {
                $product['code'] = $match[1];

                if (!empty($is_wilson_racket)) {
                    $product['code'] = 'WR' . $product['code'];
                }

            } else {
                $product['code'] = '';
            }

            if (preg_match('/<p class="price price_product.*?">(.*?)<\/p>/', $section[1], $match)) {
                $product['price'] = preg_replace('/[^0-9]/', '', $match[1]);
            }

            if (preg_match('/<noindex><a class="buy add-to-basket"(.*?)<\/noindex>/', $section[1], $match)) {
                $product['in_stock'] = 'Y';
            } else {
                $product['in_stock'] = 'N';
            }
        }

        if (!empty($product['name']) && /*!empty($product['code']) &&*/ !empty($product['price']) && !empty($product['in_stock'])) {
            $product['link'] = $this->current_link;
            return $product;
        }

        return false;
    }

}
