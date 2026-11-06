/*!
 * コンテナブロック
 *
 * 以前は @handle のコピーミス（hametuha-block-container が excerpt.js を指していた）で
 * 読み込まれていなかった。block.json 化で直った（#361）。
 */

import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks } from '@wordpress/block-editor';
import metadata from './block.json';

registerBlockType( metadata.name, {
	edit( { className } ) {
		return (
			<div
				className={ [ className, 'container' ]
					.filter( Boolean )
					.join( ' ' ) }
			>
				<InnerBlocks templateLock={ false } />
			</div>
		);
	},

	// className は save に渡されない（ブロックのクラス名は WordPress が自動で付ける）。
	save() {
		return (
			<div className="container">
				<InnerBlocks.Content />
			</div>
		);
	},
} );
