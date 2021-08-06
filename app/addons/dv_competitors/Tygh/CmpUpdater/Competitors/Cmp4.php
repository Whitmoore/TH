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

class Cmp4 extends Competitor
{
    public function __construct()
    {
        parent::__construct(RACKETS_COMPETITOR_ID);
        $this->new_links = array(
            // 'http://rackets.ru/rackets-shop/tennis-rackets.html' => true
        );
    }

    protected function trimLinks(&$links)
    {
        $links = array_unique($links);
        $domain = ($this->competitor['link'][-1] == '/') ? mb_substr($this->competitor['link'], 0, -1) : $this->competitor['link'];
        $parsed_home = parse_url($domain);
        $result = array();
        foreach ($links as $link) {
            $parsed_link = parse_url($link);
            if (empty($parsed_link) || empty($parsed_link['path']) || $parsed_link['path'] == '/' ||
            (!empty($parsed_link['host']) && $parsed_link['host'] != $parsed_home['host']) ||
            (!empty($parsed_link['scheme']) && !in_array($parsed_link['scheme'], array('http', 'https'))) ||
            fn_strtolower(substr($parsed_link['path'], -4, 4 )) == '.jpg' ||
            fn_strtolower(substr($parsed_link['path'], -4, 4 )) == '.png' ||
            fn_strtolower(substr($parsed_link['path'], -5, 5 )) == '.jpeg') {
                continue;
            }

            $query = '';
            if (!empty($parsed_link['query'])) {
                $pts = explode('&', $parsed_link['query']);
                foreach ($pts as $pt) {
                    if (strpos($pt, 'start=') === 0) {
                        $query = '?' . $pt;
                        break;
                    }
                }
            }

            $result[] = $domain . $parsed_link['path'] . $query;
        }

        $links = array_unique($result);
    }

    protected function prsProduct($content)
    {
        $product = array();

        if (preg_match('/<form name="product".*?>(.*?)<\/form>/', preg_replace(array('/[\r\n\t]/', '/\>\s+\</m'), array('', '><'), $content), $section)) {

            if (preg_match('/<h1 class="title">(.*?)<\/h1>/', $section[1], $match)) {
                $product['name'] = $match[1];

                $parts = explode(' ', $product['name']);
                $tmp = array_pop($parts);
                if (!empty($this->codes[$tmp])) {
                    $product['code'] = $tmp;
                } else {
                    $product['code'] = '';
                }
            }

            if (preg_match('/<div class="prod_price"><span id="block_price">(.*?)<\/span>/', $section[1], $match)) {
                $product['price'] = (int)preg_replace('/[^0-9]/', '', $match[1]);
            }

            $product['in_stock'] = 'Y';
        }

        if (!empty($product['name']) && /*!empty($product['code']) &&*/ !empty($product['price']) && !empty($product['in_stock'])) {
            $product['link'] = $this->current_link;
            return $product;
        }

        return false;
    }

}
