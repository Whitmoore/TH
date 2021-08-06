<div class="ty-racket-finder" id="rf_steps">
    {$width = 100 / $schema|count}
    <div class="ty-rf-navigation">
        {foreach from=$schema item="q_data" key="q_name"}
            <div style="width:{$width}%;" class="ty-rf-navigation-item">
                <div class="ty-rf-navigation--wrapper {if $step == $q_name}ty-rf-navigation-item-active{/if} {if !$racket_finder.$q_name && $step != $q_name}ty-rf-navigation-item-hidden{/if}">{*$racket_finder.$q_name*}
                <div class="ty-rf-navigation-wrapper" {if $racket_finder.$q_name}onclick="fn_submit_answer('{$step}', 'same', '{$q_name}');"{/if}>
                    <div class="ty-rf-navigation-{$q_name}"></div>
                </div>
                </div>
            </div>
        {/foreach}
    </div>
    <form action="{""|fn_url}" method="post" class="cm-ajax" name="racket_finder_form">
    <input type="hidden" name="result_ids" value="rf_steps">
    <input type="hidden" name="dispatch" value="racket_finder.submit">
    <input type="hidden" name="step" id="step" value="{$step}">
    <input type="hidden" name="direction" id="direction" value="F">
    {foreach from=$schema item="q_data" key="q_name" name="rf_questions"}
        <div class="ty-racket-finder_question {if $step != $q_name}hidden{/if}" id="rf_question_{$q_name}">
            {if !$smarty.foreach.rf_questions.first}<div class="ty-rf-arrow-left" onclick="fn_submit_answer('{$q_name}', 'same', 'B');"></div>{/if}
            <div class="ty-rf_question">
                <div class="ty-rf_question-title">{$q_data.title}</div>
                <div class="ty-rf_question-body">
                    {if $q_data.type == 'input'}
                        <input type="text" name="racket_finder[{$q_name}]" id="rf_question_{$q_name}_value" maxlength="{$q_data.options.max_length}" value="{$racket_finder.$q_name}" class="ty-rf_input-answer cm-numeric" data-m-dec="" data-v-max="{$q_data.options.mask}" data-v-min="0" placeholder="{$q_data.options.placeholder}"/><span class="ty-rf_question-ok-button" onclick="fn_submit_answer('{$q_name}', 'same', 'F');">{__("rf_next")}</span>
                    {elseif $q_data.type == 'select'}
                        <input type="hidden" name="racket_finder[{$q_name}]" id="rf_question_{$q_name}_value" value="{$racket_finder.$q_name}" />
                        {foreach from=$q_data.variants item="var_text" key="var_key"}
                            <div class="ty-rf_question-body-answer {if $racket_finder.$q_name == $var_key}ty-rf_question-body-answer-selected{/if}" onclick="fn_submit_answer('{$q_name}', '{$var_key}', 'F');">
                                <div class="ty-rf_question-body-answer-title">{$var_text}</div>
                                <div class="ty-rf_question-variant ty-rf_{$q_name}-{$var_key}"></div>
                            </div>
                        {/foreach}
                    {/if}
                </div>
            </div>
            {if !$smarty.foreach.rf_questions.last}<div class="ty-rf-arrow-right" onclick="fn_submit_answer('{$q_name}', 'same', 'F');"></div>{/if}
        </div>
    {/foreach}
    <div class="ty-racket-finder_question {if $step != 'result'}hidden{/if}" id="rf_question_results">
        <div class="ty-racket-finder_results">
            {__("rf_results")}
            <div class="ty-rf-complete-again" onclick="fn_submit_answer('results', 'same', 'reset');">{__("complete_rf_again")}</div>
        </div>
        {if $results}
            {include file="blocks/list_templates/grid_list.tpl"
            products=$results
            columns=5
            form_prefix="block_manager"
            no_sorting="Y"
            no_pagination="Y"
            no_ids="Y"
            obj_prefix="racket_finder"
            item_number=false
            show_trunc_name=true
            show_old_price=true
            show_price=true
            show_rating=true
            show_clean_price=true
            show_list_discount=true
            show_add_to_cart=false
            but_role="action"
            show_discount_label=true}
        {else}
            <div class="ty-rf-no-products">{__("rf_results_no_products")}</div>
        {/if}
    </div>
    <button class="hidden" id="form_racket_finder_submit" type="submit"></button>
    </form>
<!--rf_steps--></div>
<div>{__("racket_finder_description")}</div>
<script type="text/javascript">
    {literal}
        function fn_submit_answer(step, value, dir)
        {
            if (value != 'same') {
                $('#rf_question_' + step + '_value').val(value);
            }

            $('#direction').val(dir);

            if ($('#rf_question_' + step + '_value').val() || dir != 'F') {
                $('#rf_question_' + step).fadeOut(1000);
                $('#step').val(step);
                $('#form_racket_finder_submit').delay(1000).click();
            }
        }
    {/literal}
</script>

{capture name="mainbox_title"}{__("find_tennis_racket")}{/capture}
