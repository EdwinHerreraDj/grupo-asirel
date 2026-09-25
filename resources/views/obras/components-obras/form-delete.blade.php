{{--
    Formulario oculto para eliminar una línea (material, alquiler, subcontrata,
    gasto vario o venta). El JS de cada página le pone la acción y lo envía.

    Estaba borrado y las cinco páginas que lo incluyen daban error 500.
--}}
<form id="form-delete" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>
