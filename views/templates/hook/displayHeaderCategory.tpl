{if $customer.is_logged == true}
<section id="products">
  <h1>{l s='Best Sellers' d='Modules.Bestsellers.Shop'}</h1>
  <div class="products row" id="productslistfilterbestsellers">
    {foreach from=$products item="product"}
    <div class="js-product product col-xs-12 col-sm-6 col-xl-4">
      {include file="catalog/_partials/miniatures/product.tpl" product=$product}
    </div>
    {/foreach}
  </div>
  <a href="{$allBestSellers}">{l s='All best sellers' d='Modules.Bestsellers.Shop'}</a>
</section>
{/if}
