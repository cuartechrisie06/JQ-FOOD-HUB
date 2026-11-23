<div class="row g-3">
<div class="col-md-6"><label class="form-label" for="package_name">Package Name</label><input id="package_name" class="form-control" name="package_name" value="{{ old('package_name', $package->package_name ?? '') }}" required></div>
<div class="col-md-6"><label class="form-label" for="event_type">Event Type</label><input id="event_type" class="form-control" name="event_type" value="{{ old('event_type', $package->event_type ?? '') }}" required></div>
<div class="col-12"><label class="form-label" for="package_description">Description</label><textarea id="package_description" class="form-control" name="description">{{ old('description', $package->description ?? '') }}</textarea></div>
<div class="col-md-3"><label class="form-label" for="price">Price</label><input id="price" type="number" step="0.01" min="0" class="form-control" name="price" value="{{ old('price', $package->price ?? '') }}" required></div>
<div class="col-md-3"><label class="form-label" for="min_pax">Minimum Pax</label><input id="min_pax" type="number" min="1" class="form-control" name="min_pax" value="{{ old('min_pax', $package->min_pax ?? 1) }}" required></div>
<div class="col-md-3"><label class="form-label" for="max_pax">Maximum Pax</label><input id="max_pax" type="number" min="1" class="form-control" name="max_pax" value="{{ old('max_pax', $package->max_pax ?? 1) }}" required></div>
<div class="col-md-3"><label class="form-label" for="package_image">Photo</label><input id="package_image" type="file" class="form-control" name="image" accept="image/jpeg,image/png,image/webp"></div>
<div class="col-12"><div class="form-check"><input type="hidden" name="is_available" value="0"><input class="form-check-input" type="checkbox" id="package_available" name="is_available" value="1" @checked(old('is_available', $package->is_available ?? 1))><label class="form-check-label" for="package_available">Available</label></div></div>
</div>

<hr>
<div class="d-flex justify-content-between align-items-center mb-2"><h2 class="h5 mb-0">Package Items</h2><button type="button" class="btn btn-sm btn-outline-dark" id="add-item">Add Inclusion</button></div>
<div id="package-items">
@php $existingItems = old('items', isset($package) ? $package->items->map(fn ($item) => ['item_description' => $item->item_description, 'quantity' => $item->quantity])->all() : []); @endphp
@foreach($existingItems as $itemIndex => $item)
<div class="row g-2 mb-2 package-item">
<div class="col-md-7"><input class="form-control" name="items[{{ $itemIndex }}][item_description]" value="{{ $item['item_description'] }}" placeholder="Item description" required></div>
<div class="col-md-3"><input class="form-control" name="items[{{ $itemIndex }}][quantity]" value="{{ $item['quantity'] }}" placeholder="Quantity" required></div>
<div class="col-md-2"><button type="button" class="btn btn-outline-danger remove-item">Remove</button></div>
</div>
@endforeach
</div>
<template id="package-item-template">
<div class="row g-2 mb-2 package-item">
<div class="col-md-7"><input class="form-control" name="items[__INDEX__][item_description]" placeholder="Item description" required></div>
<div class="col-md-3"><input class="form-control" name="items[__INDEX__][quantity]" placeholder="Quantity" required></div>
<div class="col-md-2"><button type="button" class="btn btn-outline-danger remove-item">Remove</button></div>
</div>
</template>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('package-items');
    const template = document.getElementById('package-item-template');
    document.getElementById('add-item').addEventListener('click', function () {
        const index = container.querySelectorAll('.package-item').length;
        container.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', index));
    });
    container.addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-item')) event.target.closest('.package-item').remove();
    });
});
</script>
