<div id='preview'>
    <div class="col-span-12">
        @if(count($blogImages))
            @foreach($blogImages as $newimhg)
                <div class="col-span-12 sm:col-span-12 pull-left" id="delete_div_{{$newimhg->id}}">
                    <?php $url = url("/upload/blog/banner/360/".$newimhg->image) ?>
                    <div>
                        <img onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';"  src="{{$url}}" class="multipleUpload">
                    </div>
                    
                    <p class="mt-5">
                        <a href="javascript:;" onclick="deleteBlogImage('{{$newimhg->id}}');">{{__('admin.delete')}}</a>
                    </p>
                </div>                                                    
            @endforeach
        @endif
    </div>                                            
</div>