/*!
 * セクションブロック
 *
 * save() の出力を変えると既存の固定ページが「無効なブロック」になるので、変えないこと。
 */

import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks, PanelColorSettings, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';
import metadata from './block.json';

registerBlockType( metadata.name, {
	edit( { attributes, setAttributes, className } ) {
		return (
			<>
				<InspectorControls>
					<PanelBody title="セクションレイアウト" className="hametuha-section-layout">
						<ToggleControl
							label="コンテナ"
							checked={ !! attributes.withContainer }
							onChange={ () => {
								setAttributes( { withContainer: ! attributes.withContainer } );
							} }
							help="コンテナがあると、中身が中央によります。"
						/>
					</PanelBody>
					<PanelColorSettings
						title="スタイル設定"
						initialOpen={ false }
						colorSettings={ [
							{
								value: attributes.backgroundColor,
								onChange: ( value ) => setAttributes( { backgroundColor: value } ),
								label: '背景色',
							},
						] }
					></PanelColorSettings>
				</InspectorControls>
				<div className={ className } style={ { backgroundColor: attributes.backgroundColor } }>
					<div className={ attributes.withContainer ? 'container' : 'no-container' }>
						<InnerBlocks templateLock={ false } />
					</div>
				</div>
			</>
		);
	},

	save( { className, attributes } ) {
		return (
			<section className={ className } style={ { backgroundColor: attributes.backgroundColor } }>
				<div className={ attributes.withContainer ? 'container' : 'no-container' }>
					<InnerBlocks.Content />
				</div>
			</section>
		);
	},
} );
