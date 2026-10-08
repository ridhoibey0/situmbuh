@props(['level' => null])
@php
    $key = in_array($level, ['tinggi', 'sedang', 'rendah'], true) ? $level : 'belum';
    $label = ['tinggi' => 'Prioritas tinggi', 'sedang' => 'Prioritas sedang', 'rendah' => 'Prioritas rendah', 'belum' => 'Belum dinilai'][$key];
@endphp
<span {{ $attributes->merge(['class' => "lvl is-{$key}"]) }}>{{ $label }}</span>
