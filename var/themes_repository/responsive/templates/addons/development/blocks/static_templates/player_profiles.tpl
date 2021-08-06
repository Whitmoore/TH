<div class="ty-block-categories-wrapper cm-banner">
    <picture>
        <source srcset="{$images_dir}/addons/development/player_profiles.jpg">
        <img src="{$images_dir}/addons/development/player_profiles.jpg" alt="Homepage banner">
    </picture>
    <div class="ty-block-categories__overlay"></div>
    <div class="ty-block-categories ty-block-categories-bottom-left">
        <a href="{"players.list"|fn_url}" class="cm-banner-link"><div class="ty-block-categories__item ty-block-categories__title">{__("player_profiles")}</div></a>
        {$players = ""|fn_get_block_players}
        <div class="ty-block-categories__players">
            {foreach from=$players.male item="player"}
                <div class="ty-block-categories__item"><a href="{"players.view?player_id=`$player.player_id`"|fn_url}"> - {$player.player}</a></div>
            {/foreach}
        </div>
        <div class="ty-block-categories__players">
            {foreach from=$players.female item="player"}
                <div class="ty-block-categories__item"><a href="{"players.view?player_id=`$player.player_id`"|fn_url}"> - {$player.player}</a></div>
            {/foreach}
        </div>
        <div class="ty-block-categories__item"><a href="{"players.list"|fn_url}"> - {__("check_all_players")}</a></div>
    </div>
</div>