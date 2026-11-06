/*!
 * Compiled files.
 *
 * @handle hametuha-module-compiled-files
 * @deps jquery, wp-api-fetch, jquery-effects-highlight, moment
 */

const $ = jQuery;

//
// Delete files.
//
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

//
// Download files.
//
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

$( document ).on( 'click', '.compiled-file-validate-btn', function( e ) {
  e.preventDefault();
  const fileId = $( this ).attr( 'data-file-id' );
  const $cell = $( this ).parents( 'td' );
  $cell.addClass( 'loading' );
  wp.apiFetch( {
    path: `/hametuha/v1/epub/file/${fileId}?format=report`,
  } ).then( res => {
    alert( res.message );
    window.console && console.log( res );
  } ).catch( res => {
    let messages = [ '【バリデーション失敗】' ];
    messages.push( res.message );
    if ( res.additional_errors ) {
      for ( let error of res.additional_errors ) {
        messages.push( error.message );
      }
    }
    alert( messages.join( "\n" ) );
    window.console && console.log( res );
  } ).finally( res => {
    $cell.removeClass( 'loading' );
  } );
} );

$( document ).on( 'click', '.compiled-file-published-btn', function( e ) {
  e.preventDefault();
  let published = $( this ).attr( 'data-published' );
  let fileId = $( this ).attr( 'data-file-id' );
  const $cell = $( this ).parents( 'td' );
  const response = window.prompt( "公開日時を入力してください。\n削除する場合は大文字で DELETE と入力してください。", published || moment().format( 'YYYY-MM-DD HH:mm:ss' ) );
  if ( ! response ) {
    return true;
  }
  // Now published date is got.
  $cell.addClass( 'loading' );
  wp.apiFetch( {
    path: `/hametuha/v1/epub/file/${fileId}`,
    method: 'POST',
    data: {
      published: response
    }
  } ).then( res => {
    let text = '';
    if ( 'DELETE' === response ) {
      text = '---';
    } else {
      text = response;
    }
    $cell.removeClass( 'loading' ).parents( 'tr' ).effect( 'highlight' ).find( '.compile-file-published' ).text( text );
  } ).catch( res => {
    alert( res.message || '更新に失敗しました。' );
    $cell.removeClass( 'loading' );
  } );
} );
