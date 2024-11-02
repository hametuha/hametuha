/*!
 * 公募への参加をコントロールする
 *
 * @handle hametuha-campaign-participants
 * @deps wp-api-fetch, hametuha-common, wp-i18n
 */

const { apiFetch } = wp;
const { __ } = wp.i18n;

document.addEventListener( 'DOMContentLoaded', function(){
	document.getElementById( 'campaign-support-action' ).addEventListener( 'click', function( event ) {
		event.preventDefault();
		event.stopPropagation();
		const button = event.target;
		button.disabled = true;
		Hametuha.confirm( __( 'サポーターは非公開状態の作品を閲覧できるなど、非常に大きな権限を持っています。非公開作品の秘密保持などを守っていただく義務が生じます。よろしいですか？', 'hametuha' ), () => {
			apiFetch( {
				path: `hametuha/v1/campaign/support/${ button.dataset.action }`,
				method: 'POST',
			} ).then( ( res ) => {
				Hametuha.alert( __( 'サポーターとして登録しました。', 'hametuha' ) );
				location.href = res.url;
			} ).catch( ( res ) => {
				Hametuha.alert( res.message, 'danger' );
				button.disabled = false;
			} );
		} );
	} );
} );
