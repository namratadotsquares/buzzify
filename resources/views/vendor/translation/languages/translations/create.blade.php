<?php $theme = 'light'; $layout= 'side-menu'; $page_name ='Language'; ?>

@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ __('translation::translation.add_translation') }} - {{setting('site_name')}}</title>
@endsection

@section('subcontent')
    @include('../layout/components/top-bar')

    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">{{ __('translation::translation.add_translation') }}</h2>
    </div>

    <form action="{{ route('languages.translations.store', $language) }}" method="POST">
        <div class="grid grid-cols-12 gap-6 mt-5">
            <div class="intro-y col-span-12 lg:col-span-12 bg_">
                <div class="intro-y box p-5">
                        
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    @include('translation::forms.text', ['field' => 'group', 'label' => __('translation::translation.group_label'), 'placeholder' => __('translation::translation.group_placeholder')])
                    
                    @include('translation::forms.text', ['field' => 'key', 'label' => __('translation::translation.key_label'), 'placeholder' => __('translation::translation.key_placeholder')])

                    @include('translation::forms.text', ['field' => 'value', 'label' => __('translation::translation.value_label'), 'placeholder' => __('translation::translation.value_placeholder')])

                    <div class="text-right mt-8">
                        <button type="submit" class="button w-24 bg-theme-1 text-white">{{ __('translation::translation.save') }}</button>
                    </div>
                </div>            
            </div>
        </div> 
    </form>
 

@endsection