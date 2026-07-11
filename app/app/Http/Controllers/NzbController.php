<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Nzb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class NzbController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function nfo(Nzb $nzb)
    {
        $url = $nzb->nfo;

        abort_unless(str_starts_with($url, 'https://'), 400);

        $resp = Http::timeout(5)->get($url);

        abort_unless($resp->ok(), 404);

        return view('nbzs.nfo', [
            'nzb' => $nzb,
            'content' => $resp->body(),
        ]);
    }
}
