
<?php $theme = 'light'; $layout= 'side-menu'; $page_name ='Language'; ?>

@extends('../layout/' . $layout)

@section('subhead')
    <title>Add Blog - {{setting('site_name')}}</title>
@endsection

@section('subcontent')
    @include('../layout/components/top-bar')

    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">{{ __('translation::translation.edit_translation') }}</h2>
    </div>

    <form action="{{ route('languages.translations.update') }}" method="POST">
        <div class="grid grid-cols-12 gap-6 mt-5">
            <div class="intro-y col-span-12 lg:col-span-12 bg_">
                <div class="intro-y box p-5">
                        
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="language" value="{{$language}}">

                    <div class="mt-3">
                        <label for="group">
                            {{  __('translation::translation.group_label') }}
                        </label>
                        <input  readonly
                            class="@if($errors->has('group')) error @endif input w-full border mt-2" 
                            name="group" 
                            id="group" 
                            type="text" 
                            placeholder="{{  __('translation::translation.group_placeholder')}}"
                            value="{{$translation->group}}"
                            >
                        @if($errors->has('group'))
                            @foreach($errors->get('group') as $error)
                                <p class="error-text">{!! $error !!}</p>
                            @endforeach
                        @endif
                    </div>


                    <div class="mt-3">
                        <label for="key">
                            {{  __('translation::translation.key_label') }}
                        </label>
                        <input readonly 
                            class="@if($errors->has('key')) error @endif input w-full border mt-2" 
                            name="key" 
                            id="key" 
                            type="text" 
                            placeholder="{{  __('translation::translation.key_placeholder')}}"
                            value="{{$translation->key}}"
                          >
                        @if($errors->has('key'))
                            @foreach($errors->get('key') as $error)
                                <p class="error-text">{!! $error !!}</p>
                            @endforeach
                        @endif
                    </div>


                    <div class="mt-3">
                        <label for="value">
                            {{  __('translation::translation.value_label') }}
                        </label>
                        <input 
                            class="@if($errors->has('value')) error @endif input w-full border mt-2" 
                            name="value" 
                            id="value" 
                            type="text" 
                            placeholder="{{  __('translation::translation.value_placeholder')}}"
                            value="{{$translation->value}}"
                            >
                        @if($errors->has('value'))
                            @foreach($errors->get('value') as $error)
                                <p class="error-text">{!! $error !!}</p>
                            @endforeach
                        @endif
                    </div>

                    <div class="text-right mt-8">
                        <button type="submit" class="button w-24 bg-theme-1 text-white">{{ __('translation::translation.save') }}</button>
                    </div>
                </div>            
            </div>
        </div> 
    </form>
 

@endsection