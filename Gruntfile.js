module.exports = function (grunt) {

    grunt.initConfig({

        pkg: grunt.file.readJSON('package.json'),

        compass: {

            dist: {
                options: {
                    config: 'assets/config.rb',
                    basePath: 'assets',
                    sourcemap: true
                }
            }

        },

        jshint: {

            options: {
                jshintrc: 'assets/.jshintrc',
                force: true
            },

            files: [
                'assets/js/**/*.js',
                '!assets/js/**/*.min.js'
            ]

        },

        uglify: {
            build: {
                options: {
                    sourceMap: true,
                    mangle: true,
                    compress: true
                },
                files: [{
                    expand: true,
                    cwd: './assets/js/',
                    src: ['**/*.js', '!**/*.min.js'],
                    dest: './assets/js/',
                    ext: '.min.js'
                }]
            }
        },

        watch: {

            js: {
                files: ['assets/js/**/*.js', '!assets/js/**/*.min.js'],
                tasks: ['jshint', 'uglify']
            },

            compass: {
                files: ['assets/sass/*.scss'],
                tasks: ['compass']
            }
        }
    });

    // Load plugins
    grunt.loadNpmTasks('grunt-contrib-compass');
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-watch');

    // Register tasks
    grunt.registerTask('default', ['jshint', 'uglify', 'compass']);

};
