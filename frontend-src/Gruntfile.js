module.exports = function(grunt) {
    "use strict";

    grunt.initConfig({
        pkg: grunt.file.readJSON('../package.json'),

        concat: {
            css: {
                src: [
                    'css/001-layers.css',
                    'css/002-reset.css',
                    'css/003-layout.css',
                    'css/004-components.css',
                    'css/005-ui.css',
                    'css/006-utilities.css',
                    'css/007-specific.css',
                    'css/008-stitch.css',
                ],
                dest: 'concat.css'
            }
        },

        cssmin: {
            css: {
                src: 'concat.css',
                dest: '../public_html/public/css/main.min.css'
            }
        },
        uglify: {
            js: {
                options: {
                    preserveComments: false
                },
                files: {
                    '../public_html/public/js/index.min.js': 'js/index.js',
                    '../public_html/public/js/forms.min.js': 'js/forms.js',
                    '../public_html/public/js/table.min.js': 'js/table.js',
                    '../public_html/public/js/ui.min.js': 'js/ui.js'
                }
            }
        },

        /**
         * Image/WebP compression.
         *
         * Favicon and fonts are excluded because they must be copied as-is.
         */
        cwebp: {
            dynamic: {
                options: {
                    q: 75
                },
                files: [{
                    expand: true,
                    cwd: 'assets/',
                    src: [
                        '**/*.{png,jpg,jpeg,gif}',
                        '!favicon/**',
                        '!fonts/**'
                    ],
                    dest: '../public_html/public/assets/'
                }]
            }
        },


        /**
         * SVG compression.
         *
         * Favicon and fonts are excluded because they may include files that
         * should remain untouched, especially favicon SVGs and SVG font files.
         */
        svgmin: {
            options: {
                plugins: [{
                    name: 'preset-default',
                    params: {
                        overrides: {
                            removeViewBox: false
                        }
                    }
                }]
            },
            dist: {
                files: [{
                    expand: true,
                    cwd: 'assets/',
                    src: [
                        '**/*.svg',
                        '!favicon/**',
                        '!fonts/**'
                    ],
                    dest: '../public_html/public/assets/',
                    ext: '.svg'
                }]
            }
        },
        /**
         * Static assets copied as-is.
         */
        copy: {
            static: {
                files: [
                    {
                        expand: true,
                        cwd: 'assets/favicon/',
                        src: ['**/*'],
                        dest: '../public_html/public/assets/favicon/'
                    },
                    {
                        expand: true,
                        cwd: 'assets/fonts/',
                        src: ['**/*'],
                        dest: '../public_html/public/assets/fonts/'
                    }
                ]
            }
        },


        /** Watch file changes and run tasks -- for DEV **/
        watch: {
            css: {
                files: ['css/*.css'],
                tasks: ['concat:css', 'cssmin:css']
            },

            js: {
                files: ['js/*.js'],
                tasks: ['uglify:js']
            },

            assets: {
                files: ['assets/**/*'],
                tasks: ['optimize']
            }
        }
    });

    /**
     * Load plugins.
     */
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-cwebp');
    grunt.loadNpmTasks('grunt-svgmin');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-css');
    grunt.loadNpmTasks('grunt-contrib-uglify');

    /**
     * Tasks.
     *
     * build:
     *   Compiles CSS and JS once.
     *
     * optimize:
     *   Optimizes frontend image/SVG assets.
     *
     * deploy:
     *   Full production-oriented build.
     *
     * default:
     *   Safe default task that terminates.
     *
     * dev:
     *   Build once, then watch.
     */
    grunt.registerTask('build', [
        'concat:css',
        'cssmin:css',
        'uglify:js'
    ]);

    grunt.registerTask('optimize', [
        'cwebp',
        'svgmin',
        'copy:static'
    ]);

    grunt.registerTask('deploy', [
        'build',
        'optimize'
    ]);

    grunt.registerTask('default', [
        'build'
    ]);

    grunt.registerTask('dev', [
        'build',
        'watch'
    ]);
};