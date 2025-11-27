@extends('layouts.admin', ['title' => 'Ringkasan Impor'])

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-semibold text-slate-900">
                Ringkasan Impor: {{ $filename }}
            </h2>
            <a href="{{ route('admin.import') }}"
               class="inline-flex items-center gap-2 rounded-full bg-gray-700 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Form Impor
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left px-6 py-3 font-medium text-gray-500 uppercase tracking-wider">Sheet</th>
                            <th class="text-left px-6 py-3 font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="text-left px-6 py-3 font-medium text-gray-500 uppercase tracking-wider">Rows OK</th>
                            <th class="text-left px-6 py-3 font-medium text-gray-500 uppercase tracking-wider">Rows Gagal</th>
                            <th class="text-left px-6 py-3 font-medium text-gray-500 uppercase tracking-wider">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($summary as [$sheet,$status,$ok,$fail,$errs])
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $sheet }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                  {{ $status==='success' ? 'bg-green-100 text-green-800' : ($status==='partial' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ strtoupper($status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ $ok }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ $fail }}</td>
                            <td class="px-6 py-4 text-gray-500">
                                @if($errs)
                                    <details class="group">
                                        <summary class="cursor-pointer text-blue-600 hover:text-blue-800 font-medium list-none flex items-center gap-1">
                                            <span>Lihat Detail</span>
                                            <svg class="h-4 w-4 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </summary>
                                        <ul class="mt-2 list-disc list-inside text-xs text-red-600 space-y-1 bg-red-50 p-3 rounded-lg">
                                            @foreach($errs as $e)<li>{{ $e }}</li>@endforeach
                                        </ul>
                                    </details>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if(!empty($highlights))
            @php
                $filterYears = $filters['years'] ?? [];
                $filterSemesters = $filters['semesters'] ?? [];
            @endphp
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
                <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Highlight Data (Sampel)
                    </h3>
                    <div class="text-right">
                        <span class="text-xs uppercase tracking-wide text-gray-500 block">Periode Data</span>
                        <div class="text-sm font-medium text-gray-900">
                            @if(!empty($filterYears))
                                Tahun {{ implode(', ', $filterYears) }}
                            @endif
                            @if(!empty($filterSemesters))
                                <span class="mx-1 text-gray-400">|</span> Semester {{ implode(', ', $filterSemesters) }}
                            @endif
                        </div>
                    </div>
                </div>

                <div class="grid gap-8">
                    @foreach($highlights as $highlight)
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="text-base font-semibold text-gray-800 flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#009B4D]"></span>
                                    {{ $highlight['label'] ?? $highlight['key'] }}
                                </h4>
                                @if(!empty($highlight['table']))
                                    <code class="text-xs bg-gray-100 px-2 py-1 rounded text-gray-500 font-mono">{{ $highlight['table'] }}</code>
                                @endif
                            </div>

                            @if(!empty($highlight['missing_table']))
                                <div class="bg-red-50 border border-red-100 rounded-lg p-4 text-sm text-red-700">
                                    Tabel {{ $highlight['table'] ?? '-' }} belum tersedia di database.
                                </div>
                            @elseif(empty($highlight['rows']))
                                <div class="bg-gray-50 border border-gray-100 rounded-lg p-4 text-sm text-gray-500 italic">
                                    Belum ada data untuk kategori ini pada impor terakhir.
                                </div>
                            @else
                                <div class="overflow-x-auto rounded-lg border border-gray-200">
                                    <table class="min-w-full text-xs md:text-sm divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                @foreach($highlight['columns'] as $column)
                                                    <th class="text-left px-4 py-2 font-medium text-gray-500 capitalize whitespace-nowrap">
                                                        {{ str_replace('_', ' ', $column) }}
                                                    </th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($highlight['rows'] as $row)
                                                <tr class="hover:bg-gray-50">
                                                    @foreach($highlight['columns'] as $column)
                                                        @php
                                                            $value = $row[$column] ?? null;
                                                        @endphp
                                                        <td class="px-4 py-2 whitespace-nowrap text-gray-700">
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
            </div>
        @endif
    </div>
@endsection
