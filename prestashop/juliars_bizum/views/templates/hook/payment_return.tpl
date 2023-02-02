

{if $status == 'ok'}
	<p>{l s='Tu pedido en %s está completado.' sprintf=[$shop_name]  mod='juliars_bizum'}
		<br /><br />
		{l s='Por favor, realiza el pago con Bizum, éstos son los detalles para realizarlo:'  mod='juliars_bizum'}
		<br /><br />- {l s='Cantidad a pagar:'  mod='juliars_bizum'} <span class="price"><strong>{$total_to_pay}</strong></span>
		<br /><br />- {l s='Realizar el pago a:'  mod='juliars_bizum'} <strong>{if $checkName}{$checkName}{else}___________{/if}</strong>
		<br /><br />- {l s='Número de móvil al que hacer el Bizum:'  mod='juliars_bizum'} <strong>{if $checkMovile}{$checkMovile}{else}___________{/if}</strong>
		{if !isset($reference)}
			<br /><br />- {l s='No olvides poner en el concepto tu número de pedido: #%d.' sprintf=[$id_order]  mod='juliars_bizum'}
		{else}
			<br /><br />- {l s='No olvides poner en el concepto tu referencia de pedido: %s.' sprintf=[$reference]  mod='juliars_bizum'}
		{/if}
		<br /><br />{l s='Hemos enviado un email con toda la información.'  mod='juliars_bizum'}
		<br /><br /><strong>{l s='Por favor, no olvides incluir la referencia de tu pedido al hacer el pago con Bizum'  mod='juliars_bizum'}</strong>
		<br /><br />{l s='Para cualquier duda, por favor, contactanos'  mod='juliars_bizum'} <a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='Servicio de atención al cliente'  mod='juliars_bizum'}</a>.
	</p>
{else}
	<p class="warning">
		{l s='We have noticed that there is a problem with your order. If you think this is an error, you can contact our'  mod='juliars_bizum'}
		<a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='customer service department.'  mod='juliars_bizum'}</a>.
	</p>
{/if}
