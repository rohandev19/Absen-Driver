<table>
    <thead>
        <tr>
            <th style="width: 250px;">Nama Driver</th>
            {{-- Loop Tanggal 1 s/d 30/31 --}}
            @foreach($dates as $date)
                <th style="width: 35px; text-align: center;">{{ $date }}</th>
            @endforeach
            <th style="width: 100px; text-align: center;">Total Hadir</th>
        </tr>
    </thead>
    <tbody>
        @foreach($matrix as $data)
            <tr>
                <td>{{ $data['name'] }}</td>

                {{-- Loop Status Centang/Silang --}}
                @foreach($data['data'] as $status)
                    <td style="text-align: center;">{{ $status }}</td>
                @endforeach

                <td style="text-align: center; font-weight: bold;">{{ $data['total'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>