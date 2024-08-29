<div class="features" id="features">
    <h2 class="main-title">Categories</h2>
    <div class="container">
       @foreach ($categories as $category)
       <x-Home.CategoryItemSection :category="$category"/>
       @endforeach
    </div>
</div>
