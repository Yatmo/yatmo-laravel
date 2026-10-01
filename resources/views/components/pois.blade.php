@php($groups = $groups())
@if($groups !== [])
<div {{ $attributes->merge(['class' => 'yatmo-pois']) }}>
@foreach($groups as $group)
<div class="yatmo-pois-group"><{{ $heading }}>{{ $group['label'] }}</{{ $heading }}><ul>
@foreach($group['places'] as $place)
<li>{{ $place['name'] }}@if($place['time']) ({{ implode(', ', array_filter([$place['distance'], $place['time'], $place['mode']])) }})@endif</li>
@endforeach
</ul></div>
@endforeach
</div>
@endif
