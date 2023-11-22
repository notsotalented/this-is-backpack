{{-- handle_payment_field field --}}
@php
    $field['value'] = old_empty_or_null($field['name'], '') ?? ($field['value'] ?? ($field['default'] ?? ''));
@endphp

@include('crud::fields.inc.wrapper_start')
<label>{!! $field['label'] !!}</label>
@include('crud::fields.inc.translatable_icon')

<input type="button" value="Pay" id="pay_button" class="btn btn-primary"
    onclick="bpFieldInitDummyFieldElement(jQuery(this).closest('.form-group').find('input'))"
    @include('crud::fields.inc.attributes')>

{{-- HINT --}}
@if (isset($field['hint']))
    <p class="help-block">{!! $field['hint'] !!}</p>
@endif
@include('crud::fields.inc.wrapper_end')

{{-- CUSTOM CSS --}}
@push('crud_fields_styles')
    {{-- How to load a CSS file? --}}
    @basset('handle_paymentFieldStyle.css')

    {{-- How to add some CSS? --}}
    @bassetBlock('backpack/crud/fields/handle_payment_field-style.css')
        <style>
            .handle_payment_field_class {
                display: none;
            }
        </style>
    @endBassetBlock
@endpush

{{-- CUSTOM JS --}}
@push('crud_fields_scripts')
    {{-- How to load a JS file? --}}
    @basset('handle_paymentFieldScript.js')

    {{-- How to add some JS to the field? --}}
    @bassetBlock('path/to/script.js')
        <script>
            function bpFieldInitDummyFieldElement(element) {
                
            }
        </script>
    @endBassetBlock
@endpush
