<div class="ty-learning-center ty-block-categories-wrapper">
    <div class="ty-block-categories__overlay"></div>
    <div class="ty-block-categories-bottom-right">
        <a href="{"pages.view?page_id=`$smarty.const.LEARNING_CENTER_PAGE_ID`"|fn_url}"><div class="ty-block-categories__item ty-block-categories__title">{__("learning_material")}</div></a>
    </div>
</div>
<a href="{"pages.view?page_id=`$smarty.const.LEARNING_CENTER_PAGE_ID`"|fn_url}"  class="ty-learning-center-link"></a>
<script type="text/javascript">
    Tygh.$(document).ready(function() {$ldelim}
        $('.ty-learning-center').click(function(){$ldelim}
            $('.ty-learning-center-link').click();
        {$rdelim});
    {$rdelim});
</script>