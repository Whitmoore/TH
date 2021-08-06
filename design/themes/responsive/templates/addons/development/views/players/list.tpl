{$columns = "3"}
<div class="ty-players-list">
    {split data=$atp_players size=$columns|default:"2" assign="splitted_atp_players"}
    <div class="ty-column2">
        {strip}
        <h1 class="ty-mainbox-title">{__("atp")}</h1>
        <div class="ty-atp-players-block">
        {foreach from=$splitted_atp_players item="saplayers"}
            <div class="ty-players-row">
            {foreach from=$saplayers item="atp_player"}
                {if $atp_player}
                    <a href="{"players.view?player_id=`$atp_player.player_id`"|fn_url}" class="ty-subcategories-block__a">
                        <div class="ty-players-list_column{$columns} ty-players-list__item">
                            {include file="common/image.tpl" obj_id=$obj_id_prefix images=$atp_player.main_pair image_width="130" image_height="130"}
                            <div class="ty-player-name">{$atp_player.player}</div>
                        </div>
                    </a>
                {/if}
            {/foreach}
            </div>
        {/foreach}
        </div>
        {/strip}
    </div>
    {split data=$wta_players size=$columns|default:"2" assign="splitted_wta_players"}
    <div class="ty-column2">
        {strip}
        <h1 class="ty-mainbox-title">{__("wta")}</h1>
        <div class="ty-wta-players-block">
        {foreach from=$splitted_wta_players item="swplayer"}
            <div class="ty-players-row">
            {foreach from=$swplayer item="wta_player"}
                {if $wta_player}
                    <a href="{"players.view?player_id=`$wta_player.player_id`"|fn_url}" class="ty-subcategories-block__a">
                        <div class="ty-players-list_column{$columns} ty-players-list__item">
                            {include file="common/image.tpl" obj_id=$obj_id_prefix images=$wta_player.main_pair image_width="130" image_height="130"}
                            <div class="ty-player-name">{$wta_player.player}</div>
                        </div>
                    </a>
                {/if}
            {/foreach}
            </div>
        {/foreach}
        </div>
        {/strip}
    </div>
</div>