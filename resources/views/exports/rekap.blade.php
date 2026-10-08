<table>
    <thead>
        <tr style="background-color: #4a90e2; color: white;">
            <th rowspan="2">No</th>
            <th rowspan="2">Nama Anak</th>
            <th rowspan="2">NIK</th>
            <th rowspan="2">Nama Orang Tua</th>
            <th rowspan="2">Alamat</th>
            <th rowspan="2">No HP</th>
            <th colspan="4">Pertumbuhan</th>
            <th colspan="7">Perkembangan</th>
        </tr>
        <tr style="background-color: #7bb1f9; color: white;">
            <th>BB</th>
            <th>TB</th>
            <th>Lila</th>
            <th>LK</th>
            <th>KPSP</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($data as $i => $anak)
            @php
                $perkembangan = $anak->latestKpspResults->keyBy('age_category_id');
                $measurement = $anak->latestMeasurement;
            @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $anak->name }}</td>
                <td>{{ $anak->nik }}</td>
                <td>{{ $anak->parent?->parent_name }}</td>
                <td>{{ $anak->parent?->address_detail }}</td>
                <td>{{ $anak->parent?->phone }}</td>
                <td>{{ $measurement->weight ?? '-' }}</td>
                <td>{{ $measurement->height ?? '-' }}</td>
                <td>{{ $measurement->arm_circumference ?? '-' }}</td>
                <td>{{ $measurement->head_circumference ?? '-' }}</td>
                <td>{{ $perkembangan[1]->interpretation ?? '-' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
