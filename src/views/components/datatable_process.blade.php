<table class="datatable datatable-process responsive wrap {{ $tableExtraClass ?? "" }}">
    <thead>
        <tr>
            @foreach($theads as $thead)
                <th>{{ $thead }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>