@php
    /** @var \App\Models\User $user */
    $gestionable = $actor->puedeGestionarUsuario($user);
    $esYo = $user->id === $actor->id;
    $ultimoSuper = $user->isSuperAdmin() && $totalSuperAdmins <= 1;
    $motivoNoBorrable = match (true) {
        $esYo => 'No puedes eliminar tu propio usuario',
        ! $gestionable => 'Solo un súper administrador puede eliminarlo',
        $ultimoSuper => 'Es el único súper administrador',
        default => null,
    };
    $ultimoAcceso = $ultimosAccesos[$user->id] ?? null;
@endphp

<li class="px-4 py-3">
    <div class="flex items-start gap-3">
        @if ($user->avatar_url)
            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                class="h-10 w-10 shrink-0 rounded-full object-cover ring-1 ring-slate-200">
        @else
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-100 text-sm font-semibold text-slate-600">
                {{ $user->iniciales }}
            </span>
        @endif

        <div class="min-w-0 flex-1">
            <p class="break-words font-semibold text-slate-800">
                {{ $user->name }}
                @if ($esYo)
                    <span class="ml-1 rounded-full bg-cyan-50 px-2 py-0.5 text-[11px] font-semibold text-cyan-700">tú</span>
                @endif
            </p>
            <p class="break-all text-xs text-slate-500">{{ $user->email }}</p>

            <div class="mt-2 flex flex-wrap items-center gap-2">
                <span class="inline-flex rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $rolClases[$user->role] ?? $rolClases['user'] }}">
                    {{ $roles[$user->role] ?? $user->role }}
                </span>
                <span class="text-xs text-slate-500">
                    @if ($ultimoAcceso)
                        Último acceso: {{ \Illuminate\Support\Carbon::parse($ultimoAcceso)->format('d/m/Y H:i') }}
                    @else
                        Nunca ha entrado
                    @endif
                </span>
            </div>
        </div>

        <div class="flex shrink-0 items-center gap-1">
            @if ($gestionable)
                <button type="button" title="Editar usuario"
                    x-on:click="usuario = {{ Illuminate\Support\Js::from(['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'role' => $user->role]) }}; modal = 'editar'"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-cyan-700">
                    <i class="mgc_edit_line"></i>
                </button>
            @endif

            @if (! $motivoNoBorrable)
                <button type="button" title="Eliminar usuario"
                    x-on:click="borrar = {{ Illuminate\Support\Js::from(['id' => $user->id, 'name' => $user->name, 'email' => $user->email]) }}"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-rose-500 transition hover:bg-rose-50 hover:text-rose-700">
                    <i class="mgc_delete_line"></i>
                </button>
            @endif
        </div>
    </div>
</li>
