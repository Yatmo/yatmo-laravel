@php($html = $html())
@if($html !== '')
<div {{ $attributes->merge(["class" => "yatmo-text-wrapper"]) }}>{!! $html !!}</div>
@endif
