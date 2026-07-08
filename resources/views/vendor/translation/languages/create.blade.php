

@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ __('admin.add_language') }} - {{setting('site_name')}}</title>
@endsection

@section('subcontent')
    @include('../layout/components/top-bar')

    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">{{ __('admin.add_language') }}</h2>
    </div>

    <form action="{{ url('languages/language/store') }}" method="POST">
        <div class="grid grid-cols-12 gap-6 mt-5">
            <div class="intro-y col-span-12 lg:col-span-12 bg_">
                <div class="intro-y box p-5">
                        
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    @include('translation::forms.text', ['field' => 'name', 'label' => __('admin.language_name'), ])

                    @include('translation::forms.text', ['field' => 'locale', 'label' => __('admin.iso_code'), ])
                    
                    <select name="position" class=" input w-full border mt-2">
                        <option value="ltr">LTR</option>
                        <option value="rtl">RTL</option>
                    </select>
                    <div class="text-right mt-8">
                        <button type="submit" class="button w-24 bg-theme-1 text-white">{{ __('admin.save') }}</button>
                        <a href="{{url('languages')}}?layout={{$layout}}&theme={{$theme}}" class="button mr-1 mb-2 border text-gray-700 dark:bg-dark-5 dark:text-gray-300 bg_white">{{__('admin.back')}}</a>
                    </div>
                </div>            
            </div>
        </div> 
    </form>
 

@endsection