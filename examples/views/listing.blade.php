<x-layout :title="$listing->title" :meta-description="$metaDescription">
    <article class="listing">
        <h1>{{ $listing->title }}</h1>
        <p class="price">{{ number_format($listing->price, 0, ',', ' ') }} €</p>
        <p>{{ $listing->description }}</p>

        <h2>Neighbourhood</h2>
        <x-yatmo-map :latitude="$listing->latitude" :longitude="$listing->longitude"
                     :marker="$listing->discreet ? 'circle' : 'pin'" :circle-radius="300" isochrone="right" :rounded="12" height="520" />

        <h2>Around the property</h2>
        <x-yatmo-pois :latitude="$listing->latitude" :longitude="$listing->longitude" categories="education,transport,shopping" mode="walking" />

        <h2>Living here</h2>
        <x-yatmo-text :latitude="$listing->latitude" :longitude="$listing->longitude" :titles="$listing->discreet ? 'city' : 'street-city'" />
    </article>
</x-layout>
