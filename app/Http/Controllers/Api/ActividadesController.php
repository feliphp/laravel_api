<?php

namespace App\Http\Controllers\Api;

use App\Actividad;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Validator;

class ActividadesController extends ApiController
{
    public function getActividades(){
        $data = [];

        $actividades = DB::table("actividad")
            ->select("actividad.id", "actividad.nombre", "actividad.foto", "actividad.fecha")
            ->get();

        $data['actividades'] = $actividades;

        return $this->sendResponse($data, "Actividades recuperadas correctamente");
    }

    public function getActividadDetail($id, Request $request){
        $actividad = Actividad::find($id);
        if($actividad === null){
            return $this->sendError("Error en los datos", ["La actividado no existe"], 422);
        }

        $confirmaciones = DB::table("confirmacion")
            ->where("confirmacion.idactividad", "=", $id)
            ->join("userdata", "confirmacion.iduser", "userdata.iduser")
            ->select("userdata.iduser", "userdata.nombre", "userdata.foto", "userdata.edad", "userdata.genero")
            ->get();

        $data = [];
        $data["actividad"] = $actividad;
        $data["users"] = $confirmaciones;
        return $this->sendResponse($data, "Actividades recuperadas correctamente");
    }

    public function addActividad(Request $request){
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|unique:actividad',
            'foto' => 'required',
            'descripcion' => 'required',
            'fecha' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError("Error de validación", $validator->errors(), 422);
        }


        $actividad = new Actividad();
        $actividad->nombre = $request->get("nombre");
        $actividad->foto = $request->get("foto");
        $actividad->fecha = $request->get("fecha");
        $actividad->descripcion = $request->get("descripcion");
        $actividad->save();

        $data = [
            'actividad' => $actividad
        ];
        return $this->sendResponse($data, "Actividad creada correctamente");

    }

    public function updateActividad(Request $request){
        $actividad = Actividad::find($request->get("id"));
        if($actividad === null){
            return $this->sendError("Error en los datos", ["La actividado no existe"], 422);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required',
            'foto' => 'required',
            'descripcion' => 'required',
            'fecha' => 'required'
        ]);
        if($validator->fails()){
            return $this->sendError("Error de validación", $validator->errors(), 422);
        }

        $actividad->nombre = $request->get("nombre");
        $actividad->foto = $request->get("foto");
        $actividad->descripcion = $request->get("descripcion");
        $actividad->fecha = $request->get("fecha");
        $actividad->save();

        $data = [
            'actividad' => $actividad
        ];
        return $this->sendResponse($data, "Actividad modificada correctamente");
    }


    public function deleteActividad(Request $request){
        $actividad = Actividad::find($request->get("id"));
        if($actividad === null){
            return $this->sendError("Error en los datos", ["La actividado no existe"], 422);
        }

        $validator = Validator::make($request->all(), [
            'active' => 'required'
        ]);
        if($validator->fails()){
            return $this->sendError("Error de validación", $validator->errors(), 422);
        }

        $actividad->active = $request->get("active");
        $actividad->save();

        $data = [
            '$actividad' => $actividad
        ];
        return $this->sendResponse($data, "Actividad modificada correctamente");
    }
}
