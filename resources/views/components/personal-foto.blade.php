@php
    $personalId = data_get($this->data, 'personal_id');
    $personal = \App\Models\Personal::find($personalId);
@endphp

@if ($personal)
    <div class="w-full max-w-md mx-auto bg-white/5 p-4 rounded-xl shadow border border-gray-700 flex flex-col items-center space-y-4">
        @if ($personal->foto)
            <img
                src="{{ \Illuminate\Support\Facades\Storage::url($personal->foto) }}"
                alt="Foto del personal"
                class="w-40 h-40 object-cover rounded-full border border-gray-500 shadow-md transition hover:scale-105 duration-300 ease-in-out"
            >
        @else
            <div class="w-40 h-40 flex items-center justify-center rounded-full bg-gray-800 text-gray-400 text-sm border border-gray-500">
                Sin Foto
            </div>
        @endif

        <div class="text-center">
            <h2 class="text-lg font-semibold text-white">{{ $personal->nombre_completo }}</h2>
            <p class="text-sm text-gray-400">
                Cargo: {{ $personal->cargo }}<br>
                Salario: <span class="text-green-400 font-semibold">{{ number_format($personal->salario, 2) }} Bs.</span>
            </p>
        </div>
    </div>
@else
    <div class="text-center italic text-gray-500">Sin información del personal.</div>
@endif