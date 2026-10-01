<x-layout title="Maison Demo, properties for sale">
    <h1>Properties for sale</h1>
    <div class="grid">
        @foreach($listings as $listing)
            <div class="card">
                <a href="/listings/{{ $listing->slug }}">{{ $listing->title }}</a>
                <p class="price">{{ number_format($listing->price, 0, ',', ' ') }} €</p>
                <p>{{ Illuminate\Support\Str::limit($listing->description, 90) }}</p>
            </div>
        @endforeach
    </div>
</x-layout>
