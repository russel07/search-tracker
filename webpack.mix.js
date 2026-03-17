let mix = require('laravel-mix');
mix.autoload({
    jquery: ['$', 'window.jQuery', 'jQuery']
});

mix.webpackConfig(
    {
        module: {
            rules: [
                {
                    test: /\.mjs$/, resolve: { fullySpecified: false },
                    include: /node_modules/,
                    type: "javascript/auto"
                }
            ]
        },
    }
);

mix.js('resources/js/admin.js', 'assets/js/admin.js').vue({ version: 3 })
    .sass('resources/css/admin.scss', 'assets/css/gift_card_admin.css')