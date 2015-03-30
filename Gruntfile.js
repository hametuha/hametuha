module.exports = function (grunt) {

    grunt.initConfig({

        pkg: grunt.file.readJSON('package.json'),

        sass: {
            options: {
                includePaths: ['assets/vendor/bootstrap-sass/assets/stylesheets'],
                sourceMap: true
            },
            dist: {
                options: {
                    outputStyle: 'compressed'
                },
                files: [{
                    expand: true,
                    cwd: './assets/sass/',
                    src: ['**/*.scss', '!**/_*.scss'],
                    dest: './assets/css/',
                    ext: '.css'
                }]
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

            grunt: { files: ['Gruntfile.js'] },

            js: {
                files: ['assets/js/**/*.js', '!assets/js/**/*.min.js'],
                tasks: ['jshint', 'uglify']
            },

            sass: {
                files: ['assets/sass/**/*.scss'],
                tasks: ['sass']
            }
        }
    });

    // Load plugins
    grunt.loadNpmTasks('grunt-sass');
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-watch');

    // Register tasks
    grunt.registerTask('build', ['jshint', 'uglify', 'sass']);
    grunt.registerTask('default', ['watch']);

};
