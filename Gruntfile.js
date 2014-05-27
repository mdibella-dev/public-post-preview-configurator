module.exports = function(grunt) {

  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    pot: {
      options: {
        text_domain: 'public-post-preview-configurator',
        dest: 'src/languages/',
        keywords: ['__', '_e', '_x:1,2c']
      },
      files: {
        src:  [ 'src/**/*.php' ],
        expand: true
      }
    }
  });
  grunt.loadNpmTasks('grunt-pot');
};