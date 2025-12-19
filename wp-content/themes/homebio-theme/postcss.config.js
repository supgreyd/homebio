/**
 * PostCSS Configuration
 *
 * Handles CSS processing: imports, modern CSS features, and minification.
 */

module.exports = {
    plugins: [
        // Process @import statements
        require('postcss-import'),

        // Use modern CSS features with fallbacks
        require('postcss-preset-env')({
            stage: 2,
            features: {
                'nesting-rules': true,
                'custom-properties': false // Keep CSS variables as-is
            }
        }),

        // Minify CSS in production
        ...(process.env.NODE_ENV === 'production' ? [
            require('cssnano')({
                preset: ['default', {
                    discardComments: {
                        removeAll: true
                    }
                }]
            })
        ] : [])
    ]
};
