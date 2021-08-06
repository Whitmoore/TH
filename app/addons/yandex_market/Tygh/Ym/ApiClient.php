<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/

namespace Tygh\Ym;

use Tygh\Registry;
use Tygh\RestClient;
use Tygh\Settings;

class ApiClient
{

    protected $options;
    protected $campaign_id;
    protected $client;

    public function __construct()
    {
        $this->options = Registry::get('addons.yandex_market');

        // OAuth
        $auth = sprintf('OAuth oauth_token="%s", oauth_client_id="%s", oauth_login="%s"',
            $this->options['ym_auth_token'],
            $this->options['ym_application_id'],
            $this->options['user_login']
        );

        $headers = array(
            'Authorization: ' . $auth
        );

        // Client
        $this->client = new RestClient($this->options['ym_api_url'], null, null, null, $headers, 'json');

        // Campaign_id
        $this->campaign_id = $this->options['campaign_id'];
        if ($pos = strpos($this->campaign_id, '-')) {
            $this->campaign_id = substr($this->campaign_id, $pos + 1);
        }
    }

    public function orderStatusUpdate($ym_order_id, $data)
    {
        $path = 'campaigns/' . $this->campaign_id . '/orders/' . $ym_order_id . '/status.json';
        $data = array(
            'order' => $data
        );

        $res = $this->client->put($path, $data);

        return $res;
    }

    public function auth($code)
    {
        $client = new RestClient(
            'https://oauth.yandex.ru/',
            Registry::get('addons.yandex_market.ym_application_id'),
            Registry::get('addons.yandex_market.ym_application_password'),
            'basic',
            array(),
            ''
        );
        $res = $client->post('token', array(
            'grant_type' => 'authorization_code',
            'code' => $code,
        ));
        $result = json_decode($res, true);
        if (!empty($result['access_token'])) {
            Settings::instance()->updateValue('ym_auth_token', $result['access_token'], 'yandex_market');
        }
    }

    public function test()
    {
        return $this->client->get('campaigns/' . $this->campaign_id . '/region');
    }

}
