const mix = require("laravel-mix");
const path = require('path')

if(process.env.MIX_PUBLIC_PATH !== null && process.env.MIX_PUBLIC_PATH !== undefined && process.env.MIX_PUBLIC_PATH !== '') {
    mix.setPublicPath('public')
      .webpackConfig({
        output: {publicPath: process.env.MIX_PUBLIC_PATH}
      });
}
/**
 *
 * !Copy Assets
 *
 * -----------------------------------------------------------------------------
 */
// icon fonts
mix.copy(
  "node_modules/@fortawesome/fontawesome-free/webfonts/*",
  "public/webfonts"
);
/**
 *
 * !Backend/Dashboard
 *
 * -----------------------------------------------------------------------------
 */
// Build Backend/Dashboard SASS
mix.sass("resources/sass/libs.scss", "public/css/libs.min.css")
    .sass("public/scss/custom.scss", "public/css")
    .sass("public/scss/frezka-dashboard.scss", "public/css")
    .sass("public/scss/customizer.scss", "public/css")
    .sass("public/scss/rtl.scss", "public/css");



   mix.copy('node_modules/select2/dist/js/select2.min.js', 'public/js/select2.js') // Copy JS
   .version();


// Backend/Dashboard Styles
mix.styles(
    [
        "public/css/frezka-dashboard.css",
    ],
    "public/css/backend.css"
);

mix.styles([
  "node_modules/@fortawesome/fontawesome-free/css/all.min.css"
], 'public/css/icon.min.css')

// Backend/Dashboard Scripts
mix.js("resources/js/libs.js", "public/js/core/libs.min.js")
    .js("resources/js/backend-custom.js", "public/js/backend-custom.js");

mix.scripts(
    [
        "public/js/core/libs.min.js",
        "public/js/backend-custom.js",
    ],
    "public/js/backend.js"
);

mix.alias({
    '@': path.join(__dirname, 'resources/js')
});
mix.js('resources/js/app.js', 'public/js')
.sass('resources/sass/app.scss', 'public/css');

mix.copy('node_modules/datatables.net-buttons/js/dataTables.buttons.js', 'public/js/dataTables.buttons.min.js')
   .copy('node_modules/datatables.net-buttons/js/buttons.html5.js', 'public/js/buttons.html5.min.js')
   .copy('node_modules/datatables.net-buttons/js/buttons.print.js', 'public/js/buttons.print.min.js')
   .copy('node_modules/jszip/dist/jszip.min.js', 'public/js')
   .copy('node_modules/pdfmake/build/pdfmake.min.js', 'public/js')
   .copy('node_modules/pdfmake/build/vfs_fonts.js', 'public/js');

mix.copy('node_modules/apexcharts/dist/apexcharts.min.js', 'public/js')
.copy('node_modules/apexcharts/dist/apexcharts.css', 'public/css');


mix.copy('node_modules/toastr/build/toastr.min.js', 'public/js')
   .copy('node_modules/toastr/build/toastr.min.css', 'public/css')
   .copy('node_modules/jquery-validation/dist/jquery.validate.min.js', 'public/js');

   mix.copy('node_modules/jquery-validation/dist/jquery.validate.min.js', 'public/js')
   .copyDirectory('node_modules/tinymce', 'public/js/tinymce');

   mix.copy('node_modules/intl-tel-input/build/js/intlTelInput.min.js', 'public/js')
   .copy('node_modules/intl-tel-input/build/img/flags.webp', 'public/img/flags.webp')
   .copy('node_modules/intl-tel-input/build/js/utils.js', 'public/js')
   .copy('node_modules/intl-tel-input/build/css/intlTelInput.css', 'public/css')
   .copyDirectory('node_modules/intl-tel-input/build/img', 'public/img/intl-tel-input');
   mix.copy('node_modules/chart.js/dist/Chart.min.js', 'public/js');


mix.js("resources/js/setting-vue.js", "public/js/setting-vue.min.js")

mix.js("resources/js/profile-vue.js", "public/js/profile-vue.min.js")

mix.js("resources/js/import-export.js", "public/js/import-export.min.js")

// Global Vue Script
mix.js('resources/js/vue/app.js', 'public/js/vue.min.js').vue();
mix.js('resources/js/vue/booking-form.js', 'public/js/booking-form.min.js').vue();

/**
 * !Module Based Script & Style Bundel
 * @path Modules/{module_name}/app.js (This Could be vue, react, vanila javascript)
 * @path Module/{module_name}/app.scss (There is all module css)
 *
 * !Final Build Path Should Be
 * @path public/modules/{module_name}/script.js
 * @path public/modules/{module_name}/style.js
 *
 * *USAGE IN BLADE FILE*
 * ? <link rel="stylesheet" href="{{ mix('modules/{module_name}/style.css') }}">
 * ? <script src="{{ mix('modules/{module_name}/script.js') }}"></script>
 */

const Modules = require("./modules_statuses.json");
const Fs = require("fs");

for (const key in Modules) {
    if (Object.hasOwnProperty.call(Modules, key)) {
        if (
            Fs.existsSync(
                `${__dirname}/Modules/${key}/Resources/assets/js/app.js`
            )
        ) {
            mix.js(
                `${__dirname}/Modules/${key}/Resources/assets/js/app.js`,
                `modules/${key.toLocaleLowerCase()}/script.js`
            )
                .vue()
                .sourceMaps();
        }
        if (
            Fs.existsSync(
                `${__dirname}/Modules/${key}//Resources/assets/sass/app.scss`
            )
        ) {
            mix.sass(
                `${__dirname}/Modules/${key}//Resources/assets/sass/app.scss`,
                `modules/${key.toLocaleLowerCase()}/style.css`
            ).sourceMaps();
        }
    }
}

// !For Production Build Added To Version on File for cache
if (mix.inProduction()) {
    mix.version();
}
mix.js('Modules/Service/Resources/assets/js/service-form.js', 'public/modules/service/service-form.js');
mix.js('Modules/Category/Resources/assets/js/category-form.js', 'public/modules/category/category-form.js');
