'use strict';
const { mix } = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/assets/js/app.js', 'public/js')
   .sass('resources/assets/sass/app.scss', 'public/css')
   .sourceMaps();

mix.webpackConfig({
    resolve: {
        alias: {
            '~': path.resolve(__dirname, 'resources/assets/js')
        }
    },
     watchOptions: {
        ignored: /node_modules/
    }
});

module.exports.devServer = {
    historyApiFallback: true,
    noInfo: true,
    compress: true,
    quiet: true,
    headers: {
        'Access-Control-Allow-Origin': '*'
    },
    inline: true,
    hot: true,
    port: 44300
};