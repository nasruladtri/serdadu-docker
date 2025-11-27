@props([
    'category' => 'gender',
    'downloadRoute' => 'public.charts.download.pdf',
])

@php
    $queryParams = array_merge(request()->query(), ['category' => $category]);
    $downloadUrl = route($downloadRoute, $queryParams);
    $defaultYear = request()->query('year', now()->year);
    $defaultSemester = request()->query('semester', 1);
    $downloadLabelBase = 'chart-' . $category . '-' . $defaultYear . '-s' . $defaultSemester;
@endphp

<div class="dk-table-heading__downloads flex flex-wrap gap-2 items-center justify-end text-right">
    <span 
        class="js-download-btn chart-action-btn cursor-pointer select-none"
        data-download-type="chart"
        data-download-format="pdf"
        data-download-url="{{ $downloadUrl }}"
        data-download-label="{{ $downloadLabelBase }}.pdf"
        data-year-default="{{ $defaultYear }}"
        data-semester-default="{{ $defaultSemester }}"
        role="button"
        tabindex="0"
        aria-label="Download PDF">
        <img src="{{ asset('img/pdf.png') }}" alt="PDF icon" class="w-7 h-7 md:w-8 md:h-8 object-contain" style="width:1.3rem;height:1.3rem;">
    </span>
</div>
