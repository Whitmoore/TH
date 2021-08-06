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

class Cmp2 extends Competitor
{
    public function __construct()
    {
        parent::__construct(SALETENNIS_COMPETITOR_ID);
        $this->new_links = array(
            'https://www.saletennis.com/catalog/product/tiennisnaia-rakietka-babolat-aero-gamer-yellow-black-10400/' => true,
        );
    }

    protected function prsProduct($content)
    {
        $product = array();
        if (preg_match('/<section class="card">(.*?)<\/section>/', preg_replace('/[\r\n\t]/', '', $content), $section)) {
            if (preg_match('/<h1 class="card__title">(.*?)<\/h1>/', $section[1], $match)) {
                $product['name'] = $match[1];
            }
            if (preg_match('/<p class="card__code">(.*?)<\/p>/', $section[1], $match)) {
                $product['code'] = trim(preg_replace('/Артикул/', '', $match[1]));

                if (preg_match('/(.*)U[0-9]?$/', $product['code'], $code)) {
                    $product['code'] = $code[1];
                } elseif (strpos($product['code'], '-') !== false) {
                    $code = explode('-', $product['code']);
                    if (!empty($this->codes[$code[0]])) {
                        $product['code'] = $code[0];
                    }
                } elseif (strpos($product['code'], '_') !== false) {
                    $code = explode('_', $product['code']);
                    if (!empty($this->codes[$code[0]])) {
                        $product['code'] = $code[0];
                    }
                }
                
            } else {
                $product['code'] = '';
            }

            if (preg_match('/<p class="card__price">(.*?)<\/p>/', $section[1], $match)) {
                $product['price'] = (int)$match[1];
            }
            if (preg_match('/<span class="card__button-cart-label">(.*?)<\/span>/', $section[1], $match)) {
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
