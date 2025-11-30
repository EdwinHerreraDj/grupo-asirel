@extends('layouts.vertical', ['title' => 'Editar Obra', 'sub_title' => 'Pages', 'mode' => $mode ?? '', 'demo' => $demo ?? ''])

@section('css')
    <!-- Leaflet MAP -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <script src="https://unpkg.com/alpinejs" defer></script>

    <style>
        #mapaSeleccion {
            height: 420px;
            width: 100%;
        }
    </style>
@endsection

@section('content')
    {{-- Mensaje de éxito --}}
    @if (session('success'))
        <script>
            const notyf = new Notyf({
                duration: 4000,
                dismissible: true,
                position: {
                    x: 'right',
                    y: 'top',
                },
            });

            // Mostrar mensaje de éxito
            notyf.success('{{ session('success') }}');
        </script>
    @endif


    <div class="grid grid-cols-12">
        <div class="col-span-12">
            <div class="card p-6">


                <form action="{{ route('obras.update', $obra->id) }}" method="POST" class="space-y-8">
                    @csrf
                    @method('PUT')

                    <!-- CARD: TÍTULO -->
                    <div class="bg-primary text-gray-50 p-5 rounded-xl shadow-md">
                        <h1 class="text-2xl font-bold tracking-wide">Editar obra</h1>
                    </div>


                    <!-- CARD 1: DATOS DE LA OBRA -->
                    <div class="bg-white rounded-xl shadow p-6 border border-gray-200">
                        <h2 class="text-lg font-semibold text-primary mb-4 flex items-center gap-2">
                            <span class="w-1.5 h-5 bg-primary rounded"></span>
                            Datos de la obra
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                            <!-- Nombre -->
                            <div>
                                <label class="text-sm font-medium text-gray-700">Nombre Obra</label>
                                <input type="text" name="nombre" class="mt-1 form-input w-full"
                                    value="{{ old('nombre', $obra->nombre) }}" required>
                                @error('nombre')
                                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Estado -->
                            <div>
                                <label class="text-sm font-medium text-gray-700">Estado</label>
                                <select name="estado" class="mt-1 form-select w-full">
                                    <option value="planificacion"
                                        {{ old('estado', $obra->estado) == 'planificacion' ? 'selected' : '' }}>
                                        Planificación</option>
                                    <option value="ejecucion"
                                        {{ old('estado', $obra->estado) == 'ejecucion' ? 'selected' : '' }}>Ejecución
                                    </option>
                                    <option value="finalizada"
                                        {{ old('estado', $obra->estado) == 'finalizada' ? 'selected' : '' }}>Finalizada
                                    </option>
                                </select>
                                @error('estado')
                                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Fecha inicio -->
                            <div>
                                <label class="text-sm font-medium text-gray-700">Fecha inicio</label>
                                <input type="date" name="fecha_inicio" class="mt-1 form-input w-full"
                                    value="{{ old('fecha_inicio', $obra->fecha_inicio) }}">
                                @error('fecha_inicio')
                                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Fecha fin -->
                            <div>
                                <label class="text-sm font-medium text-gray-700">Fecha fin</label>
                                <input type="date" name="fecha_fin" class="mt-1 form-input w-full"
                                    value="{{ old('fecha_fin', $obra->fecha_fin) }}">
                                @error('fecha_fin')
                                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Importe Presupuestado -->
                            <div class="md:col-span-2">
                                <label class="text-sm font-medium text-gray-700">Importe presupuestado</label>
                                <input type="number" min="0" step="0.01" name="importe_presupuestado"
                                    class="mt-1 form-input w-full"
                                    value="{{ old('importe_presupuestado', $obra->importe_presupuestado) }}" required>
                                @error('importe_presupuestado')
                                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Descripción -->
                            <div class="md:col-span-2">
                                <label class="text-sm font-medium text-gray-700">Descripción</label>
                                <textarea name="descripcion" rows="4" class="mt-1 form-input w-full" placeholder="Detalles sobre la obra...">{{ old('descripcion', $obra->descripcion) }}</textarea>
                                @error('descripcion')
                                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>



                    <!-- CARD 2: GEOLOCALIZACIÓN -->
                    <div class="bg-white rounded-xl shadow p-6 border border-gray-200">

                        <h2 class="text-lg font-semibold text-primary mb-4 flex items-center gap-2">
                            <span class="w-1.5 h-5 bg-primary rounded"></span>
                            Geolocalización de la obra
                        </h2>

                        <p class="text-sm text-gray-600 mb-4">
                            Esta información se utiliza en la app de presencia para validar fichajes dentro del área
                            permitida.
                        </p>

                        <div class="flex justify-end mb-6">
                            <button type="button" onclick="abrirMapa()"
                                class="px-4 py-2 bg-primary text-white rounded-md shadow hover:bg-primary/90 transition text-sm">
                                Seleccionar en mapa
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="text-sm font-medium text-gray-700">Latitud</label>
                                <input type="text" id="latitud" name="latitud" class="mt-1 form-input w-full"
                                    value="{{ old('latitud', $obra->latitud) }}">
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-700">Longitud</label>
                                <input type="text" id="longitud" name="longitud" class="mt-1 form-input w-full"
                                    value="{{ old('longitud', $obra->longitud) }}">
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-700">Radio permitido (metros)</label>
                                <input type="number" id="radio" name="radio" class="mt-1 form-input w-full"
                                    value="{{ old('radio', $obra->radio) }}">
                            </div>
                        </div>

                    </div>



                    <!-- CARD 3: GASTOS INICIALES -->
                    <div class="bg-white rounded-xl shadow p-6 border border-gray-200">
                        @livewire('obras.gastos-iniciales', ['obra' => $obra], key('gastos-iniciales-edit-' . $obra->id))
                    </div>



                    <!-- CARD 4: CONFIRMACIÓN -->
                    <div class="bg-white rounded-xl shadow p-5 border border-gray-200 flex items-center gap-3">
                        <input type="checkbox" id="confirmar" class="form-checkbox text-primary rounded" required>
                        <label for="confirmar" class="text-sm font-medium text-gray-700">
                            Confirmo que los datos son correctos.
                        </label>
                    </div>


                    <!-- BOTÓN -->
                    <button type="submit"
                        class="w-full md:w-auto px-8 py-3 bg-primary text-white font-semibold rounded-lg shadow hover:bg-primary/90 transition">
                        Actualizar obra
                    </button>

                </form>
                <!-- MODAL MAPA -->
                <div id="modalMapa"
                    class="fixed inset-0 bg-black/40 backdrop-blur-sm hidden z-50 flex justify-center items-center p-4 transition duration-300">

                    <div
                        class="bg-white max-w-3xl w-full rounded-2xl shadow-2xl relative p-6 border border-gray-200 animate-fadeIn">

                        <!-- BOTÓN CERRAR -->
                        <button onclick="cerrarMapa()"
                            class="absolute top-4 right-4 text-gray-600 hover:text-red-600 transition text-2xl leading-none">
                            &times;
                        </button>

                        <!-- TÍTULO -->
                        <h3 class="text-xl font-semibold text-primary mb-5 flex items-center gap-2">
                            <span class="w-1.5 h-6 bg-primary rounded"></span>
                            Buscar ubicación en el mapa
                        </h3>

                        <!-- BUSCADOR -->
                        <div class="flex gap-2 mb-5">
                            <input id="buscadorMapa" type="text"
                                class="form-input flex-1 rounded-lg border-gray-300 focus:border-primary focus:ring-primary"
                                placeholder="Buscar dirección o sitio...">
                            <button onclick="buscarDireccion()"
                                class="px-5 py-2 bg-primary text-white rounded-lg shadow hover:bg-primary/90 transition">
                                Buscar
                            </button>
                        </div>

                        <!-- CONTENEDOR MAPA -->
                        <div id="mapaObra" class="rounded-xl border border-gray-300 shadow-inner"
                            style="height: 420px;">
                        </div>

                    </div>
                </div>

                <!-- ANIMACIÓN -->
                <style>
                    @keyframes fadeIn {
                        from {
                            opacity: 0;
                            transform: scale(0.97);
                        }

                        to {
                            opacity: 1;
                            transform: scale(1);
                        }
                    }

                    .animate-fadeIn {
                        animation: fadeIn 0.25s ease-out;
                    }
                </style>




            </div>
        </div>
    </div>
@endsection

@section('script')
    @vite(['resources/js/pages/highlight.js'])
    <script>
        let mapa, marcador;

        // ✔ Abrir modal
        function abrirMapa() {
            const modal = document.getElementById('modalMapa');
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            setTimeout(() => inicializarMapa(), 100);
        }

        // ✔ Cerrar modal
        function cerrarMapa() {
            document.getElementById('modalMapa').classList.add('hidden');
            document.getElementById('modalMapa').classList.remove('flex');
        }

        // ✔ Inicializar el mapa centrado en coordenadas actuales
        function inicializarMapa() {
            const latInput = document.getElementById('latitud');
            const lngInput = document.getElementById('longitud');

            const lat = parseFloat(latInput.value) || 37.18817;
            const lng = parseFloat(lngInput.value) || -3.60667;

            // Reset mapa si ya existe
            if (mapa) mapa.remove();

            mapa = L.map('mapaObra').setView([lat, lng], 15);

            // TileLayer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19
            }).addTo(mapa);

            // Crear marcador
            marcador = L.marker([lat, lng], {
                draggable: true
            }).addTo(mapa);

            // 1️⃣ Si arrastra el marcador → actualizar inputs
            marcador.on('dragend', e => {
                const pos = e.target.getLatLng();
                actualizarInputs(pos.lat, pos.lng);
            });

            // 2️⃣ Si hace CLICK en el mapa → mover marcador y actualizar inputs
            mapa.on('click', e => {
                const {
                    lat,
                    lng
                } = e.latlng;

                marcador.setLatLng([lat, lng]);
                actualizarInputs(lat, lng);
            });
        }


        // ✔ Actualizar inputs de latitud/longitud
        function actualizarInputs(lat, lng) {
            document.getElementById('latitud').value = lat.toFixed(6);
            document.getElementById('longitud').value = lng.toFixed(6);
        }

        // ✔ BUSCADOR (Geocoding con Nominatim OSM)
        function buscarDireccion() {
            const query = document.getElementById('buscadorMapa').value.trim();
            if (!query) return;

            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${query}`)
                .then(res => res.json())
                .then(data => {
                    if (data.length === 0) {
                        alert("No se encontraron resultados.");
                        return;
                    }

                    const lat = parseFloat(data[0].lat);
                    const lng = parseFloat(data[0].lon);

                    mapa.setView([lat, lng], 16);
                    marcador.setLatLng([lat, lng]);

                    actualizarInputs(lat, lng);
                })
                .catch(() => alert("Error buscando la ubicación."));
        }
    </script>
@endsection
