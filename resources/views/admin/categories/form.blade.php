{{-- PLACEHOLDER partial: $category --}}
@if ($errors->any())<ul role="alert">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
<label>Name <input name="name" value="{{ old('name', $category->name) }}" required maxlength="255"></label>
<label>Slug (optional) <input name="slug" value="{{ old('slug', $category->slug) }}" maxlength="190"></label>
<label>Description <textarea name="description">{{ old('description', $category->description) }}</textarea></label>
