<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><title>Commercial inquiry</title></head><body>
<h1>New commercial inquiry</h1>
@foreach($inquiry as $field => $value)
    @if($value !== '' && $value !== null)
        <h2>{{ match ($field) { 'type' => 'Inquiry type', 'name' => 'Full name', 'email' => 'Business email', 'category' => 'Category of interest', 'product' => 'Product / specifications', 'quantity' => 'Estimated quantity', 'quantityUnit' => 'Quantity unit', 'packDetails' => 'Pack size / unit details', 'origin' => 'Origin / sourcing location', 'destination' => 'Destination / delivery location', 'consent' => 'Privacy acknowledgment', default => ucfirst($field) } }}</h2>
        <p style="white-space: pre-wrap">{{ match ($field) { 'type' => config('juanovio.types.'.$value), 'category' => $value === 'unsure' ? 'Not sure yet' : config('juanovio.categories.'.$value.'.title'), 'quantityUnit' => config('juanovio.quantity_units.'.$value), 'consent' => $value ? 'Acknowledged: use of submitted information to review and respond to this inquiry.' : 'Not acknowledged', default => $value } }}</p>
    @endif
@endforeach
</body></html>
