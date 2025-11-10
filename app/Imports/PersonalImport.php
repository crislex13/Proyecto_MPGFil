<?php

namespace App\Imports;

use App\Models\Personal;
use App\Models\User;
use App\Models\Disciplina;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as XlsDate;

class PersonalImport implements ToCollection, WithHeadingRow
{
    public bool $simulate;
    public int $inserted = 0;
    public int $updated  = 0;
    public array $errors = [];

    /**
     * Columnas aceptadas (alias flexibles):
     * nombre, apellido_paterno|ap_paterno, apellido_materno|ap_materno,
     * ci|carnet|c.i, telefono|whatsapp, direccion,
     * fecha_de_nacimiento|fecha_nacimiento|fnac, correo|email,
     * cargo, fecha_contratacion|fcontratacion, estado,
     * disciplinas (opcional, separadas por coma)
     */
    public function __construct(bool $simulate = false)
    {
        $this->simulate = $simulate;
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $i => $row) {
            // normaliza claves
            $r = collect($row)->keyBy(fn($v, $k) => mb_strtolower($k))->all();

            $get = function(array $aliases) use ($r) {
                foreach ($aliases as $k) {
                    if (array_key_exists($k, $r) && $r[$k] !== null && $r[$k] !== '') return $r[$k];
                }
                return null;
            };

            $nombre      = $get(['nombre']);
            $ap_pat      = $get(['apellido_paterno','ap_paterno','apellido paterno']);
            $ap_mat      = $get(['apellido_materno','ap_materno','apellido materno']);
            $ci          = $get(['ci','c.i','carnet','carnet_de_identidad','carnet de identidad']);
            $tel         = $get(['telefono','tel','whatsapp']);
            $direccion   = $get(['direccion']);
            $correo      = $get(['correo','email','e-mail']);
            $cargo       = Str::lower((string) $get(['cargo']) ?: 'instructor');
            $estado      = Str::lower((string) $get(['estado']) ?: 'activo');
            $discRaw     = $get(['disciplinas']); // "Funcional, Box, Zumba"

            $fnacRaw     = $get(['fecha_de_nacimiento','fecha_nacimiento','fnac','f_nac','fecha nacimiento']);
            $fcontRaw    = $get(['fecha_contratacion','fcontratacion','fecha contratacion']);

            // validaciones mínimas
            if (!$nombre || !$ap_pat || !$ci || !$fnacRaw || !$fcontRaw) {
                $this->errors[] = "Fila ".($i+2).": faltan campos obligatorios (nombre, apellido_paterno, ci, fecha_de_nacimiento, fecha_contratacion).";
                continue;
            }

            // parsea fechas (acepta serial Excel)
            try {
                $fnac = is_numeric($fnacRaw)
                    ? Carbon::instance(XlsDate::excelToDateTimeObject((float)$fnacRaw))
                    : Carbon::parse($fnacRaw);
                $fcon = is_numeric($fcontRaw)
                    ? Carbon::instance(XlsDate::excelToDateTimeObject((float)$fcontRaw))
                    : Carbon::parse($fcontRaw);
            } catch (\Throwable $e) {
                $this->errors[] = "Fila ".($i+2).": fecha inválida (nac/contratación).";
                continue;
            }

            // normaliza teléfono
            if ($tel) {
                $tel = preg_replace('/\s+/', '', $tel);
                if (preg_match('/^\d{8}$/', $tel)) $tel = '+591'.$tel;
            }

            // credenciales de usuario
            $primerNombre = explode(' ', trim($nombre))[0] ?? $nombre;
            $username = $primerNombre . '_' . $ci;
            $passwordInicial = $fnac->format('d-m-Y');

            // normaliza estado/cargo
            $estado = in_array($estado, ['activo','inactivo','baja']) ? $estado : 'activo';
            $cargo  = in_array($cargo, ['instructor','recepcionista','supervisor']) ? $cargo : 'instructor';

            if ($this->simulate) {
                // solo preconteo (no guardamos)
                continue;
            }

            try {
                DB::transaction(function () use (
                    $nombre,$ap_pat,$ap_mat,$ci,$tel,$direccion,$correo,$cargo,$estado,$fnac,$fcon,$username,$passwordInicial,$discRaw
                ) {
                    // crea/obtiene usuario
                    $user = User::firstOrCreate(
                        ['username' => $username],
                        [
                            'name'     => trim($nombre.' '.$ap_pat.' '.($ap_mat ?? '')),
                            'ci'       => $ci,
                            'telefono' => $tel,
                            'estado'   => 'activo',
                            'password' => \Hash::make($passwordInicial),
                        ]
                    );
                    // asegura rol según cargo
                    if (!$user->hasRole($cargo)) {
                        $user->syncRoles([$cargo]); // un solo rol, ajústalo si necesitas múltiples
                    }

                    // upsert de Personal por CI
                    $personal = Personal::updateOrCreate(
                        ['ci' => (string) $ci],
                        [
                            'nombre'              => $nombre,
                            'apellido_paterno'    => $ap_pat,
                            'apellido_materno'    => $ap_mat,
                            'telefono'            => $tel,
                            'direccion'           => $direccion,
                            'fecha_de_nacimiento' => $fnac->toDateString(),
                            'correo'              => $correo,
                            'cargo'               => $cargo,
                            'biometrico_id'       => (string) $ci,
                            'fecha_contratacion'  => $fcon->toDateString(),
                            'estado'              => $estado,
                            'user_id'             => $user->id,
                            'registrado_por'      => auth()->id(),
                            'modificado_por'      => auth()->id(),
                        ]
                    );

                    // vincula disciplinas si vienen
                    if (!empty($discRaw)) {
                        $nombres = collect(explode(',', (string) $discRaw))
                            ->map(fn($v) => trim($v))
                            ->filter()
                            ->values();

                        if ($nombres->isNotEmpty()) {
                            $ids = Disciplina::whereIn('nombre', $nombres)->pluck('id')->all();
                            // si el modelo tiene ->disciplinas() belongsToMany:
                            $personal->disciplinas()->sync($ids);
                        }
                    }

                    if ($personal->wasRecentlyCreated) {
                        $this->inserted++;
                    } else {
                        $this->updated++;
                    }
                });
            } catch (\Throwable $e) {
                $this->errors[] = "Fila ".($i+2).": ".$e->getMessage();
            }
        }
    }
}
