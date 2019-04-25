/*!
 * wpdeps=jquery, wp-api-fetch, jquery-effects-highlight
 */

const $ = jQuery;


$( document ).on( 'click', '.compiled-file-delete-btn', function( e ) {
  e.preventDefault();
  if ( window.confirm( '本当に削除してよろしいですか？　この操作は取り消せません。' ) ) {
    let id = $( this ).attr( 'data-file-id' );
    const $cell = $( this ).parents( 'td' );
    $cell.addClass( 'loading' );
    wp.apiFetch( {
      path: `/hametuha/v1/epub/file/${id}`,
      method: 'DELETE'
    } ).then( res => {
      $cell.removeClass( 'loading' );
      $cell.parents( 'tr' ).effect( 'highlight', {}, 500, function() {
        $( this ).fadeOut( 300, function() {
          $( this ).remove();
        } );
      } );
    } ).catch( res => {
      $cell.removeClass( 'loading' );
      alert( res.message || 'エラーが発生しました。' );
    } );
  }
} );

$( document ).ready( () => {

  const $iframe = $( 'iframe[name=file-downloader]' );
  $iframe.load( function( e ) {
    if (e.target.contentDocument && e.target.contentDocument.body && e.target.contentDocument.body.innerText ) {
      let response = JSON.parse( e.target.contentDocument.body.innerText );
      if ( response.message ) {
        alert( response.message );
      }
    }
  } );

} );
