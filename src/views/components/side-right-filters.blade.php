<div x-data="{ showFormFilters: false }">
    <div
        @if (isset($outside))
            class="fixed overflow-hidden right-0 bottom-10 sm:bottom-1/2 z-3" style="z-index: 1030"
        @endif
        >
        <button class="btn btn-outline-dark rounded-pill"
                @click="showFormFilters = true"
                x-show="!showFormFilters"
                title="{{ __('Filter App ') . count((array)$form) ?? 0 }}"
                type="button"
        >
            <i class="fa fa-filter fa-2x" aria-hidden="true" aria-label="icon"></i>
            {{-- TODO odometter --}}
            <span id="filter-count" class="tag-count bg-danger text-white">
                {{  count((array)$form) ?? 0 }}
            </span>
        </button>
    </div>

    <div id="side-filter" class="fixed inset-0 overflow-hidden z-3" x-show="showFormFilters" style="display: none; z-index: 1030">
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <div class="fixed flex inset-y-0 pl-10 right-0 sm:duration-700 w-full md:w-2/4">
                <div class="max-w-full relative w-screen">
                    <div class="absolute top-0 left-0 -ml-8 pt-4 pr-2 flex sm:-ml-10 sm:pr-4">
                        <button type="button" class="rounded-md text-gray-300 hover:text-white focus:outline-none focus:ring-2 focus:ring-white" @click="showFormFilters = false">
                            <span class="sr-only">Fechar</span>
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="h-full flex flex-col py-6 bg-white shadow-xl overflow-y-scroll" @click.outside="showFormFilters = false" @keyup.escape="showFormFilters = false">
                        <div class="mt-6 relative flex-1 px-4 sm:px-6">
                            <div class="absolute inset-0 px-4 sm:px-6">
                                <div class="h-full">
                                    {{ $slot }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>