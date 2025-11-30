@props(['pdf', 'title' => 'Ver factura en visor'])

<button type="button"
   class="open-pdf-modal inline-flex items-center justify-center w-9 h-9 rounded-full 
          bg-blue-100 text-blue-700 border border-blue-200 
          hover:bg-blue-200 hover:border-blue-300 transition-all duration-200 shadow-sm"
   data-pdf="{{ $pdf }}"
   title="{{ $title }}">
   <i class="mgc_eye_2_line text-lg"></i>
</button>
