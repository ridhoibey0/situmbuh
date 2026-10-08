@props(['level' => null])
@php
    $key = in_array($level, ['tinggi', 'sedang', 'rendah'], true) ? $level : 'belum';
    $label = ['tinggi' => 'Tinggi', 'sedang' => 'Sedang', 'rendah' => 'Rendah', 'belum' => 'Belum dinilai'][$key];
@endphp
<span {{ $attributes->merge(['class' => "lvl is-{$key}"]) }}>{{ $label }}</span>
