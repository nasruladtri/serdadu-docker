@php
    $logoPath = asset('img/admin.png');
@endphp
<img src="{{ $logoPath }}"
     alt="Serdadu Admin"
     {{ $attributes->merge(['class' => 'block h-10 w-auto']) }} />
