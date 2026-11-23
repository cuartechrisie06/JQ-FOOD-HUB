<div class="mb-3">
<label class="form-label" for="category_name">Category Name</label>
<input id="category_name" class="form-control" name="category_name" value="{{ old('category_name', $category->category_name ?? '') }}" required>
</div>
<div class="mb-3">
<label class="form-label" for="description">Description</label>
<textarea id="description" class="form-control" name="description" maxlength="200">{{ old('description', $category->description ?? '') }}</textarea>
</div>
