import { BitrixVue } from 'ui.vue';
import 'sale.checkout.view.element.button'

import './props-list'

BitrixVue.component('sale-checkout-view-product-item_deleted', {
	props: ['item', 'index'],
	methods:
		{
			getSrc()
			{
				return encodeURI(this.item.product.picture)
			}
		},
	computed:
		{
			localize() {
				return Object.freeze(
					BitrixVue.getFilteredPhrases('CHECKOUT_VIEW_PRODUCT_INFO_DELETED_'))
			},
		},
	// language=Vue
	template: `
      <div class="checkout-basket-item-container">
        <div class="checkout-basket-item-inner">
          <a href="" class="checkout-basket-item-image-block">
            <img :src="getSrc()" :alt="item.name" class="checkout-basket-item-image">
          </a>
          <div class="checkout-basket-item-info-container">
            <h2 class="checkout-basket-item-name-block">
				<span class="checkout-basket-item-name-text"><strong>{{localize.CHECKOUT_VIEW_PRODUCT_INFO_DELETED_WAS_DELETED}}</strong> {{item.name}}</span>
            </h2>
          </div>
          <sale-checkout-view-element-button-restore :index="index"/>
        </div>
      </div>
	`
});