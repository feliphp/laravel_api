<?php

namespace App\Http\Controllers\Api;

use App\Actividad;
use App\Confirmacion;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Validator;
use OneSignal;

class ConfirmacionController extends ApiController
{
    public function getConfirmaciones(){
        $data = [];
        $confirmaciones = Confirmacion::all();

        $data['confirmaciones'] = $confirmaciones;

        return $this->sendResponse($data, "Confirmaciones recuperadas correctamente");
    }

    public function getConfirmacionDetail($id, Request$request){
        $confirmacion = Confirmacion::find($id);
        if ($confirmacion === null) {
            return $this->sendError("Error en los datos", ["La confirmación no existe"], 422);
        }

        $actividad = Actividad::find($confirmacion->idactividad);
        if($actividad === null){
            return $this->sendError("Error en los datos", ["La actividado no existe"], 422);
        }

        $users = DB::table("confirmacion")
            ->where("confirmacion.idactividad", "=", $confirmacion->idactividad)
            ->join("userdata", "confirmacion.iduser", "userdata.iduser")
            ->select("userdata.iduser", "userdata.nombre", "userdata.foto", "userdata.edad", "userdata.genero")
            ->get();

        $data = [
            'actividad' => $actividad,
            'users' => $users
        ];
        return $this->sendResponse($data, "Confirmación recuperada correctamente");
    }



    public function getConfirmacionUser($id, Request$request){
        $confirmaciones = DB::table("confirmacion")
            ->where("confirmacion.iduser", "=", $id)
            ->join("actividad", "confirmacion.idactividad", "actividad.id")
            ->get();


        $data = [
            'confirmaciones' => $confirmaciones
        ];
        return $this->sendResponse($data, "Confirmaciones recuperadas correctamente");
    }


    public function addConfirmacion(Request $request){
        $validator = Validator::make($request->all(), [
            'iduser' => 'required',
            'idactividad' => 'required'
        ]);
        if($validator->fails()){
            return $this->sendError("Error de validación", $validator->errors(), 422);
        }

        $confirmacion = Confirmacion::where([
            ["iduser", "=", $request->get("iduser")],
            ["idactividad", "=", $request->get("idactividad")],
            ])->first();
        if($confirmacion !== null){
            return $this->sendError("Error de confirmación", ["El usuario ya ha confirmado previamente"], 422);
        }

        $confirmacion = new Confirmacion();
        $confirmacion->iduser = $request->get("iduser");
        $confirmacion->idactividad = $request->get("idactividad");
        $confirmacion->save();

        $users = DB::table("confirmacion")
            ->where("confirmacion.idactividad", "=", $confirmacion->idactividad)
            ->join("userdata", "confirmacion.iduser", "userdata.iduser")
            ->select("userdata.idonesignal")
            ->get();
        foreach ($users as $user){
            $id = $user->idonesignal;
            if($id !=1){
                OneSignal::sendNotificationToUser("Se ha apuntado otro usuario a la actividad",
                    $id,
                    $url = null,
                    $data = null,
                    $buttons = null,
                    $schedule = null);
            }
        }


        $data = [
            'confirmacion' => $confirmacion
        ];
        return $this->sendResponse($data, "Confirmación creada correctamente");

    }

    public function deleteConfirmacion(Request $request)
    {
        $confirmacion = Confirmacion::find($request->get("id"));
        if ($confirmacion === null) {
            return $this->sendError("Error en los datos", ["El usuario no existe"], 422);
        }

        $confirmacion->delete();

        return $this->sendResponse([
            'status' => "OK"
        ], "Confirmación borrada correctamente");
    }
}
