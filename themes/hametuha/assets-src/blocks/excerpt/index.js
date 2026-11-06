/*!
 * リード文ブロック
 *
 * 投稿の抜粋（excerpt）をブロックとして編集する。中身は投稿の excerpt に保存され、
 * ブロック自体は何も出力しない（save は null）。ニュースのテンプレートで使っている（hooks/news.php）。
 */

import { registerBlockType } from '@wordpress/blocks';
import { RichText } from '@wordpress/block-editor';
import { useEntityProp } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { __, sprintf } from '@wordpress/i18n';
import metadata from './block.json';
import './editor.scss';

registerBlockType( metadata.name, {
	edit( { attributes, className } ) {
		const post = useSelect(
			( select ) => select( 'core/editor' ).getCurrentPost(),
			[]
		);
		const [ excerpt, setExcerpt ] = useEntityProp(
			'postType',
			post.type,
			'excerpt',
			post.id
		);
		const { max, min } = attributes;
		let { placeholder } = attributes;
		if ( 'news' === post.type ) {
			placeholder = __(
				'ニュースの内容を要約するリード文を書いてください。',
				'hametuha'
			);
		}
		const length = ( excerpt || '' ).length;
		const ok = length < max && length > min;
		const helperClass = [ 'hametuha-block-lead-status', ok ? 'ok' : 'ng' ];
		// translators: %1$d: current length, %2$d: min, %3$d: max.
		const helperStatusText = sprintf(
			__( '現在%1$d文字: %2$d〜%3$d文字', 'hametuha' ),
			length,
			min,
			max
		);
		return (
			<div className={ className + '-container' }>
				<RichText
					className="hametuha-block-lead-text"
					tagName="p"
					allowedFormats={ [] }
					aria-label={ __( 'リード文', 'hametuha' ) }
					placeholder={ placeholder }
					value={ excerpt }
					onChange={ setExcerpt }
				/>
				<span className={ helperClass.join( ' ' ) }>
					<span
						className={
							ok
								? 'dashicons dashicons-yes'
								: 'dashicons dashicons-warning'
						}
					></span>
					{ helperStatusText }
				</span>
			</div>
		);
	},

	save() {
		return null;
	},
} );
