<!--<div id='preview'>-->
<!--    <div class="col-span-12">-->
<!--        @if(count($blogImages))-->
<!--            @foreach($blogImages as $newimhg)-->
<!--                <div class="col-span-12 sm:col-span-12 pull-left" id="delete_div_{{$newimhg->id}}">-->
<!--                    <?php  $url = url("/upload/blog/banner/360/".$newimhg->image);        ?>-->
<!--                    <div>-->
<!--                        <img onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';"  src="{{$url}}" class="multipleUpload">-->
<!--                    </div>-->
<!--                   @can('blog-delete')-->
<!--                    <p class="mt-5">-->
<!--                        <a href="javascript:;" onclick="deleteBlogImage('{{$newimhg->id}}');">{{__('admin.delete')}}</a>-->
<!--                    </p>-->
<!--                        @endcan-->
<!--                </div>-->
<!--            @endforeach-->
<!--        @endif-->
<!--    </div>-->
<!--</div>-->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<div id='preview'>
    <div class="col-span-12">
        @if(count($blogImages))
            @foreach($blogImages as $newimhg)
                <div class="col-span-12 sm:col-span-12 pull-left" id="delete_div_{{$newimhg->id}}">
                    <?php  $url = url("/upload/blog/banner/360/".$newimhg->image);        ?>
                    <div>
                        <a href="{{$url}}" class="image-popup" title="{{$newimhg->image}}">
                             <img onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';"  src="{{$url}}" class="multipleUpload" id="getImage{{$newimhg->id}}">
                        </a>
                    </div>
                   @can('blog-delete')
                        <p class="mt-5 text-center">
                            <a href="javascript:;" onclick="reimageThis('{{$newimhg->id}}')" title="Reimagine this Image"><i class="fa fa-file-image" style="color: blue;" aria-hidden="true"></i></a>
                            <a href="javascript:;" onclick="deleteBlogImage('{{$newimhg->id}}');" title="Delete Banner"><i class="fa fa-trash" style="color: red;" aria-hidden="true"></i></a>
                            
                        </p>
                        @endcan
                </div>
            @endforeach
        @endif
    </div>
</div>

