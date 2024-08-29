@auth
<div class="discount" id="comments">
    <div class="image">
      <div class="content">
        <h2>Share Your Experience</h2>
        <p>
            We value your feedback! Our community thrives on the experiences and stories shared by our patrons. Whether you've found the perfect book, attended an enriching event, or simply enjoyed the ambiance of our library, we want to hear from you. Please take a moment to share your thoughts and experiences with us. Your testimonials help us to continue providing a welcoming and resourceful environment for all. Thank you for being a part of our library community!
        </p>
        <img src="imgs/discount.png" alt="" />
      </div>
    </div>
    <div class="form">
      <div class="content">
        <img src="{{ $setting->logo }}" alt="" />
        <br><br>
        <h2>Feel free to Write a Comment </h2>
        <br>
        <form method="POST" action="{{ route('comment.create') }}">
            @csrf
          <textarea class="input" id="comment" name="comment" placeholder="Tell Us About Your Needs" name="message"></textarea>
          <div>
            <label for="rating">Rating:</label>
            <select id="rating" name="rating" required>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
            </select>
        </div>
        <br>
        <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
          <input type="submit" value="Send" />
        </form>
      </div>
    </div>
</div>
@endauth

