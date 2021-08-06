{foreach from=$tree item="player" key="key"}
    {math equation="x*10" x=$player.level assign="shift"}
    <li>
        <a href="{"players.view?player_id=`$player.player_id`"|fn_url}">{$player.player}</a>
    </li>
{/foreach}