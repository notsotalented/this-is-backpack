{{-- get_account_by_id_ajax_field field --}}
@php
$field['value'] = old_empty_or_null($field['name'], '') ?? ($field['value'] ?? ($field['default'] ?? ''));
@endphp

@include('crud::fields.inc.wrapper_start')
<label>{!! $field['label'] !!}</label>
@include('crud::fields.inc.translatable_icon')

<input type="text" name="{{ $field['name'] }}" id="{{ $field['name'] }}" value="{{ $field['value'] }}" autocomplete="off"
    @include('crud::fields.inc.attributes')>
<p class='help-block' id='account_info'>Use the ID of the account</p>

{{-- HINT --}}
@if (isset($field['hint']))
<p class="help-block">{!! $field['hint'] !!}</p>
@endif
@include('crud::fields.inc.wrapper_end')

{{-- CUSTOM CSS --}}
@push('crud_fields_styles')
{{-- How to load a CSS file? --}}
@basset('get_account_by_id_ajaxFieldStyle.css')

{{-- How to add some CSS? --}}
@bassetBlock('backpack/crud/fields/get_account_by_id_ajax_field-style.css')
<style>
    .get_account_by_id_ajax_field_class {
        display: none;
    }
</style>
@endBassetBlock
@endpush

{{-- CUSTOM JS --}}
@push('crud_fields_scripts')
{{-- How to load a JS file? --}}
@basset('get_account_by_id_ajaxFieldScript.js')

{{-- How to add some JS to the field? --}}
@bassetBlock('path/to/script.js')
<script>
    function bpFieldInitDummyFieldElement(element) {
        // this function will be called on page load, because it's
        // present as data-init-function in the HTML above; the
        // element parameter here will be the jQuery wrapped
        // element where init function was defined
        console.log(element.val());
    }

    $('#{{ $field["name"] }}').on('input', function () {
        var value = $(this).val();
        var url = '{{ route('get_account_by_id_ajax') }}';
        var data = {
            'id': value
        };
        $.ajax({
            url: url,
            type: 'GET',
            data: data,
            success: function (response) {
                if (response.status == 'success') {
                    $('#account_info').html(response.data.name + ' - ' + response.data.type);
                    $('#account_info').attr('readonly', true);
                    $('#account_info').attr('disabled', true);
                } else {
                    $('#account_info').html('Account not found!!');
                    $('#account_info').attr('readonly', true);
                    $('#account_info').attr('disabled', true);
                }
            },
            error: function (response) {
                console.log(response);
            }
        });
    });

</script>
@endBassetBlock
@endpush
