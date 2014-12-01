<div>
    <h3 class="idTabHrefShort page-product-heading">
        {l s='Similar Products' mod='blocksimilarbytags'}
    </h3>

    <div class="rte">
        <div id="products_by_tags_block_tab" class="container-fluid">
            <div class="row">
                {foreach from=$tag_products item=product}
                    <div class="col-xs-6 col-md-2 text-center">
                        <a href="{$product['url']}">
                            <img src="{$product['img']}"/>
                        </a>
                    </div>
                {/foreach}
            </div>
        </div>
    </div>
</div>
