{assign var="obj_id" value=$player_data.player_id}
{assign var="obj_id_prefix" value="`$obj_prefix``$obj_id`"}
<div class="ty-player-details">
    <div class="ty-player-detail_wrapper">
        <div itemscope itemtype="http://schema.org/Person" class="ty-player-detail">
            <div class="ty-player-block__img-wrapper">
                {hook name="products:image_wrap"}
                    {if !$no_images}
                        <div class="ty-product-block__img cm-reload-{$product.product_id}" id="product_images_{$product.product_id}_update">

                            {include file="common/image.tpl" obj_id=$obj_id_prefix images=$player_data.main_pair.detailed itemprop="image"}
                        <!--product_images_{$product.product_id}_update--></div>
                    {/if}
                {/hook}
            </div>
            <h1 class="ty-player-details-title">
                {if $player_data.ranking}
                    {$font_size = 50 - ($player_data.ranking|strlen - 1) * 4}
                    <span class="ty-player-ranking">
                        <span class="ty-player-ranking-hashtag">#</span>
                        <span class="ty-player-ranking-num" style="font-size: {$font_size}px;">{$player_data.ranking}</span>
                    </span>
                {/if}
                <span itemprop="name" class="ty-player-name">{$player_data.player}</span>
                <meta itemprop="jobTitle" content="{__(tennis_player)}" />
                <meta itemprop="gender" content="{if $player_data.gender == 'M'}{__('male')}{else}{__('female')}{/if}" />
                
                {if $player_data.titles}<span itemprop="award" class="ty-player-titles">{$player_data.titles}</span>{/if}
            </h1>
            <div class="ty-player-block__left">
                <div class="ty-player-data">
                    <span class="ty-player-data__label">{__("date_of_birth")}:</span>
                    <div itemprop="birthDate" content="{$player_data.birthday|date_format:"%Y-%m-%d"}" class="ty-player-data__value">{$player_data.birthday|date_format:"`$settings.Appearance.date_format`"} ({$player_data.birthday|fn_get_age|fn_show_age})</div>
                </div>
                <div class="ty-player-data">
                    <span class="ty-player-data__label">{__("birthplace")}:</span>
                    <div itemprop="birthPlace" class="ty-player-data__value">{$player_data.birthplace}</div>
                </div>
                <div class="ty-player-data">
                    <span class="ty-player-data__label">{__("residence")}:</span>
                    <div itemprop="address" class="ty-player-data__value">{$player_data.residence}</div>
                </div>
                <div class="ty-player-data">
                    <span class="ty-player-data__label">{__("player_height")}:</span>
                    <div itemprop="height" itemscope itemtype="http://schema.org/Distance" class="ty-player-data__value">{$player_data.height} {__("cm")}</div>
                </div>
                <div class="ty-player-data">
                    <span class="ty-player-data__label">{__("weight")}:</span>
                    <div itemprop="weight" class="ty-player-data__value">{$player_data.weight} {__("kg")}</div>
                </div>
                <div class="ty-player-data">
                    <span class="ty-player-data__label">{__("player_plays")}:</span>
                    <div class="ty-player-data__value">{if $player_data.plays == 'R'}{__("righty")}{else}{__("lefty")}{/if}</div>
                </div>
                <div class="ty-player-data">
                    <span class="ty-player-data__label">{__("turned_pro")}:</span>
                    <div class="ty-player-data__value">{$player_data.turned_pro}</div>
                </div>
                {if $player_data.coach}
                <div class="ty-player-data">
                    <span class="ty-player-data__label">{__("coach")}:</span>
                    <div class="ty-player-data__value">{$player_data.coach}</div>
                </div>
                {/if}
                {if $player_data.data.career_won && $player_data.data.career_lost}
                <div class="ty-player-data">
                    <span class="ty-player-data__label">{__("won_lost")}:</span>
                    <div class="ty-player-data__value">{$player_data.data.career_won} - {$player_data.data.career_lost}</div>
                </div>
                {/if}
                {if $player_data.data.career_prize}
                <div class="ty-player-data">
                    <span class="ty-player-data__label">{__("career_prize")}:</span>
                    {$usd_currency = $currencies.USD}
                    {$usd_currency.coefficient = 1}
                    {$usd_currency.decimals = 0}
                    <div class="ty-player-data__value">{$player_data.data.career_prize|format_price:$usd_currency:$span_id:$class:true:$live_editor_name nofilter}</div>
                </div>
                {/if}
                {if $player_data.website}
                <div class="ty-player-data">
                    <span class="ty-player-data__label">{__("website")}:</span>
                    <div class="ty-player-data__value"><a href="{$player_data.website}" target="_blank">{$player_data.website}</a></div>
                </div>
                {/if}
            </div>
        </div>
    </div>
</div>
[-player_racket-]
[-player_bags_accessories-]
[-player_apparel_shoes-]
{if $player_data.description}
    <div class="ty-player-description">{$player_data.description nofilter}</div>
{/if}
