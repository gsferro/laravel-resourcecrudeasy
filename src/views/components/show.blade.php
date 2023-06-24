@extends('layouts.app')

@section('content')
    {{-- TODO Components card --}}
    <div class="card mb-4">
        <div class="card-header">
            {{ __('Show') }}
        </div>
        <div class="card-body">
            @include($form)
        </div>
        <div class="card-footer">
            <x-link-cancel />
        </div>
    </div>
    <style>
        .liga-form-control-static {
            border: 1px solid #ccc;
            background: #f6f6f6;
            padding: 5px 0px 5px 11px;
            min-height: 32px;
            height: auto !important;
            border-radius: 4px;
            color: #202124;
        }
    </style>
@endsection
@section('js')
    <script type="text/javascript">
        $(() => {
           $('.form-control').addClass('liga-form-control-static').attr('disabled', true)
        });
    </script>
@stop
