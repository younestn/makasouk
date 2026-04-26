@php
    $currentLocale = app()->getLocale();
    $switchTo = $currentLocale === 'ar' ? 'en' : 'ar';
@endphp

<a
    href="{{ route('locale.switch', ['locale' => $switchTo]) }}"
    class="fi-btn fi-btn-size-sm fi-btn-color-gray fi-btn-outlined"
    style="margin-inline-start: .5rem;"
>
    {{ $currentLocale === 'ar' ? __('admin.layout.english') : __('admin.layout.arabic') }}
</a>

