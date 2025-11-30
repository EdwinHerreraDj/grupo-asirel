@props(['href', 'title' => 'Descargar factura'])

<a href="{{ $href }}" target="_blank"
   class="inline-flex items-center justify-center w-9 h-9 rounded-full 
          bg-green-100 text-green-700 border border-green-200 
          hover:bg-green-200 hover:border-green-300 transition-all duration-200 shadow-sm"
   title="{{ $title }}">
   <i class="mgc_download_2_line text-lg"></i>
</a>
