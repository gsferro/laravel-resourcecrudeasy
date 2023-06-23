{{-- TODO Components card --}}
<div class="card mb-4">
    @if (!empty($header))
        <div class="card-header">
            {{ $header }}
        </div>
    @endif
    <form id="form-filter" autocomplete="off" method="post" action="#">
        <div class="card-body">
            <div class="column g-3">
                {{ $slot ?? '' }}
            </div>
        </div>
        <div class="card-footer">
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
    </form>
</div>
