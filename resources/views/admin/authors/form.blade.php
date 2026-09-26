{{-- PLACEHOLDER partial: $author. multipart/form-data --}}
@if ($errors->any())<ul role="alert">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
<label>Name <input name="name" value="{{ old('name', $author->name) }}" required maxlength="255"></label>
<label>Slug (optional) <input name="slug" value="{{ old('slug', $author->slug) }}" maxlength="190"></label>
<label>Bio <textarea name="bio">{{ old('bio', $author->bio) }}</textarea></label>
<label>Photo (JPG/PNG/WebP) <input type="file" name="photo" accept="image/jpeg,image/png,image/webp"></label>
@if ($author->photo_url)<img src="{{ $author->photo_url }}" alt="" width="80"> <label><input type="checkbox" name="remove_photo" value="1"> Remove photo</label>@endif
