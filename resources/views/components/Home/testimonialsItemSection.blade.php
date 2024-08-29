<div id="testimonials" class="box">
    <img src="{{ $comment->user->image ? asset($comment->user->image) : asset('profile/anonymous.png') }}"  alt="" />
    <h3>{{ $comment->user->first_name }} {{ $comment->user->last_name }} </h3>
    <span class="title">{{ $comment->user->username}}</span>
    <div class="rate">
        @for ($i=0;$i<$comment->rating;$i++)
        <i class="filled fas fa-star"></i>
        @endfor
    </div>
    <p>
      {{ $comment->comment }}
    </p>
</div>
