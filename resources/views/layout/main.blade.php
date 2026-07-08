@extends('../layout/base')



@section('body')

    <body class="app">

        <style>
            /* Ensure admin select dropdowns render above boxes */
            .intro-y.box, .box { overflow: visible !important; }
            .tail-select .select-dropdown,
            .tail-select .select-list,
            .ts-wrapper .ts-list,
            .select2-container .select2-dropdown,
            .select2-container .select2-results,
            .select2-container--open .select2-dropdown,
            .select-dropdown,
            .dropdown-menu,
            .choices__list,
            .choices__list--dropdown {
                z-index: 999999 !important;
                position: absolute !important;
            }
            /* Global modal fix: force modals to center in viewport and avoid parent transform issues */
            .modal { display: none; }
            .modal.show { display: block !important; visibility: visible !important; opacity: 1 !important; position: fixed !important; left: 0; top: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.45); z-index: 100000 !important; }
            .modal .modal__content { position: fixed !important; left: 50% !important; top: 50% !important; transform: translate(-50%, -50%) !important; max-width: 640px; width: auto; background: #fff; border-radius: 8px; z-index:100001 !important; }
        </style>

        @yield('content')

        <script src="{{ asset('js/jquery-v3.6.0.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
        <script src="{{ asset('dist/js/app.js') }}"></script>

        <script src="{{ asset('dist/js/tagsinput.js') }}"></script>

        <script src="{{ asset('js/bootstrap3-typeahead.min.js') }}"></script>

        <script src="{{ asset('js/admin.js') }}?v={{ time() }}"></script>

        {{-- <script src="{{ asset('js/dark-mode-switcher.js') }}"></script> --}}
        @include('../layout/components/dark-mode-switcher')
        <script type="text/javascript" src="{{ url('plugin/toastr/toastr.min.js') }}"></script>

        <script src="{{ url('plugin/magnific-popup/dist/jquery.magnific-popup.min.js') }}"></script>

        <script src="{{ asset('js/jquery-ui.js') }}"></script>

        <script src="{{ asset('/vendor/translation/js/app.js') }}"></script>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.1.20/jquery.fancybox.min.js"></script>

        <script src="{{ asset('js/dropzone.js') }}" type="text/javascript"></script>

        @yield('script')



        <script>
            $(document).ready(function() {



                $(".modal").modal({

                    backdrop: 'static',

                    keyboard: false,
                    // Prevent Bootstrap from auto-opening all modals on page load (default is show: true)
                    show: false

                });



            });
            
            // Ensure modal elements are appended to body and centered to avoid being affected by parent transforms
            document.addEventListener('DOMContentLoaded', function() {
                try {
                    document.querySelectorAll('.modal').forEach(function(m){
                        if (m.parentNode !== document.body) document.body.appendChild(m);
                        m.style.position = 'fixed';
                        m.style.left = '0'; m.style.top = '0'; m.style.width = '100vw'; m.style.height = '100vh'; m.style.zIndex = '100000';
                        var content = m.querySelector('.modal__content');
                        if (content) {
                            content.style.position = 'fixed';
                            content.style.left = '50%'; content.style.top = '50%'; content.style.transform = 'translate(-50%,-50%)';
                            content.style.zIndex = '100001';
                        }
                    });
                    // When a modal is about to be opened, close any other open modals first
                    document.addEventListener('click', function(ev){
                        var toggle = ev.target.closest('[data-toggle="modal"]');
                        if (toggle) {
                            var target = toggle.getAttribute('data-target') || toggle.getAttribute('href');
                            if (!target) return;
                            document.querySelectorAll('.modal.show').forEach(function(openM){
                                try {
                                    if ('#'+openM.id !== target) openM.classList.remove('show');
                                } catch(e){}
                            });
                        }
                    }, true);

                    // Also observe DOM mutations: if any modal gains the 'show' class, close other modals
                    try {
                        var mo = new MutationObserver(function(mutations){
                            mutations.forEach(function(m){
                                if (m.type === 'attributes' && m.attributeName === 'class') {
                                    var node = m.target;
                                    if (node && node.classList && node.classList.contains('modal') && node.classList.contains('show')) {
                                        // close other modals
                                        document.querySelectorAll('.modal.show').forEach(function(openM){
                                            if (openM !== node) openM.classList.remove('show');
                                        });
                                        // remove any fallback overlays created earlier
                                        var fb = document.getElementById('bulk-edit-fallback'); if (fb) fb.parentNode.removeChild(fb);
                                    }
                                }
                            });
                        });
                        mo.observe(document.body, { attributes: true, subtree: true, attributeFilter: ['class'] });
                    } catch (moErr) {
                        console.error('MutationObserver error', moErr);
                    }
                } catch (e) {
                    console.error('Modal init error', e);
                }
            });

            Dropzone.autoDiscover = false;

            $(document).ready(function() {



                if (document.getElementById("myDrop")) {



                    $('.reorder').on('click', function() {

                        $("ul.nav").sortable({
                            tolerance: 'pointer'
                        });

                        $('.reorder').html('Save Reordering & Url');

                        $('.reorder').attr("id", "updateReorder");

                        $('#reorder-msg').slideDown('');

                        $('.img-link').attr("href", "javascript:;");

                        $('.img-link').css("cursor", "move");

                        $("#updateReorder").click(function(e) {

                            if (!$("#updateReorder i").length) {

                                $(this).html('').prepend('<i class="fa fa-spin fa-spinner"></i>');

                                $("ul.nav").sortable('destroy');

                                $("#reorder-msg").html(
                                    "Reordering Photos - This could take a moment. Please don't navigate away from this page."
                                    ).removeClass('light_box').addClass('notice notice_error');



                                var h = [];

                                var url = [];

                                $("ul.nav li").each(function() {
                                    h.push($(this).attr('id').substr(9));
                                });

                                $("ul.nav li input").each(function() {
                                    url.push($(this).val());
                                });



                                $.ajax({

                                    type: "POST",

                                    url: "/api/reArrange/ads_images",

                                    data: {
                                        ids: " " + h + "",
                                        adID: $('#updateReorder').attr('adID'),
                                        url: " " + url + ""
                                    },

                                    success: function(data) {

                                        if (data == 1 || parseInt(data) == 1) {

                                            window.location.reload();

                                        }

                                    }

                                });

                                return false;

                            }

                            e.preventDefault();

                        });

                    });



                    $(function() {

                        $("#myDrop").sortable({

                            items: '.dz-preview',

                            cursor: 'move',

                            opacity: 0.5,

                            containment: '#myDrop',

                            distance: 20,

                            tolerance: 'pointer',

                        });



                        $("#myDrop").disableSelection();

                    });



                    //Dropzone script





                    var myDropzone = new Dropzone("div#myDrop",

                        {

                            paramName: "files", // The name that will be used to transfer the file

                            addRemoveLinks: true,

                            uploadMultiple: true,

                            autoProcessQueue: false,

                            parallelUploads: 50,

                            maxFilesize: 1, // MB

                            acceptedFiles: ".png, .jpeg, .jpg, .gif",

                            url: "/api/uploads/ads_images",

                            previewTemplate: "<div class='dz-preview dz-file-preview'>"

                                +
                                "<div class='dz-image'><img data-dz-thumbnail /></div>" +

                                "<div class='dz-details'>" +

                                "<div class='dz-size'><span data-dz-size></span></div>" +

                                "<div class='dz-filename'><span data-dz-name></span></div>" +

                                "</div>" +

                                "<div class='dz-progress'><span class='dz-upload' data-dz-uploadprogress></span></div>" +

                                "<div class='dz-error-message'><span data-dz-errormessage></span></div>" +

                                "<input type='text' name='url[]'  placeholder='Image URL' style='border: #0b0b0b solid 1px'>" +

                                "</div>"

                        });



                    myDropzone.on("sending", function(file, xhr, formData) {

                        var filenames = [];

                        var urls = $('input[name="url[]"]').map(function() {

                            return this.value; // $(this).val()

                        }).get();



                        $('.dz-preview .dz-filename').each(function() {

                            filenames.push($(this).find('span').text());

                        });



                        formData.append('filenames', filenames);

                        formData.append('adId', $('#updateReorder').attr('adID'));

                        formData.append('url', urls);

                    });



                    /* Add Files Script*/

                    myDropzone.on("success", function(file, message) {

                        $("#msg").html(message);

                        setTimeout(function() {
                            window.location.reload()
                        }, 200);

                    });



                    myDropzone.on("error", function(data) {

                        $("#msg").html(
                            '<div class="alert alert-danger">There is some thing wrong, Please try again!</div>'
                            );

                    });



                    myDropzone.on("complete", function(file) {

                        myDropzone.removeFile(file);

                    });



                    $("#add_file").on("click", function() {

                        myDropzone.processQueue();

                    });

                }

            });
        </script>

    </body>
@endsection
