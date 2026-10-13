@props(['variant' => 'horizontal', 'compact' => false, 'lazy' => false])
@php
    $image = config('juanovio.brand_assets.'.$variant);
    $srcset = collect($image['variants'])->map(fn ($size) => asset('brand/optimized/'.$image['base'].'-'.$size.'.webp').' '.$size.'w')->implode(', ');
    if ($variant === 'horizontal' && config('juanovio.logo_path') && is_file(public_path(config('juanovio.logo_path')))) {
        $image['path'] = config('juanovio.logo_path');
        [$image['width'], $image['height']] = getimagesize(public_path($image['path']));
        $srcset = '';
    }
    $monogram = config('juanovio.brand_assets.monogram');
    $compactSrcset = collect($monogram['variants'])->map(fn ($size) => asset('brand/optimized/'.$monogram['base'].'-'.$size.'.webp').' '.$size.'w')->implode(', ');
@endphp
<picture>
    @if($compact)
        <source media="(max-width: 80rem)" srcset="{{ $compactSrcset }}" sizes="72px" width="96" height="96" type="image/webp">
    @endif
    <img {{ $attributes }} src="{{ asset($image['path']) }}" @if($srcset) srcset="{{ $srcset }}" sizes="{{ $variant === 'full' ? '(max-width: 40rem) 176px, 240px' : '320px' }}" @endif width="{{ $image['width'] }}" height="{{ $image['height'] }}" alt="Juanovio International Trading Inc." loading="{{ $lazy ? 'lazy' : 'eager' }}" decoding="async">
</picture>
