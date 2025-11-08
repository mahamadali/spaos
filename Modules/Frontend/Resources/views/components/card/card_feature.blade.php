<div class="features-box">
   <div class="d-md-flex d-sm-block align-items-center gap-3">
       <img src="{{ asset($feature['image'] ?? default_feature_image()) }}" alt="image" loading="lazy" class="feature-img">
       <div class="mt-md-0 mt-4">
           <h6 class="icon-box-title mb-2 line-count-2"><a>{{ $feature['title'] }}</a></h6>
           <p class="icon-box-desc mb-0 line-count-2">{{ $feature['description'] }}
           </p>
       </div>
   </div>
</div>
