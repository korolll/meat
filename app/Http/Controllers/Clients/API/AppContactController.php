<?php

namespace App\Http\Controllers\Clients\API;

use App\Models\AppContact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class AppContactController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $contacts = AppContact::all()->first()->toArray();
        return response()->json(['data'=>$contacts],200);
    }
}
