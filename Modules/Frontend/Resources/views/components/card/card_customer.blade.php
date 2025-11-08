<div class="testimonial rounded-3 p-5">
    <p class="testimonial-desc">{{ $review->review_msg ?? '-' }} </p>
    <div class="testimonial-author-info d-flex flex-sm-row flex-column align-items-sm-center gap-3">
      <div class="flex-shrink-0">
        <img src="{{ asset($review->user->profile_image ?? default_user_avatar()) }}" alt="Author" class="rounded-circle img-fluid author-image object-fit-cover">
      </div>
      <div>
        <h6 class="mb-1">
          {{ $review->user ? $review->user->first_name . ' ' . $review->user->last_name : default_user_name() }}  
        </h6>
        <span class="font-size-14">{{__('messages.happy_customer')}}</span>
      </div>
    </div>
   <div class="comma-img"> <img src="{{ asset('/img/frontend/comma.png') }}" alt=""> </div>
</div>