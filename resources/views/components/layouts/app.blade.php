<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <title>GPAR</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Iconic Bootstrap 4.5.0 Admin Template">
    <meta name="author" content="WrapTheme, design by: ThemeMakker.com">
    <link rel="icon" href="/images/logo.png" type="image/x-icon">

    <!-- VENDOR CSS -->
    <link rel="stylesheet" href="/assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/vendor/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="/assets/vendor/toastr/toastr.min.css">
    <link rel="stylesheet" href="/assets/vendor/charts-c3/plugin.css" />
    <link rel="stylesheet" href="/assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/vendor/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="/assets/vendor/bootstrap-multiselect/bootstrap-multiselect.css">
    <link rel="stylesheet" href="/assets/vendor/bootstrap-datepicker/css/bootstrap-datepicker3.min.css">
    <link rel="stylesheet" href="/assets/vendor/bootstrap-colorpicker/css/bootstrap-colorpicker.css" />
    <link rel="stylesheet" href="/assets/vendor/multi-select/css/multi-select.css">
    <link rel="stylesheet" href="/assets/vendor/bootstrap-tagsinput/bootstrap-tagsinput.css">
    <link rel="stylesheet" href="/assets/vendor/nouislider/nouislider.min.css" />


    <link rel="stylesheet" href="/assets/vendor/jquery-datatable/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/assets/vendor/jquery-datatable/fixedeader/dataTables.fixedcolumns.bootstrap4.min.css">
    <link rel="stylesheet" href="/assets/vendor/jquery-datatable/fixedeader/dataTables.fixedheader.bootstrap4.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.slim.js"
        integrity="sha256-UgvvN8vBkgO0luPSUl2s8TIlOSYRoGFAX4jlCIm9Adc=" crossorigin="anonymous"></script>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>


    <!-- MAIN Project CSS file -->
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/chatapp.css">
    @livewireStyles

</head>

<body data-theme="light" class="font-nunito">
    <div id="wrapper" class="theme-cyan">

        <!-- Page Loader -->
        <div class="page-loader-wrapper">
            <div class="loader">
                <div class="m-t-30"><img src="/assets/images/logo-icon.svg" width="48" height="48"
                        alt="Iconic">
                </div>
                <p>Please wait...</p>
            </div>
        </div>

        <!-- Top navbar div start -->
        @include('partials.navbar')

        <!-- main left menu -->
        @include('partials.sidebar')

        <!-- mani page content body part -->
        {{ $slot }}

    </div>
    @livewireScripts

    <!-- Javascript -->
    <script src="/assets/bundles/libscripts.bundle.js"></script>
    <script src="/assets/bundles/vendorscripts.bundle.js"></script>

    <script src="/assets/bundles/datatablescripts.bundle.js"></script>
    <script src="/assets/vendor/jquery-datatable/buttons/dataTables.buttons.min.js"></script>
    <script src="/assets/vendor/jquery-datatable/buttons/buttons.bootstrap4.min.js"></script>
    <script src="/assets/vendor/jquery-datatable/buttons/buttons.colVis.min.js"></script>
    <script src="/assets/vendor/jquery-datatable/buttons/buttons.html5.min.js"></script>
    <script src="/assets/vendor/jquery-datatable/buttons/buttons.print.min.js"></script>


    <!-- page vendor js file -->
    <script src="/assets/vendor/toastr/toastr.js"></script>
    <script src="/assets/bundles/c3.bundle.js"></script>

    <!-- page js file -->
    <script src="/assets/bundles/mainscripts.bundle.js"></script>
    <script src="/assets/js/pages/tables/jquery-datatable.js"></script>
    <script src="/assets/js/index.js"></script>

    <script src="/assets/js/pages/forms/advanced-form-elements.js"></script>
    <script src="/assets/vendor/bootstrap-colorpicker/js/bootstrap-colorpicker.js"></script> <!-- Bootstrap Colorpicker Js -->
    <script src="/assets/vendor/jquery-inputmask/jquery.inputmask.bundle.js"></script> <!-- Input Mask Plugin Js -->
    <script src="/assets/vendor/jquery.maskedinput/jquery.maskedinput.min.js"></script>
    <script src="/assets/vendor/multi-select/js/jquery.multi-select.js"></script> <!-- Multi Select Plugin Js -->
    <script src="/assets/vendor/bootstrap-multiselect/bootstrap-multiselect.js"></script>
    <script src="/assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
    <script src="/assets/vendor/bootstrap-tagsinput/bootstrap-tagsinput.js"></script> <!-- Bootstrap Tags Input Plugin Js -->
    <script src="/assets/vendor/nouislider/nouislider.js"></script> <!-- noUISlider Plugin Js -->
    <script src="/assets/js/pages/ui/dialogs.js"></script>


    <script>
        window.addEventListener('swal:modal', event => {
            swal({
                title: event.detail.message,
                text: event.detail.text,
                icon: event.detail.type,
                timer: 2000
            }).then(function() {
                console.log('okasd')
                window.Livewire.dispatch(event.detail.redirect)
            });
        });
    </script>
</body>

</html>
