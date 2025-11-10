<?php

namespace App\Imports;

use App\Models\Clientes;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as XlsDate;

class ClientesImport implements ToCollection, WithHeadingRow
{
    public bool $simulate;
    public int $inserted = 0;
    public int $updated  = 0;
    public array $errors = [];

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
            // normaliza claves a minúsculas
            $r = collect($row)->keyBy(fn($v, $k) => mb_strtolower($k))->all();

            // alias tolerantes por campo
            $get = function(array $aliases) use ($r) {
                foreach ($aliases as $k) {
                    if (array_key_exists($k, $r) && $r[$k] !== null && $r[$k] !== '') return $r[$k];
                }
                return null;
            };

            $nombre             = $get(['nombre']);
            $ap_pat             = $get(['apellido_paterno','ap_paterno','apellido paterno']);
            $ap_mat             = $get(['apellido_materno','ap_materno','apellido materno']);
            $ci                 = $get(['ci','c.i','carnet','carnet_de_identidad','carnet de identidad']);
            $tel                = $get(['telefono','tel','whatsapp']);
            $correo             = $get(['correo','email','e-mail']);
            $sexo               = $get(['sexo','genero','género']);
            $fecha_nac_raw      = $get(['fecha_de_nacimiento','fecha_nacimiento','fecha nacimiento','nacimiento','f_nac','fnac']);

            // valida mínimos
            if (!$nombre || !$ap_pat || !$ci || !$fecha_nac_raw) {
                $this->errors[] = "Fila ".($i+2).": faltan campos obligatorios (nombre, apellido_paterno, ci, fecha_de_nacimiento).";
                continue;
            }

            // parsea fecha en múltiples formatos o serial de Excel
            try {
                if (is_numeric($fecha_nac_raw)) {
                    $fecha_nac = Carbon::instance(XlsDate::excelToDateTimeObject((float)$fecha_nac_raw));
                } else {
                    $fecha_nac = Carbon::parse($fecha_nac_raw);
                }
            } catch (\Throwable $e) {
                $this->errors[] = "Fila ".($i+2).": fecha de nacimiento inválida ({$fecha_nac_raw}).";
                continue;
            }

            // normaliza teléfono
            if ($tel && preg_match('/^\d{8}$/', $tel)) {
                $tel = '+591'.$tel;
            }

            // credenciales
            $primerNombre = explode(' ', trim($nombre))[0] ?? $nombre;
            $username = $primerNombre . '_' . $ci;
            $passwordInicial = $fecha_nac->format('d-m-Y');

            if ($this->simulate) {
                // solo cuenta como “preview”
                continue;
            }

            try {
                DB::transaction(function () use (
                    $nombre,$ap_pat,$ap_mat,$ci,$tel,$correo,$sexo,$fecha_nac,$username,$passwordInicial
                ) {
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

                    $cliente = Clientes::updateOrCreate(
                        ['ci' => $ci],
                        [
                            'nombre'                 => $nombre,
                            'apellido_paterno'       => $ap_pat,
                            'apellido_materno'       => $ap_mat,
                            'fecha_de_nacimiento'    => $fecha_nac->toDateString(),
                            'telefono'               => $tel,
                            'correo'                 => $correo,
                            'sexo'                   => $sexo,
                            'biometrico_id'          => $ci,
                            'user_id'                => $user->id,
                            'registrado_por'         => auth()->id(),
                            'modificado_por'         => auth()->id(),
                        ]
                    );

                    // marca insert/update
                    if ($cliente->wasRecentlyCreated) {
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
