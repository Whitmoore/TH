{if $content|trim}
    <div class="ty-mainbox-container clearfix{if isset($hide_wrapper)} cm-hidden-wrapper{/if}{if $hide_wrapper} hidden{/if}{if $details_page} details-page{/if}{if $block.user_class} {$block.user_class}{/if}{if $content_alignment == "RIGHT"} ty-float-right{elseif $content_alignment == "LEFT"} ty-float-left{/if}">
        {if $title || $smarty.capture.title|trim}
            <h1 class="ty-mainbox-title">
                {hook name="wrapper:mainbox_general_title"}
                {if $smarty.capture.title|trim}
                    {$smarty.capture.title nofilter}
                {else}
                    {$title nofilter}
                {/if}
                {/hook}
            </h1>
        {/if}
        <div class="ty-mainbox-body">{$content nofilter}</div>
        {if $show_racket_finder}
            <a href="{"racket_finder.view"|fn_url}"><div class="ty-racket-finder-block">
                <div class="ty-sticky" data-min-sticky-width="1400">
                {*<div class="ty-rf-slide-button ty-rf-slide-button-in {if !$smarty.session.hide_rf_add}hidden{/if}" id="rf_slide_in" onclick="fn_rf_slide_in();return false;"><div class="ty-rf-slide-icon"></div></div>
                <div class="ty-rf-slide-button ty-rf-slide-button-out {if $smarty.session.hide_rf_add}hidden{/if}" id="rf_slide_out" onclick="fn_rf_slide_out();return false;"><div class="ty-rf-slide-icon"></div></div>*}
                <div class="ty-racket-finder-block-content {*if $smarty.session.hide_rf_add}hidden{/if*}" id="rf_block">
                    <div class="ty-racket-finder-block-bg">
                        <div class="ty-racket-finder-block-text-vertical">{__("test")}</div>
                    </div>
                    <div class="ty-racket-finder-block-text-bottom">
                        {__("find_tennis_racket_add")}
                    </div>
                </div>
                </div>
            </div></a>
        {/if}
        {if $smarty.const.HTTPS}
            <div class="ty-hover-ssl-cert">{include file="addons/development/blocks/static_templates/ssl_seal.tpl" show_tooltip=true}</div>
        {/if}
    </div>
{/if}