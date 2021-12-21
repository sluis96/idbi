<?php

namespace App\Http\Controllers;

use App\Group;
use Illuminate\Http\Request;

use App\Note;
use Validator;
use DB;
use Storage;
use Illuminate\Support\Str;

use App\Jobs\SendNotificationMail; 

class GroupController extends Controller
{
    /**
     * Muestra la lista de grupos
     *
     */
    public function index()
    {
        $groups = Group::all();

        return response()->json([
            'ready' => true,
            'groups' => $groups,
        ]);
    }

    /**
     * Registra un nuevo grupo.
     *
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $messages = [
                'nombre.required' => 'El nombre del Grupo es obligatorio.',
            ];

            $validator = Validator::make($request->all(), [
                'nombre' => ['required'],
            ], $messages);

            if ($validator->fails()) {
                return response()->json([
                    'ready' => false,
                    'message' => 'Los datos enviados no son correctos',
                    'errors' => $validator->errors(),
                ], 400);
            }

            // Datos Obligatorios
            $data = array(
                'nombre' => $request->nombre,
            );

            $group = Group::create($data);

            if (!$group->id) {
                DB::rollBack();
                return response()->json([
                    'ready' => false,
                    'message' => 'El grupo no se ha creado',
                ], 500);
            }

            // Datos Opcionales
            $group->descripcion = isset($request->descripcion) ? $request->descripcion : null;
            if (!$group->save()) {
                DB::rollBack();
                return response()->json([
                    'ready' => false,
                    'message' => 'El grupo no se ha creado',
                ], 500);
            }

            DB::commit();
            return response()->json([
                'ready' => true,
                'message' => 'El grupo se ha creado correctamente',
                'group' => $group,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'ready' => false,
            ], 500);
        }
    }

    /**
     * Muestra el contenido de un grupo en especifico.
     *
     * @param  int  $idGroup
     */
    public function show($idGroup)
    {
        $group = Group::find($idGroup);

        if(is_null($group)){
            return response()->json([
                'ready' => false,
                'message' => 'Grupo no encontrado',
            ], 404);
        }else{

            $group->notes;

            return response()->json([
                'ready' => true,
                'group' => $group,
            ]);
        }
    }

    /**
     * Agrega al usuario a un grupo en especifico.
     * 
     * @param  int  $idGroup
     */
    public function joinTo($idGroup)
    {
        try {
            DB::beginTransaction();

            $group = Group::find($idGroup);

            if(is_null($group)){
                return response()->json([
                    'ready' => false,
                    'message' => 'Grupo no encontrado',
                ], 404);
            }

            $user = auth()->user();
            $user->groups()->syncWithoutDetaching($group->id);

            DB::commit();
            return response()->json([
                'ready' => true,
                'message' => 'El usuario se ha unido al grupo correctamente',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'ready' => false,
            ], 500);
        }
    
    }

    /**
     * Crea una nota en un grupo en especifico.
     * 
     * @param  int  $idGroup
     */
    public function createNote(Request $request, $idGroup)
    {
        try {
            DB::beginTransaction();

            $messages = [
                'titulo.required' => 'El titulo es obligatorio.',
                'descripcion.required' => 'La descripcion es obligatorio.',
            ];

            $validator = Validator::make($request->all(), [
                'titulo' => ['required'],
                'descripcion' => ['required'],
            ], $messages);

            if ($validator->fails()) {
                return response()->json([
                    'ready' => false,
                    'message' => 'Los datos enviados no son correctos',
                    'errors' => $validator->errors(),
                ], 400);
            }

            // Datos Obligatorios
            $data = array(
                'titulo' => $request->titulo,
                'descripcion' => $request->descripcion,
                'group_id' => $idGroup,
                'user_id' => auth()->user()->id,
            );

            $note = Note::create($data);

            if (!$note->id) {
                DB::rollBack();
                return response()->json([
                    'ready' => false,
                    'message' => 'La nota no se ha creado',
                ], 500);
            }

            if ($request->hasFile('imagen')) {

                $messagesImagen = [
                    'imagen.file' => 'La imagen debe ser un archivo.',
                    'imagen.mimes' => 'Solo se permiten archivos de tipo .png, .jpg y .jpeg.',
                ];

                $validatorImagen = Validator::make($request->only('imagen'), [
                    'imagen' => ['file', 'mimes:png,jpg,jpeg'],
                ], $messagesImagen);

                if ($validatorImagen->fails()) {
                    DB::rollBack();
                    return response()->json([
                        'ready' => false,
                        'message' => 'Los datos enviados no son correctos',
                        'errors' => $validatorImagen->errors(),
                    ], 400);
                }

                $imagen = $request->file('imagen');

                $extension = $imagen->extension();
                $nombreArchivo = str_replace(' ', '_', $request->titulo) . '_' . Str::random(8). '.' . $extension;

                // Guardar imagen
                $imagen->storeAs(
                    'notas', $nombreArchivo
                );

                $note->nombreArchivo = $nombreArchivo;
                $note->save();

            }

            $this->sendNotificationMail($note);

            DB::commit();
            return response()->json([
                'ready' => true,
                'message' => 'La nota se ha creado correctamente',
                'note' => $note,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'ready' => false,
            ], 500);
        }
    }


    /**
     * Notifica la creacion de una nueva nota por correo
     * 
     * REQUISITOS:
     * - Migrar las tablas jobs & failed_jobs
     * - En .env: QUEUE_CONNECTION=database
     * - Configurar el servicio de correos
     */
    private function sendNotificationMail($note)
    {
        $group = $note->group;
        $user = $note->user;
        $destinatarios = $group->users->pluck('email')->toArray();

        $job = new SendNotificationMail($destinatarios, $group, $user);
        $this->dispatch($job);

    }
}
