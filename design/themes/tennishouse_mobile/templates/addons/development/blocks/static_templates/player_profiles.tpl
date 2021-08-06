<div class="ty-player-profiles ty-block-categories-wrapper">
    <div class="ty-block-categories__overlay"></div>
    <div class="ty-block-categories-top-right">
        <a href="{"players.list"|fn_url}"><div class="ty-block-categories__item ty-block-categories__title">{__("player_profiles")}</div></a>
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
<a href="{"players.list"|fn_url}"  class="ty-player-profiles-link"></a>
<script type="text/javascript">
    Tygh.$(document).ready(function() {$ldelim}
        $('.ty-player-profiles').click(function(){$ldelim}
            $('.ty-player-profiles-link').click();
        {$rdelim});
    {$rdelim});
</script>