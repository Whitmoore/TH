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

namespace Tygh\Providers;

use Pimple\Container;
use Tygh\Application;
use Tygh\Registry;
use Tygh\Web\Antibot;
use Tygh\Web\Antibot\NullDriver;

/**
 * The provider class that registers trivial generic components.
 *
 * @package Tygh\Providers
 */
class CommonProvider implements \Pimple\ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register(Container $app)
    {
        $app['antibot'] = function (Application $app) {
            $antibot = new Antibot($app['session'], Registry::get('settings.Image_verification'));

            $antibot->setDriver($app['antibot.default_driver']);

            if (Registry::get('config.tweaks.disable_captcha')) {
                $antibot->disable();
            } else {
                $antibot->enable();
            }

            return $antibot;
        };

        $app['antibot.default_driver'] = function(Application $app) {
            return new NullDriver();
        };
    }
}
