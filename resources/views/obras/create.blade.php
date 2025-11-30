@extends('layouts.vertical', ['title' => 'Alta de Obras', 'sub_title' => 'Pages', 'mode' => $mode ?? '', 'demo' => $demo ?? ''])

@section('css')
    <script src="https://unpkg.com/alpinejs" defer></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endsection

@section('content')
    @include('notifications.notyf')


    {{-- Mensaje explicación --}}
    <div x-data="{ show: true }" x-show="show"
        class="relative mb-6 p-4 bg-blue-50 border-l-4 border-blue-500 text-blue-800 rounded-md dark:bg-blue-900 dark:text-blue-100 dark:border-blue-400">
        <button @click="show=false" class="absolute top-2 right-2 text-blue-800 dark:text-blue-100 hover:text-red-500">
            &times;
        </button>

        <h2 class="text-lg font-semibold mb-2">Alta de nueva obra</h2>
        <p>
            En este formulario podrás <strong>dar de alta una nueva obra</strong> en el sistema.
            Asegúrate de completar todos los campos requeridos.
        </p>
        <p class="mt-2">
            Es especialmente importante introducir de forma correcta la <strong>latitud, longitud y el radio de fichaje (en
                metros)</strong>, ya que estos datos son fundamentales para la plataforma de <strong>control de
                presencia</strong> que trabaja en paralelo.
        </p>
        <ul class="mt-2 list-disc list-inside">
            <li>Verificar si los empleados fichan dentro del área autorizada.</li>
            <li>Calcular las <strong>horas trabajadas reales</strong> por cada empleado.</li>
            <li>Generar informes automáticos de mano de obra por obra.</li>
            <li>Cumplir con los requisitos legales del registro de jornada.</li>
        </ul>
    </div>



    <div class="grid grid-cols-12">
        <div class="col-span-12">
            <div class="card p-6">

                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                        <h4 class="font-semibold mb-2">Se encontraron errores:</h4>
                        <ul class="list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif



                <form action="{{ route('obras.store') }}" method="POST" class="space-y-8">
                    @csrf

                    <!-- CARD: TÍTULO -->
                    <div class="bg-primary text-gray-50 p-5 rounded-xl shadow-md">
                        <h1 class="text-2xl font-bold tracking-wide">Nueva obra</h1>
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
                                    value="{{ old('nombre') }}" placeholder="Nombre de la obra" required>
                                @error('nombre')
                                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Estado -->
                            <div>
                                <label class="text-sm font-medium text-gray-700">Estado</label>
                                <select name="estado" class="mt-1 form-select w-full">
                                    <option value="planificacion" {{ old('estado') == 'planificacion' ? 'selected' : '' }}>
                                        Planificación</option>
                                    <option value="ejecucion" {{ old('estado') == 'ejecucion' ? 'selected' : '' }}>Ejecución
                                    </option>
                                    <option value="finalizada" {{ old('estado') == 'finalizada' ? 'selected' : '' }}>
                                        Finalizada</option>
                                </select>
                                @error('estado')
                                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Fecha inicio -->
                            <div>
                                <label class="text-sm font-medium text-gray-700">Fecha inicio</label>
                                <input type="date" name="fecha_inicio" class="mt-1 form-input w-full"
                                    value="{{ old('fecha_inicio') }}">
                                @error('fecha_inicio')
                                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Fecha fin -->
                            <div>
                                <label class="text-sm font-medium text-gray-700">Fecha fin</label>
                                <input type="date" name="fecha_fin" class="mt-1 form-input w-full"
                                    value="{{ old('fecha_fin') }}">
                                @error('fecha_fin')
                                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Importe Presupuestado -->
                            <div class="md:col-span-2">
                                <label class="text-sm font-medium text-gray-700">Importe presupuestado</label>
                                <input type="number" min="0" step="0.01" name="importe_presupuestado"
                                    class="mt-1 form-input w-full" value="{{ old('importe_presupuestado') }}"
                                    placeholder="0.00" required>
                                @error('importe_presupuestado')
                                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            

                            <!-- Descripción -->
                            <div class="md:col-span-2">
                                <label class="text-sm font-medium text-gray-700">Descripción</label>
                                <textarea name="descripcion" rows="4" class="mt-1 form-input w-full" placeholder="Detalles sobre la obra...">{{ old('descripcion') }}</textarea>
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
                            Esta información se utilizará en la <strong>app de presencia</strong> para validar fichajes
                            dentro del área permitida.
                        </p>

                        <div class="flex justify-end mb-6">
                            <button type="button" onclick="abrirMapa()"
                                class="px-4 py-2 bg-primary text-white rounded-md shadow hover:bg-primary/90 transition text-sm">
                                Seleccionar en mapa
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                            <!-- Latitud -->
                            <div>
                                <label class="text-sm font-medium text-gray-700">Latitud</label>
                                <input type="text" name="latitud" class="mt-1 form-input w-full"
                                    value="{{ old('latitud') }}" placeholder="Ej: 37.123456">
                                @error('latitud')
                                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Longitud -->
                            <div>
                                <label class="text-sm font-medium text-gray-700">Longitud</label>
                                <input type="text" name="longitud" class="mt-1 form-input w-full"
                                    value="{{ old('longitud') }}" placeholder="Ej: -3.123456">
                                @error('longitud')
                                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Radio -->
                            <div>
                                <label class="text-sm font-medium text-gray-700">Radio permitido (metros)</label>
                                <input type="number" name="radio" class="mt-1 form-input w-full"
                                    value="{{ old('radio') }}" placeholder="Ej: 50">
                                @error('radio')
                                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>
                    </div>


                    <!-- CARD 3: GASTOS INICIALES -->
                    <div class="bg-white rounded-xl shadow p-6 border border-gray-200">
                        @livewire('obras.gastos-iniciales', ['obra' => $obra ?? null])
                    </div>


                    <!-- CARD 4: CONFIRMACIÓN -->
                    <div class="bg-white rounded-xl shadow p-5 border border-gray-200 flex items-center gap-3">
                        <input type="checkbox" id="confirmar" class="form-checkbox text-primary rounded" required>
                        <label for="confirmar" class="text-sm font-medium text-gray-700">
                            Confirmo que los datos son correctos.
                        </label>
                    </div>


                    <!-- BOTÓN SUBMIT -->
                    <button type="submit"
                        class="w-full md:w-auto px-8 py-3 bg-primary text-white font-semibold rounded-lg shadow hover:bg-primary/90 transition">
                        Crear obra
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
        let mapa;
        let marker;

        // Abrir modal + inicializar mapa
        function abrirMapa() {
            const modal = document.getElementById('modalMapa');
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            setTimeout(() => {
                if (!mapa) {
                    mapa = L.map('mapaObra').setView([37.1882, -3.6067], 14); // Granada por defecto

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap'
                    }).addTo(mapa);

                    mapa.on('click', e => {
                        ponerMarcador(e.latlng.lat, e.latlng.lng);
                    });
                }

                mapa.invalidateSize();
            }, 200);
        }

        function cerrarMapa() {
            const modal = document.getElementById('modalMapa');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function ponerMarcador(lat, lng) {
            if (marker) mapa.removeLayer(marker);

            marker = L.marker([lat, lng]).addTo(mapa);

            document.querySelector("input[name='latitud']").value = lat;
            document.querySelector("input[name='longitud']").value = lng;
        }

        // BUSCADOR DE DIRECCIONES (Nominatim)
        function buscarDireccion() {
            const query = document.getElementById('buscadorMapa').value;

            if (!query) return;

            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${query}`)
                .then(r => r.json())
                .then(data => {
                    if (!data.length) {
                        alert("No se encontró esa dirección");
                        return;
                    }

                    const lat = parseFloat(data[0].lat);
                    const lon = parseFloat(data[0].lon);

                    mapa.setView([lat, lon], 17);
                    ponerMarcador(lat, lon);
                });
        }
    </script>
@endsection
