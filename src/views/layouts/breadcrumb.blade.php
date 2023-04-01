<ul class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('home') }}">
            <i class="fa fa-home fa-fw"></i></a>
    </li>
    @forelse ($breadcrumb as $key => $item)
        <li class="breadcrumb-item {{ isset($item["href"]) ? "" : "active" }}">
            @isset($item["href"])
                <a href="{{$item["href"]}}">
                    @endisset

                    @isset($item["icone"])
                        <i class="{{ $item["icone"] }}"></i>
                    @endisset

                    {{ $item["titulo"] }}

                    @isset($item["href"])
                </a>
            @endisset
        </li>
    @empty
        <li class="breadcrumb-item active">{{$titulo ?? "Página Principal"}}</li>
    @endforelse
</ul>
