<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admision;
use Illuminate\Http\Request;

class AdmisionController extends Controller
{
    public function index()
    {
        return Admision::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'paquetes' => 'required|string|max:255',
            'departamento' => 'required|string|max:255',
        ]);

        $admision = Admision::create($request->all());

        return response()->json($admision, 201);
    }

    public function show(Admision $admision)
    {
        return $admision;
    }

    public function update(Request $request, Admision $admision)
    {
        $request->validate([
            'paquetes' => 'required|string|max:255',
            'departamento' => 'required|string|max:255',
        ]);

        $admision->update($request->all());

        return response()->json($admision, 200);
    }

    public function destroy(Admision $admision)
    {
        $admision->delete();

        return response()->json(null, 204);
    }
}
