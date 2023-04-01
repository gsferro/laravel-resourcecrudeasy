<table class="dataTable datatable-process responsive wrap {{ $tableExtraClass ?? "" }}" style="width: 100%">
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