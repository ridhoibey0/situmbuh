@props(['gender' => 'male', 'size' => null])
@php
    $girl = $gender === 'female';
    $bg = $girl ? '#ffd5df' : '#dbe8ff';
    $hair = $girl ? '#5b3a2e' : '#3b2a22';
@endphp
<svg {{ $attributes->merge(['class' => 'avatar']) }} @if ($size) width="{{ $size }}" height="{{ $size }}" @endif viewBox="0 0 64 64" role="img" aria-hidden="true">
    <circle cx="32" cy="32" r="32" fill="{{ $bg }}"/>
    <circle cx="32" cy="35" r="19" fill="#ffd9b8"/>
    @if ($girl)
        <path d="M13 33c0-12 8-19 19-19s19 7 19 19c-3-7-9-10-19-10s-16 3-19 10z" fill="{{ $hair }}"/>
        <circle cx="32" cy="14" r="4.5" fill="#ff7fa1"/>
    @else
        <path d="M14 32c1-11 8-17 18-17s17 6 18 17c-4-5-9-7-18-7s-14 2-18 7z" fill="{{ $hair }}"/>
        <path d="M30 15c0-4 6-4 6 0-2 1-4 1-6 0z" fill="{{ $hair }}"/>
    @endif
    <circle cx="25" cy="36" r="2.2" fill="#2b1d17"/>
    <circle cx="39" cy="36" r="2.2" fill="#2b1d17"/>
    <circle cx="21.5" cy="41" r="3" fill="#ff9aa8" opacity=".55"/>
    <circle cx="42.5" cy="41" r="3" fill="#ff9aa8" opacity=".55"/>
    <path d="M27.5 43c2.5 3 6.5 3 9 0" stroke="#b5553f" stroke-width="2" stroke-linecap="round" fill="none"/>
</svg>
