<form id="form-filter" autocomplete="off" method="post" action="#">
    <fieldset class="border rounded-3 p-3">
        @if (!empty($header))
            <legend class="float-none w-auto px-3">{{ $header }}</legend>
        @endif
        <div class="mb-4">
            {{ $slot ?? '' }}
        </div>
        <div>
            <x-form-submit>
                <i class="fa fa-filter fa-fw"></i>
                {{ __('Filter') }}
            </x-form-submit>
            <button class="btn btn-default" type="button"
                onclick="
                    $(this).closest('form').find('input[type!=\'hidden\'], select').val('').trigger('change') &&
                    $(this).closest('form').find('input[type=\'checkbox\'], input[type=\'radio\']').prop('checked',false).trigger('change')
                    $(this).closest('form').submit()
                ">
                <i class="fa fa-eraser fa-fw" aria-hidden="true"></i>
                {{ __('Clear') }}
            </button>
        </div>
    </fieldset>
</form>
