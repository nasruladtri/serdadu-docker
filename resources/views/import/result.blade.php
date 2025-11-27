<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Ringkasan Impor: {{ $filename }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-2 pr-4">Sheet</th>
                                <th class="text-left py-2 pr-4">Status</th>
                                <th class="text-left py-2 pr-4">Rows OK</th>
                                <th class="text-left py-2 pr-4">Rows Gagal</th>
                                <th class="text-left py-2">Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($summary as [$sheet,$status,$ok,$fail,$errs])
                            <tr class="border-b">
                                <td class="py-2 pr-4">{{ $sheet }}</td>
                                <td class="py-2 pr-4">
                                    <span class="px-2 py-1 rounded text-white
                                      {{ $status==='success' ? 'bg-green-600' : ($status==='partial' ? 'bg-yellow-600' : 'bg-red-600') }}">
                                        {{ strtoupper($status) }}
                                    </span>
                                </td>
                                <td class="py-2 pr-4">{{ $ok }}</td>
                                <td class="py-2 pr-4">{{ $fail }}</td>
                                <td class="py-2">
                                    @if($errs)
                                        <details><summary class="cursor-pointer text-indigo-600">lihat</summary>
                                            <ul class="list-disc ml-6">
                                                @foreach($errs as $e)<li>{{ $e }}</li>@endforeach
                                            </ul>
                                        </details>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                @if(!empty($highlights))
                    @php
                        $filterYears = $filters['years'] ?? [];
                        $filterSemesters = $filters['semesters'] ?? [];
                    @endphp
                    <div class="mt-8 border-t border-gray-200 pt-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-800">
                                Highlight Data (maks. 5 baris per kategori)
                            </h3>
                            <div class="text-right">
                                <span class="text-xs uppercase tracking-wide text-gray-500 block">Dari basis data terbaru</span>
                                @if(!empty($filterYears) || !empty($filterSemesters))
                                    <span class="text-xs text-gray-500">
                                        @if(!empty($filterYears))
                                            Tahun: {{ implode(', ', $filterYears) }}
                                        @endif
                                        @if(!empty($filterYears) && !empty($filterSemesters))
                                            &middot;
                                        @endif
                                        @if(!empty($filterSemesters))
                                            Semester: {{ implode(', ', $filterSemesters) }}
                                        @endif
                                    </span>
                                @endif
                            </div>
                        </div>

                        @foreach($highlights as $highlight)
                            <div class="mt-6">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-base font-semibold text-gray-700">
                                        {{ $highlight['label'] ?? $highlight['key'] }}
                                    </h4>
                                    @if(!empty($highlight['table']))
                                        <span class="text-xs text-gray-500">tabel: {{ $highlight['table'] }}</span>
                                    @endif
                                </div>

                                @if(!empty($highlight['missing_table']))
                                    <p class="mt-2 text-sm text-red-600">
                                        Tabel {{ $highlight['table'] ?? '-' }} belum tersedia di database.
                                    </p>
                                @elseif(empty($highlight['rows']))
                                    <p class="mt-2 text-sm text-gray-500">
                                        Belum ada data untuk kategori ini pada impor terakhir.
                                    </p>
                                @else
                                    <div class="overflow-x-auto mt-3">
                                        <table class="min-w-full text-xs md:text-sm border border-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    @foreach($highlight['columns'] as $column)
                                                        <th class="text-left px-3 py-2 border-b border-gray-200 capitalize">
                                                            {{ str_replace('_', ' ', $column) }}
                                                        </th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($highlight['rows'] as $row)
                                                    <tr class="border-b last:border-b-0">
                                                        @foreach($highlight['columns'] as $column)
                                                            @php
                                                                $value = $row[$column] ?? null;
                                                            @endphp
                                                            <td class="px-3 py-2 whitespace-nowrap">
                                                                @if(is_numeric($value))
                                                                    {{ number_format((float) $value, 0, ',', '.') }}
                                                                @else
                                                                    {{ $value === null || $value === '' ? '-' : $value }}
                                                                @endif
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                <a href="{{ route('import.form') }}"
                   class="inline-block mt-8 bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded">
                    Impor Lagi
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
