<div>
    <p>Reimagined Image</p>
    @if(!empty($reimages))
        @foreach($reimages as $reimage)
            <div class="image-container">
               
                <a href="{{ url('/upload/blog/banner/temp_banner/'.$reimage->image) }}" class="image-popup" title="">
                    <img src="{{ url('/upload/blog/banner/temp_banner/'.$reimage->image) }}" class="multipleUpload">
                </a>
                <p class="mt-5 text-center">
                    <input type="checkbox" class="image-checkbox" value="{{ $reimage->id }}" id="rimg{{$reimage->id}}" name="reimage" title="Use this banner">
                </p>
            </div>
        @endforeach
    @endif
</div>
