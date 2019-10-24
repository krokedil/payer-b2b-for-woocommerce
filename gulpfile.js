/* globals require */
var gulp = require('gulp');
var sort = require('gulp-sort');
var wpPot = require('gulp-wp-pot');

var translateFiles = '**/*.php';

gulp.task('makePOT', function () {
	return gulp.src('**/*.php')
		.pipe(sort())
		.pipe(wpPot({
			domain: 'payer-b2b-for-woocommerce',
			destFile: 'languages/payer-b2b-for-woocommerce.pot',
			package: 'payer-b2b-for-woocommerce',
			bugReport: 'http://krokedil.se',
			lastTranslator: 'Krokedil <info@krokedil.se>',
			team: 'Krokedil <info@krokedil.se>'
		}))
		.pipe(gulp.dest('languages/payer-b2b-for-woocommerce.pot'));
});

gulp.task('watch', function() {
    gulp.watch(translateFiles, ['makePOT']);
});