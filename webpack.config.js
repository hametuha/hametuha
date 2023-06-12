const TerserPlugin = require( 'terser-webpack-plugin' );

module.exports = {
	mode: 'production',
	module: {
		rules: [
			{
				test: /\.jsx?$/,
				exclude: /(node_modules)/,
				use: {
					loader: 'babel-loader',
					options: {
						presets: [ '@babel/preset-env' ],
						plugins: [ '@babel/plugin-transform-react-jsx' ],
					},
				},
			},
		],
	},
	optimization: {
		minimize: true,
		minimizer: [
			new TerserPlugin( {
				extractComments: {
					condition: true,
					filename: ( fileData ) => {
						return `${ fileData.filename }.LICENSE.txt`;
					},
					banner: ( licenseFile ) => {
						return `License information can be found in ${ licenseFile }`;
					},
				},
			} ),
		],
	},
};
