<?php
/* @var $lwp Literally_WordPress */
global $lwp;
$footer_note = $lwp->event->get_footer_note(get_the_ID());
?>
<?php if(lwp_has_ticket()): ?>
<div class="hametu-ticket-wrap">

	<table class="lwp-ticket-list">
		<?php if (lwp_is_event_available()): ?>
			<caption><?php echo lwp_selling_limit('Y年n月j日（D）'); ?>まで！</caption>
		<?php else: ?>
			<caption class="outdated">申込期限は過ぎました</caption>
		<?php endif; ?>
		<thead>
			<tr>
				<th>&nbsp;</th>
				<th>チケット名</th>
				<th class="price">金額</th>
				<th class="stock">販売枚数</th>
			</tr>
		</thead>
		<tbody>
			<?php lwp_list_tickets('wrap=&callback=_hametuha_lwp_ticket_table'); ?>
		</tbody>
	</table><!-- //.lwp-ticket-list -->
	
	

	<?php if ( !empty($footer_note) ): ?>
		<div class="ticket-notes">
			<?php echo $footer_note ?>
			<p class="refund-condition">
				<?php if(lwp_is_cancelable()): ?>
					<span>このチケットはキャンセル可能です。</span>
				<?php else: ?>
					<span>このチケットはキャンセルできません。</span>
				<?php endif; ?>
			</p>
		</div>
	<?php endif; ?>
	</div><!-- //.hametu-ticket-wrap -->


<?php endif; 
