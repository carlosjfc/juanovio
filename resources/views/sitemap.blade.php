<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@if(config('juanovio.legal_reviewed'))
@foreach(config('juanovio.navigation') as $name => $label)<url><loc>{{ route($name) }}</loc></url>@endforeach
@foreach(config('juanovio.navigation') as $name => $label)<url><loc>{{ route('es.'.$name) }}</loc></url>@endforeach
@endif
</urlset>
